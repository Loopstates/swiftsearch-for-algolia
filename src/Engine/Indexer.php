<?php
namespace SwiftSearchAlgolia\Engine;

use SwiftSearchAlgolia\Client\Client;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Indexer
 *
 * Handles indexing of content to Algolia.
 */
class Indexer
{
    protected $client;
    protected $builder;
    protected $config_loader;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->client        = new Client();
        $this->config_loader = new ConfigLoader();

        // Hook into WP lifecycle
        add_action('save_post', array($this, 'handle_save_hook'), 10, 3);
        add_action('delete_post', array($this, 'handle_delete_hook'));

        // Hook: Terms
        add_action('created_term', array($this, 'handle_term_save_hook'), 10, 3);
        add_action('edited_term', array($this, 'handle_term_save_hook'), 10, 3);
        add_action('delete_term', array($this, 'handle_term_delete_hook'), 10, 3);

        // Hook: Users
        add_action('user_register', array($this, 'handle_user_save_hook'));
        add_action('profile_update', array($this, 'handle_user_save_hook'));
        add_action('delete_user', array($this, 'handle_user_delete_hook'));

        // Hook: WooCommerce & Meta Safety
        add_action('woocommerce_update_product', array($this, 'handle_save_hook'), 10, 1);
        add_action('woocommerce_reduce_order_stock', array($this, 'handle_wc_order_stock_hook'));
        add_action('transition_post_status', array($this, 'handle_status_transition'), 10, 3);

        // Register background processing hooks
        add_action('swift_search_algolia_async_index_post', array($this, 'index_post'));
        add_action('swift_search_algolia_async_delete_post', array($this, 'delete_post_from_index'));

        add_action('swift_search_algolia_async_index_term', array($this, 'index_term'));
        add_action('swift_search_algolia_async_delete_term', array($this, 'delete_term_from_index'));

        add_action('swift_search_algolia_async_index_user', array($this, 'index_user'));
        add_action('swift_search_algolia_async_batch_process', array($this, 'process_bulk_batch'), 10, 2);
    }

    /**
     * Get base index name (default 'posts').
     *
     * @return string
     */
    public function get_base_index_name()
    {
        return 'posts';
    }

    /**
     * Start Bulk Indexing.
     * Calculates totals and triggers first batch.
     */
    public function start_bulk_index()
    {
        $config = $this->config_loader->get_config();

        // 1. Configure settings and replicas prior to first push
        $this->create_collection();

        // 2. Calculate Totals
        $post_types = isset($config['indexed_post_types']) ? (array) $config['indexed_post_types'] : ConfigLoader::get_default_post_types();

        $args = array(
            'post_type'        => $post_types,
            'post_status'      => 'publish',
            'fields'           => 'ids',
            'posts_per_page'   => -1,
            'suppress_filters' => true,
        );

        $query = new \WP_Query($args);
        $total = $query->found_posts;

        // 3. Initialize Status
        $status = array(
            'active'          => true,
            'total'           => $total,
            'processed'       => 0,
            'last_sync_start' => time(),
            'message'         => __('Initializing Algolia sync...', 'swiftsearch-for-algolia'),
        );
        update_option('swift_search_algolia_index_status', $status);
        delete_option('swift_search_algolia_sync_errors');

        // 4. Schedule First Batch
        $bg_process = new BackgroundProcess();
        $bg_process->dispatch(array(
            'type'   => 'batch_process',
            'offset' => 0,
            'limit'  => 50,
        ), true);

        return $total;
    }

    /**
     * Recursive Worker: Process Batch.
     *
     * @param int $offset
     * @param int $limit
     */
    public function process_bulk_batch($offset, $limit = 50)
    {
        $config     = $this->config_loader->get_config();
        $post_types = isset($config['indexed_post_types']) ? (array) $config['indexed_post_types'] : ConfigLoader::get_default_post_types();

        $args = array(
            'post_type'        => $post_types,
            'post_status'      => 'publish',
            'fields'           => 'ids',
            'posts_per_page'   => $limit,
            'offset'           => $offset,
            'orderby'          => 'ID',
            'order'            => 'ASC',
            'suppress_filters' => true,
        );

        $query = new \WP_Query($args);
        $ids   = $query->posts;

        if (empty($ids)) {
            // Indexing Completed
            update_option('swift_search_algolia_index_status', array(
                'active'                 => false,
                'total'                  => $query->found_posts,
                'processed'              => $offset,
                'message'                => __('Sync Complete!', 'swiftsearch-for-algolia'),
                'last_updated'           => time(),
                'last_sync_completed_at' => time(),
            ));
            return;
        }

        // Process documents in batch
        $documents    = array();
        $batch_errors = array();

        foreach ($ids as $id) {
            try {
                $doc = $this->index_post($id, true);
                if ($doc) {
                    $documents[] = $doc;
                }
            } catch (\Exception $e) {
                $batch_errors[] = array('id' => $id, 'error' => $e->getMessage());
            }
        }

        // Bulk import via Algolia save_objects
        if (!empty($documents)) {
            $result = $this->client->save_objects($this->get_base_index_name(), $documents);
            if ($result === false) {
                $batch_errors[] = array('id' => 'BATCH_API', 'error' => $this->client->get_last_error());
            }
        }

        // Log batch results
        $this->log_batch($offset, $batch_errors);

        // Update progress status
        $new_processed          = $offset + count($ids);
        $status                 = get_option('swift_search_algolia_index_status', array());
        $status['processed']    = $new_processed;
        $status['last_updated'] = time();
        /* translators: 1: Processed document count, 2: Total document count */
        $status['message']      = sprintf(__('Indexed %1$d / %2$d', 'swiftsearch-for-algolia'), $new_processed, $status['total'] ?? $new_processed);
        update_option('swift_search_algolia_index_status', $status);

        // Schedule next batch or finalize
        if ($new_processed < ($status['total'] ?? 0)) {
            $bg_process = new BackgroundProcess();
            $bg_process->dispatch(array(
                'type'   => 'batch_process',
                'offset' => $new_processed,
                'limit'  => $limit,
            ));
        } else {
            update_option('swift_search_algolia_index_status', array(
                'active'                 => false,
                'total'                  => $status['total'] ?? $new_processed,
                'processed'              => $status['total'] ?? $new_processed,
                'message'                => __('Sync Complete!', 'swiftsearch-for-algolia'),
                'last_updated'           => time(),
                'last_sync_completed_at' => time(),
            ));

            $this->cleanup_logs();
        }
    }

    private function log_batch($offset, $errors)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'swift_search_algolia_batch_logs';

        $status     = empty($errors) ? 'success' : 'failed';
        /* translators: %d: Number of batch errors */
        $error_msg  = empty($errors) ? null : sprintf(__('Batch completed with %d errors.', 'swiftsearch-for-algolia'), count($errors));
        $failed_ids = empty($errors) ? null : wp_json_encode($errors);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert(
            $table,
            array(
                'batch_offset'  => $offset,
                'status'        => $status,
                'error_message' => $error_msg,
                'failed_ids'    => $failed_ids,
                'created_at'    => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }

    private function cleanup_logs()
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query("DELETE FROM {$wpdb->prefix}swift_search_algolia_batch_logs WHERE created_at < NOW() - INTERVAL 7 DAY");
    }

    /**
     * Hook: Schedule Indexing on Save.
     *
     * @param int $post_id
     */
    public function handle_save_hook($post_id, $post = null, $update = null)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('swift_search_algolia_async_index_post', array('post_id' => $post_id));
        } else {
            wp_schedule_single_event(time(), 'swift_search_algolia_async_index_post', array($post_id));
        }
    }

    /**
     * Hook: Schedule Deletion.
     *
     * @param int $post_id
     */
    public function handle_delete_hook($post_id)
    {
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('swift_search_algolia_async_delete_post', array('post_id' => $post_id));
        } else {
            wp_schedule_single_event(time(), 'swift_search_algolia_async_delete_post', array($post_id));
        }
    }

    // --- Terms ---

    public function handle_term_save_hook($term_id, $tt_id = null, $taxonomy = null)
    {
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('swift_search_algolia_async_index_term', array('term_id' => $term_id));
        } else {
            wp_schedule_single_event(time(), 'swift_search_algolia_async_index_term', array($term_id));
        }
    }

    public function handle_term_delete_hook($term_id, $tt_id = null, $taxonomy = null)
    {
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('swift_search_algolia_async_delete_term', array('term_id' => $term_id));
        } else {
            wp_schedule_single_event(time(), 'swift_search_algolia_async_delete_term', array($term_id));
        }
    }

    public function index_term($term_id)
    {
        $this->ensure_schema_safety();
        $term = get_term($term_id);
        if (!$term) {
            return;
        }

        $indexer  = new TermIndexer($this->config_loader);
        $document = $indexer->build($term);

        if ($document) {
            $this->client->save_objects('terms', array($document));
        }
    }

    public function delete_term_from_index($term_id)
    {
        $this->client->delete_objects('terms', array('term_' . $term_id));
    }

    // --- Users ---

    public function handle_user_save_hook($user_id, $old_user_data = null)
    {
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('swift_search_algolia_async_index_user', array('user_id' => $user_id));
        } else {
            wp_schedule_single_event(time(), 'swift_search_algolia_async_index_user', array($user_id));
        }
    }

    public function handle_user_delete_hook($user_id)
    {
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('swift_search_algolia_async_delete_user', array('user_id' => $user_id));
        } else {
            wp_schedule_single_event(time(), 'swift_search_algolia_async_delete_user', array($user_id));
        }
    }

    public function index_user($user_id)
    {
        $this->ensure_schema_safety();
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }

        $indexer  = new UserIndexer($this->config_loader);
        $document = $indexer->build($user);

        if ($document) {
            $this->client->save_objects('users', array($document));
        } else {
            $this->delete_user_from_index($user_id);
        }
    }

    public function delete_user_from_index($user_id)
    {
        $this->client->delete_objects('users', array('user_' . $user_id));
    }

    /**
     * WORKER: Index a single post.
     *
     * @param int  $post_id
     * @param bool $return_doc If true, returns document array instead of sending to API.
     * @return array|void
     */
    public function index_post($post_id, $return_doc = false)
    {
        $this->ensure_schema_safety();

        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        $this->builder = new DocumentBuilder();
        $document      = $this->builder->build($post);

        if (!$document) {
            if (!$return_doc) {
                $this->delete_post_from_index($post_id);
            }
            return;
        }

        if ($return_doc) {
            return $document;
        }

        $this->client->save_objects($this->get_base_index_name(), array($document));
    }

    private function ensure_schema_safety()
    {
        $config       = $this->config_loader->get_config();
        $base_index   = $this->get_base_index_name();
        $current_hash = Schema::get_hash($config, $base_index);
        $stored_hash  = get_option('swift_search_algolia_schema_hash');

        if ($stored_hash && $current_hash !== $stored_hash) {
            update_option('swift_search_algolia_schema_mismatch', true);
        }
    }

    /**
     * Public alias for batch processing.
     */
    public function update_post($post_id, $post)
    {
        $this->index_post($post_id);
    }

    public function delete_post_from_index($post_id)
    {
        $this->client->delete_objects($this->get_base_index_name(), array((string) $post_id));
    }

    // --- Extra Hooks ---

    public function handle_wc_order_stock_hook($order)
    {
        if (!$order) {
            return;
        }

        $items = $order->get_items();
        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            $this->handle_save_hook($product_id);
        }
    }

    public function handle_status_transition($new_status, $old_status, $post)
    {
        if ($new_status === $old_status) {
            return;
        }

        if ($new_status === 'trash' || $new_status === 'draft' || $new_status === 'auto-draft') {
            $this->handle_delete_hook($post->ID);
        } elseif ($new_status === 'publish') {
            $this->handle_save_hook($post->ID);
        }
    }

    /**
     * Configure Index Settings and Replicas for Algolia.
     */
    public function create_collection()
    {
        $config     = $this->config_loader->get_config();
        $base_index = $this->get_base_index_name();

        // 1. Calculate and store settings hash
        $hash = Schema::get_hash($config, $base_index);
        update_option('swift_search_algolia_schema_hash', $hash);
        delete_option('swift_search_algolia_schema_mismatch');

        // 2. Push primary posts settings
        $posts_settings = Schema::get_settings($config, $base_index);
        $this->client->set_settings($base_index, $posts_settings, true);

        // 3. Configure replicas ranking
        $sort_options = $config['sort_options'] ?? ConfigLoader::get_default_sort_options();
        foreach ($sort_options as $sort_key) {
            $replica_name     = Schema::get_replica_index_name($base_index, $sort_key);
            $replica_settings = Schema::get_replica_settings($sort_key);
            $this->client->set_settings($replica_name, $replica_settings);
        }

        // 4. Push terms and users settings
        $terms_settings = Schema::get_terms_settings($config);
        $this->client->set_settings('terms', $terms_settings);

        $users_settings = Schema::get_users_settings($config);
        $this->client->set_settings('users', $users_settings);

        return true;
    }

    /**
     * Delete/Clear Collections.
     */
    public function delete_collection()
    {
        $base_index = $this->get_base_index_name();
        $this->client->clear_objects($base_index);
        $this->client->clear_objects('terms');
        $this->client->clear_objects('users');

        $config       = $this->config_loader->get_config();
        $sort_options = $config['sort_options'] ?? ConfigLoader::get_default_sort_options();
        foreach ($sort_options as $sort_key) {
            $replica_name = Schema::get_replica_index_name($base_index, $sort_key);
            $this->client->clear_objects($replica_name);
        }

        return true;
    }
}
