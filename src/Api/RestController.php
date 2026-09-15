<?php

namespace SwiftSearchAlgolia\Api;

use SwiftSearchAlgolia\Client\Client;
use SwiftSearchAlgolia\Core\Gatekeeper;
use SwiftSearchAlgolia\Engine\Indexer;
use SwiftSearchAlgolia\Engine\Schema;
use SwiftSearchAlgolia\Engine\TermIndexer;
use SwiftSearchAlgolia\Engine\UserIndexer;
use SwiftSearchAlgolia\Engine\ConfigLoader;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RestController
 *
 * Handles REST API endpoints for SwiftSearch for Algolia.
 */
class RestController extends WP_REST_Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->namespace = 'swift-search-algolia/v1';
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register routes.
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/connect', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_connect'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/disconnect', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_disconnect'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/status', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_status'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/sync/batch', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_batch_sync'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/sync/start', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_sync_start'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/diagnose', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'handle_diagnose'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/sync/status', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'handle_sync_status'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/reset', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_reset'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/settings', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_settings'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/settings/reset', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_factory_reset'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/log', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_log'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($this->namespace, '/analytics', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_analytics'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/pinning/search', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handle_pinning_search'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route($this->namespace, '/pinning/items', array(
            'methods'             => array(WP_REST_Server::READABLE, WP_REST_Server::CREATABLE),
            'callback'            => array($this, 'dispatch_pinning_items'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    public function dispatch_pinning_items($request)
    {
        if ($request->get_method() === 'GET') {
            return $this->get_pinned_items();
        }
        return $this->handle_pinning_save($request);
    }

    /**
     * Capability check.
     *
     * @return bool
     */
    public function check_permission()
    {
        return current_user_can('manage_options');
    }

    /**
     * Handle connect request.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_connect($request)
    {
        $params = $request->get_params();

        $app_id         = sanitize_text_field($params['app_id'] ?? ($params['application_id'] ?? ''));
        $admin_api_key  = sanitize_text_field($params['admin_api_key'] ?? ($params['api_key'] ?? ''));
        $search_api_key = sanitize_text_field($params['search_api_key'] ?? ($params['search_key'] ?? ''));
        $index_prefix   = sanitize_text_field($params['index_prefix'] ?? '');

        if (empty($app_id) || empty($admin_api_key)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Missing Algolia Application ID or Admin API Key.', 'swiftsearch-for-algolia'),
            ), 400);
        }

        $existing_settings = get_option('swift_search_algolia_settings', array());
        $config = array_merge($existing_settings, array(
            'app_id'         => $app_id,
            'admin_api_key'  => $admin_api_key,
            'search_api_key' => $search_api_key,
            'index_prefix'   => $index_prefix,
            // Legacy keys compatibility
            'api_key'        => $admin_api_key,
            'search_key'     => $search_api_key,
        ));

        // Test Connection
        $client   = new Client($config);
        $is_valid = $client->test_connection();

        if ($is_valid) {
            update_option('swift_search_algolia_settings', $config);
            set_transient('swift_search_algolia_health_status', 'ok', 5 * MINUTE_IN_SECONDS);

            // Configure initial settings and replicas
            $indexer = new Indexer();
            try {
                $indexer->create_collection();
            } catch (\Exception $e) {
                // Non-fatal if index already configured
            }

            $stats = $client->get_stats();

            return new WP_REST_Response(array(
                'success' => true,
                'data'    => array(
                    'message'   => __('Connected to Algolia successfully!', 'swiftsearch-for-algolia'),
                    'doc_count' => $stats['num_documents'] ?? 0,
                ),
            ), 200);
        }

        $error = $client->get_last_error();
        $msg   = __('Connection failed. Please check your Algolia credentials.', 'swiftsearch-for-algolia');
        if (!empty($error)) {
            $msg .= ' ' . sprintf(__('Details: %s', 'swiftsearch-for-algolia'), $error);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'data'    => array('message' => $msg),
        ), 200);
    }

    /**
     * Handle disconnect request.
     *
     * @return WP_REST_Response
     */
    public function handle_disconnect()
    {
        $settings = get_option('swift_search_algolia_settings', array());

        unset($settings['app_id']);
        unset($settings['admin_api_key']);
        unset($settings['search_api_key']);
        unset($settings['api_key']);
        unset($settings['search_key']);

        update_option('swift_search_algolia_settings', $settings);

        delete_option('swift_search_algolia_index_status');
        delete_option('swift_search_algolia_debug_loopback');
        delete_option('swift_search_algolia_schema_hash');
        delete_option('swift_search_algolia_schema_mismatch');
        delete_option('swift_search_algolia_sync_errors');
        delete_transient('swift_search_algolia_health_status');

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Handle Factory Reset request.
     *
     * @return WP_REST_Response
     */
    public function handle_factory_reset()
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}swift_search_algolia_logs");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}swift_search_algolia_batch_logs");

        delete_option('swift_search_algolia_settings');
        delete_option('swift_search_algolia_index_status');
        delete_option('swift_search_algolia_debug_loopback');
        delete_option('swift_search_algolia_schema_hash');
        delete_option('swift_search_algolia_schema_mismatch');
        delete_option('swift_search_algolia_sync_errors');
        delete_option('swift_search_algolia_pinned_items');
        delete_option('swift_search_algolia_override_default');
        delete_option('swift_search_algolia_bg_secret');
        delete_transient('swift_search_algolia_health_status');

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Get connection and indexing status.
     *
     * @return WP_REST_Response
     */
    public function get_status()
    {
        $config = get_option('swift_search_algolia_settings', array());

        if (empty($config['admin_api_key']) && empty($config['api_key'])) {
            return new WP_REST_Response(array(
                'success' => true,
                'data'    => array('connected' => false),
            ), 200);
        }

        $client   = new Client($config);
        $is_valid = $client->test_connection();
        $stats    = $is_valid ? $client->get_stats() : array();

        return new WP_REST_Response(array(
            'success' => true,
            'data'    => array(
                'connected' => $is_valid,
                'doc_count' => $stats['num_documents'] ?? 0,
                'indices'   => $stats['indices'] ?? array(),
            ),
        ), 200);
    }

    /**
     * Handle Batch Sync for browser-fallback indexing.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_batch_sync($request)
    {
        $page     = (int) $request->get_param('page');
        $page     = $page > 0 ? $page : 1;
        $type     = $request->get_param('type') ? sanitize_text_field($request->get_param('type')) : 'posts';
        $per_page = 50;
        $offset   = ($page - 1) * $per_page;

        $config      = get_option('swift_search_algolia_settings', array());
        $indexer     = new Indexer();
        $client      = new Client($config);
        $processed   = 0;
        $total_pages = 0;

        if ($type === 'terms') {
            $taxonomies = isset($config['indexed_taxonomies']) ? $config['indexed_taxonomies'] : array('category', 'post_tag');

            if (empty($taxonomies)) {
                return new WP_REST_Response(array(
                    'success' => true,
                    'data'    => array('processed' => 0, 'page' => 1, 'total_pages' => 0, 'complete' => true),
                ), 200);
            }

            $args = array(
                'taxonomy'   => $taxonomies,
                'hide_empty' => false,
                'number'     => $per_page,
                'offset'     => $offset,
                'fields'     => 'ids',
            );

            $term_ids    = get_terms($args);
            $total_terms = wp_count_terms($taxonomies);
            if (is_wp_error($total_terms)) {
                $total_terms = 0;
            }

            $documents = array();
            if (!is_wp_error($term_ids) && !empty($term_ids)) {
                $term_indexer = new TermIndexer(new ConfigLoader());
                foreach ($term_ids as $term_id) {
                    $term = get_term($term_id);
                    if ($term) {
                        $doc = $term_indexer->build($term);
                        if ($doc) {
                            $documents[] = $doc;
                        }
                    }
                    $processed++;
                }

                if (!empty($documents)) {
                    $client->save_objects('terms', $documents);
                }
            }

            $complete    = empty($term_ids) || count($term_ids) < $per_page;
            $total_pages = ceil((int) $total_terms / $per_page);

        } elseif ($type === 'users') {
            $index_users = isset($config['indexed_users']) ? (bool) $config['indexed_users'] : false;

            if (!$index_users) {
                $status = get_option('swift_search_algolia_index_status', array());
                if (is_array($status)) {
                    $status['active']                 = false;
                    $status['message']                = __('Complete!', 'swiftsearch-for-algolia');
                    $status['last_sync_completed_at'] = time();
                    update_option('swift_search_algolia_index_status', $status);
                }
                return new WP_REST_Response(array(
                    'success' => true,
                    'data'    => array('processed' => 0, 'page' => 1, 'total_pages' => 0, 'complete' => true),
                ), 200);
            }

            $args = array(
                'number' => $per_page,
                'offset' => $offset,
                'fields' => 'ID',
                'who'    => 'authors',
            );

            $query       = new \WP_User_Query($args);
            $ids         = $query->get_results();
            $total_users = $query->get_total();

            $documents = array();
            if (!empty($ids)) {
                $user_indexer = new UserIndexer(new ConfigLoader());
                foreach ($ids as $user_id) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $doc = $user_indexer->build($user);
                        if ($doc) {
                            $documents[] = $doc;
                        }
                    }
                    $processed++;
                }

                if (!empty($documents)) {
                    $client->save_objects('users', $documents);
                }
            }

            $total_pages = ceil($total_users / $per_page);
            $complete    = empty($ids) || $page >= $total_pages;

        } else {
            // Posts (Default)
            $post_types = isset($config['indexed_post_types']) ? $config['indexed_post_types'] : array('post', 'page', 'product');

            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'fields'         => 'ids',
                'orderby'        => 'ID',
                'order'          => 'ASC',
            );

            $query = new \WP_Query($args);
            $ids   = $query->posts;

            $documents = array();
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $doc = $indexer->index_post($id, true);
                    if ($doc) {
                        $documents[] = $doc;
                    }
                    $processed++;
                }

                if (!empty($documents)) {
                    $client->save_objects($indexer->get_base_index_name(), $documents);
                }
            }

            $total_pages = $query->max_num_pages;
            $complete    = empty($ids) || $page >= $total_pages;
        }

        // Update DB status
        $status = get_option('swift_search_algolia_index_status', array());
        if (!is_array($status)) {
            $status = array();
        }
        $cumulative_processed   = $offset + $processed;
        $status['processed']    = $cumulative_processed;
        $status['last_updated'] = time();
        $status['message']      = sprintf(__('Indexing %1$s (page %2$d of %3$d)...', 'swiftsearch-for-algolia'), $type, $page, $total_pages);

        if ($complete) {
            if ($type === 'posts') {
                $status['message'] = __('Posts complete. Moving to Terms...', 'swiftsearch-for-algolia');
            } elseif ($type === 'terms') {
                $status['message'] = __('Terms complete. Moving to Users...', 'swiftsearch-for-algolia');
            } elseif ($type === 'users') {
                $status['active']                 = false;
                $status['message']                = __('Complete!', 'swiftsearch-for-algolia');
                $status['last_sync_completed_at'] = time();
            }
        }
        update_option('swift_search_algolia_index_status', $status);

        return new WP_REST_Response(array(
            'success' => true,
            'data'    => array(
                'processed'   => $processed,
                'page'        => $page,
                'total_pages' => $total_pages,
                'complete'    => $complete,
                'type'        => $type,
            ),
        ), 200);
    }

    /**
     * Start Background Sync.
     */
    public function handle_sync_start($request)
    {
        delete_option('swift_search_algolia_debug_loopback');
        $indexer = new Indexer();
        $total   = $indexer->start_bulk_index();

        return new WP_REST_Response(array(
            'success' => true,
            'data'    => array(
                'message'    => __('Algolia indexing started.', 'swiftsearch-for-algolia'),
                'total_jobs' => $total,
            ),
        ), 200);
    }

    /**
     * Get Background Sync Status.
     */
    public function handle_sync_status($request)
    {
        $status = get_option('swift_search_algolia_index_status', array());

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $error_count = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(JSON_LENGTH(failed_ids)) FROM {$wpdb->prefix}swift_search_algolia_batch_logs WHERE status = %s",
            'failed'
        ));
        $error_count = $error_count ? (int) $error_count : 0;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $raw_errors = $wpdb->get_results($wpdb->prepare(
            "SELECT failed_ids, error_message, created_at FROM {$wpdb->prefix}swift_search_algolia_batch_logs WHERE status = %s ORDER BY created_at DESC LIMIT %d",
            'failed',
            20
        ));

        $errors = array();
        foreach ($raw_errors as $row) {
            $ids = json_decode($row->failed_ids, true);
            if (is_array($ids)) {
                foreach ($ids as $err_item) {
                    $err_item['timestamp'] = $row->created_at;
                    $errors[]              = $err_item;
                }
            }
        }
        $errors = array_slice($errors, 0, 50);

        $response = array(
            'active'                 => false,
            'processed'              => 0,
            'total'                  => 0,
            'message'                => __('Idle', 'swiftsearch-for-algolia'),
            'last_sync_completed_at' => null,
            'error_count'            => $error_count,
            'errors'                 => $errors,
            'debug_loopback'         => get_option('swift_search_algolia_debug_loopback'),
        );

        if (!empty($status)) {
            $response = array_merge($response, $status);
        }

        return new WP_REST_Response(array(
            'success' => true,
            'data'    => $response,
        ), 200);
    }

    /**
     * Handle Settings Update.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_settings($request)
    {
        $params           = $request->get_params();
        $current_settings = get_option('swift_search_algolia_settings', array());
        $section          = sanitize_text_field($params['section'] ?? '');

        // 1. Content Section
        if ($section === 'content') {
            $types = isset($params['post_types']) && is_array($params['post_types'])
                ? array_map('sanitize_text_field', $params['post_types'])
                : array();
            $current_settings['indexed_post_types'] = $types;

            $taxes = isset($params['taxonomies']) && is_array($params['taxonomies'])
                ? array_map('sanitize_text_field', $params['taxonomies'])
                : array();
            $current_settings['indexed_taxonomies'] = $taxes;

            $current_settings['indexed_users'] = isset($params['index_users'])
                ? filter_var($params['index_users'], FILTER_VALIDATE_BOOLEAN)
                : false;

            // Custom Fields
            $custom_fields = isset($params['custom_fields']) && is_array($params['custom_fields'])
                ? $params['custom_fields']
                : array();

            $sanitized_fields = array();
            foreach ($custom_fields as $pt => $fields) {
                if (!is_array($fields)) continue;
                $sanitized_fields[$pt] = array();
                foreach ($fields as $field) {
                    if (empty($field['key']) || empty($field['name'])) continue;
                    $sanitized_fields[$pt][] = array(
                        'key'   => sanitize_text_field($field['key']),
                        'name'  => sanitize_text_field($field['name']),
                        'type'  => sanitize_text_field($field['type'] ?? 'string'),
                        'facet' => !empty($field['facet']),
                    );
                }
                if (empty($sanitized_fields[$pt])) {
                    unset($sanitized_fields[$pt]);
                }
            }
            $current_settings['custom_fields'] = $sanitized_fields;

            if (isset($params['override_default'])) {
                update_option('swift_search_algolia_override_default', filter_var($params['override_default'], FILTER_VALIDATE_BOOLEAN));
            }
        }

        // 2. Experience Section (Relevance, Synonyms, Facets)
        if ($section === 'experience') {
            $relevance = isset($params['relevance_settings']) && is_array($params['relevance_settings'])
                ? $params['relevance_settings']
                : array();

            // Field Weights
            $weights = !empty($relevance['weights']) ? $relevance['weights'] : ($params['weights'] ?? array());
            if (!empty($weights) && is_array($weights)) {
                foreach ($weights as $key => $weight) {
                    $current_settings['weights'][sanitize_key($key)] = absint($weight);
                }
            }

            // Synonyms (One-Way and Multi-Way)
            $synonyms = !empty($relevance['synonyms']) ? $relevance['synonyms'] : ($params['synonyms'] ?? array());
            if (is_array($synonyms)) {
                $clean_synonyms   = array();
                $algolia_synonyms = array();

                foreach ($synonyms as $idx => $group) {
                    $syn_list = isset($group['synonyms']) && is_array($group['synonyms']) ? $group['synonyms'] : array();
                    if (empty($syn_list)) continue;

                    $sanitized_list = array_values(array_unique(array_map('sanitize_text_field', $syn_list)));
                    $root           = !empty($group['root']) ? sanitize_text_field($group['root']) : '';
                    $syn_type       = !empty($group['type']) ? sanitize_text_field($group['type']) : (!empty($root) ? 'oneWaySynonym' : 'synonym');

                    $clean_synonyms[] = array(
                        'root'     => $root,
                        'type'     => $syn_type,
                        'synonyms' => $sanitized_list,
                    );

                    $syn_id = 'synonym_' . $idx;
                    if ($syn_type === 'oneWaySynonym' && !empty($root)) {
                        $algolia_synonyms[] = array(
                            'objectID' => $syn_id,
                            'type'     => 'oneWaySynonym',
                            'input'    => $root,
                            'synonyms' => $sanitized_list,
                        );
                    } else {
                        $all_words = !empty($root) ? array_merge(array($root), $sanitized_list) : $sanitized_list;
                        $algolia_synonyms[] = array(
                            'objectID' => $syn_id,
                            'type'     => 'synonym',
                            'synonyms' => array_values(array_unique($all_words)),
                        );
                    }
                }

                $current_settings['synonyms'] = $clean_synonyms;

                // Push synonyms directly to Algolia
                if (!empty($current_settings['admin_api_key'])) {
                    $client = new Client($current_settings);
                    if (!empty($algolia_synonyms)) {
                        $client->save_synonyms('posts', $algolia_synonyms, true, true);
                    } else {
                        $client->clear_synonyms('posts');
                    }
                }
            }

            // Facets
            if (isset($params['facets_config']) && is_array($params['facets_config'])) {
                $sanitized_facets = array();
                foreach ($params['facets_config'] as $facet) {
                    if (empty($facet['source'])) continue;
                    $sanitized_facets[] = array(
                        'source'    => sanitize_text_field($facet['source']),
                        'type'      => sanitize_text_field($facet['type'] ?? 'taxonomy'),
                        'label'     => sanitize_text_field($facet['label'] ?? ''),
                        'target'    => sanitize_text_field($facet['target'] ?? ''),
                        'data_type' => sanitize_text_field($facet['data_type'] ?? 'string'),
                        'enabled'   => isset($facet['enabled']) ? filter_var($facet['enabled'], FILTER_VALIDATE_BOOLEAN) : false,
                    );
                }
                $current_settings['facets_config'] = $sanitized_facets;
            }

            // General Experience
            if (isset($params['experience_settings']) && is_array($params['experience_settings'])) {
                $new_exp = $params['experience_settings'];
                $current_settings['experience'] = array(
                    'typo_tolerance' => isset($new_exp['typo_tolerance']) ? filter_var($new_exp['typo_tolerance'], FILTER_VALIDATE_BOOLEAN) : true,
                    'sort_enabled'   => isset($new_exp['sort_enabled']) ? filter_var($new_exp['sort_enabled'], FILTER_VALIDATE_BOOLEAN) : true,
                    'mobile_btn'     => isset($new_exp['mobile_btn']) ? filter_var($new_exp['mobile_btn'], FILTER_VALIDATE_BOOLEAN) : false,
                    'instant_search' => isset($new_exp['instant_search']) ? filter_var($new_exp['instant_search'], FILTER_VALIDATE_BOOLEAN) : true,
                    'show_thumbnail' => isset($new_exp['show_thumbnail']) ? filter_var($new_exp['show_thumbnail'], FILTER_VALIDATE_BOOLEAN) : true,
                    'show_price'     => isset($new_exp['show_price']) ? filter_var($new_exp['show_price'], FILTER_VALIDATE_BOOLEAN) : true,
                    'show_excerpt'   => isset($new_exp['show_excerpt']) ? filter_var($new_exp['show_excerpt'], FILTER_VALIDATE_BOOLEAN) : false,
                    'limit'          => isset($new_exp['limit']) ? absint($new_exp['limit']) : 10,
                    'layout'         => isset($new_exp['layout']) ? sanitize_text_field($new_exp['layout']) : 'overlay',
                    'browse_mode'    => isset($new_exp['browse_mode']) ? filter_var($new_exp['browse_mode'], FILTER_VALIDATE_BOOLEAN) : false,
                    'search_scope'   => array(
                        'posts' => true,
                        'terms' => isset($new_exp['search_scope']['terms']) ? filter_var($new_exp['search_scope']['terms'], FILTER_VALIDATE_BOOLEAN) : false,
                        'users' => isset($new_exp['search_scope']['users']) ? filter_var($new_exp['search_scope']['users'], FILTER_VALIDATE_BOOLEAN) : false,
                    ),
                    'post_types'     => isset($new_exp['post_types']) && is_array($new_exp['post_types']) ? array_map('sanitize_text_field', $new_exp['post_types']) : array(),
                );
            }

            // Algolia Logo Badge attribution toggle
            if (isset($params['show_algolia_badge'])) {
                $current_settings['show_algolia_badge'] = filter_var($params['show_algolia_badge'], FILTER_VALIDATE_BOOLEAN);
            }

            // Re-sync index settings to Algolia (updates searchableAttributes and facets)
            update_option('swift_search_algolia_settings', $current_settings);
            if (!empty($current_settings['admin_api_key'])) {
                $indexer = new Indexer();
                $indexer->create_collection();
            }
        }

        // 3. Styling Section
        if ($section === 'styling') {
            $current_settings['styling'] = array(
                'primary_color' => sanitize_text_field($params['primary_color'] ?? '#0052cc'),
                'card_bg'       => sanitize_text_field($params['card_bg'] ?? '#ffffff'),
                'text_color'    => sanitize_text_field($params['text_color'] ?? '#1f2937'),
                'border_radius' => absint($params['border_radius'] ?? 16),
            );
        }

        update_option('swift_search_algolia_settings', $current_settings);

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Handle Index Reset.
     *
     * @return WP_REST_Response
     */
    public function handle_reset()
    {
        $indexer = new Indexer();
        $indexer->delete_collection();

        try {
            $indexer->create_collection();
        } catch (\Exception $e) {
            // Silently proceed
        }

        delete_option('swift_search_algolia_index_status');
        delete_option('swift_search_algolia_debug_loopback');

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Handle Search Log (Analytics).
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_log($request)
    {
        $params = $request->get_params();
        $query  = sanitize_text_field($params['query'] ?? '');
        $hits   = intval($params['hits'] ?? 0);

        if (empty($query)) {
            return new WP_REST_Response(array('success' => false), 400);
        }

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}swift_search_algolia_logs (query, frequency, result_count, created_at, updated_at) 
             VALUES (%s, 1, %d, NOW(), NOW()) 
             ON DUPLICATE KEY UPDATE frequency = frequency + 1, result_count = VALUES(result_count), updated_at = NOW()",
            $query,
            $hits
        ));

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Get Analytics Data.
     * 
     * @return WP_REST_Response
     */
    public function get_analytics()
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $top_queries = $wpdb->get_results($wpdb->prepare("
            SELECT query, frequency as count, updated_at as last_hit 
            FROM {$wpdb->prefix}swift_search_algolia_logs 
            WHERE frequency > %d 
            ORDER BY frequency DESC 
            LIMIT %d
        ", 0, 10));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $no_results = $wpdb->get_results($wpdb->prepare("
            SELECT query, frequency as count 
            FROM {$wpdb->prefix}swift_search_algolia_logs 
            WHERE result_count = %d 
            ORDER BY frequency DESC 
            LIMIT %d
        ", 0, 10));

        return new WP_REST_Response(array(
            'success' => true,
            'data'    => array(
                'top_queries' => $top_queries,
                'no_results'  => $no_results,
            ),
        ), 200);
    }

    /**
     * Handle Item Search for Pinning (Autocomplete).
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_pinning_search($request)
    {
        $term = sanitize_text_field($request->get_param('term'));
        if (empty($term)) {
            return new WP_REST_Response(array('success' => true, 'data' => array()), 200);
        }

        $args = array(
            'post_type'      => array('product', 'post', 'page'),
            'post_status'    => 'publish',
            's'              => $term,
            'posts_per_page' => 10,
        );

        $query   = new \WP_Query($args);
        $results = array();

        foreach ($query->posts as $post) {
            $results[] = array(
                'id'    => (string) $post->ID,
                'title' => $post->post_title,
                'type'  => $post->post_type,
            );
        }

        return new WP_REST_Response(array('success' => true, 'data' => $results), 200);
    }

    /**
     * Handle Saving Pinned Items (stored in WP options, rendered client-side).
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_pinning_save($request)
    {
        $items = $request->get_param('items');
        if (!is_array($items)) {
            $items = array();
        }

        update_option('swift_search_algolia_pinned_items', $items);

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Get Pinned Items.
     * 
     * @return WP_REST_Response
     */
    public function get_pinned_items()
    {
        $items = get_option('swift_search_algolia_pinned_items', array());
        return new WP_REST_Response(array('success' => true, 'data' => $items), 200);
    }

    /**
     * Diagnose Algolia API connectivity and health.
     */
    public function handle_diagnose(WP_REST_Request $request)
    {
        $settings = get_option('swift_search_algolia_settings', array());
        if (empty($settings['admin_api_key'])) {
            return new WP_REST_Response(array('success' => false, 'message' => __('No Admin API Key found.', 'swiftsearch-for-algolia')), 400);
        }

        $client = new Client($settings);
        $start  = microtime(true);
        $res    = $client->test_connection();
        $timing = round((microtime(true) - $start) * 1000, 2);

        $indices = $client->list_indices();

        return new WP_REST_Response(array(
            'success' => true,
            'report'  => array(
                'connected'       => (bool) $res,
                'latency_ms'      => $timing,
                'indices_count'   => is_array($indices) ? count($indices) : 0,
                'app_id'          => $settings['app_id'] ?? '',
                'last_error'      => $client->get_last_error(),
            ),
        ), 200);
    }
}
