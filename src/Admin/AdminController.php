<?php
namespace SwiftSearchAlgolia\Admin;

use SwiftSearchAlgolia\Core\Gatekeeper;
use SwiftSearchAlgolia\Client\Client;
use SwiftSearchAlgolia\Engine\ConfigLoader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AdminController
 *
 * Handles backend UI, menus, and localized settings for SwiftSearch for Algolia.
 */
class AdminController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Register the admin menu.
     */
    public function register_menu()
    {
        add_menu_page(
            __('SwiftSearch', 'swiftsearch-for-algolia'),
            __('SwiftSearch', 'swiftsearch-for-algolia'),
            'manage_options',
            'swift-search-algolia',
            array($this, 'render_app'),
            SWIFT_SEARCH_ALGOLIA_URL . 'assets/images/menu-icon.png',
            30
        );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets($hook)
    {
        // Restrict sidebar menu icon to 20x20px
        wp_register_style('swift-search-algolia-menu-icon', false, array(), defined('SWIFT_SEARCH_ALGOLIA_VERSION') ? SWIFT_SEARCH_ALGOLIA_VERSION : '1.0.0');
        wp_enqueue_style('swift-search-algolia-menu-icon');
        wp_add_inline_style(
            'swift-search-algolia-menu-icon',
            '#adminmenu .toplevel_page_swift-search-algolia .wp-menu-image img, #adminmenu a[href*="page=swift-search-algolia"] .wp-menu-image img { width: 20px !important; height: 20px !important; max-width: 20px !important; max-height: 20px !important; padding: 7px 0 0 0 !important; object-fit: contain !important; box-sizing: content-box !important; }'
        );

        if ($hook !== 'toplevel_page_swift-search-algolia') {
            return;
        }

        // Chart.js for Local Analytics
        wp_enqueue_script(
            'swift-search-algolia-chart-js',
            SWIFT_SEARCH_ALGOLIA_URL . 'assets/vendor/chart.js',
            array(),
            '4.5.1',
            true
        );

        // Main Admin CSS
        wp_enqueue_style(
            'swift-search-algolia-admin',
            SWIFT_SEARCH_ALGOLIA_URL . 'assets/css/admin.css',
            array(),
            SWIFT_SEARCH_ALGOLIA_VERSION . '.' . time()
        );

        // Admin JS
        wp_enqueue_script(
            'swift-search-algolia-admin',
            SWIFT_SEARCH_ALGOLIA_URL . 'assets/js/admin.js',
            array('jquery', 'swift-search-algolia-chart-js'),
            SWIFT_SEARCH_ALGOLIA_VERSION . '.' . time(),
            true
        );

        $settings = get_option('swift_search_algolia_settings', array());

        $admin_config = array(
            'isConnected'          => Gatekeeper::is_connected(),
            'canIndex'             => Gatekeeper::can_index(),
            'apiUrl'               => rest_url('swift-search-algolia/v1'),
            'nonce'                => wp_create_nonce('wp_rest'),
            'sslverify'            => apply_filters('swift_search_algolia_https_local_ssl_verify', false),
            'plan'                 => array(
                'isPaying'   => true, // All features unlocked in base plugin
                'upgradeUrl' => '#',
            ),
            'status'               => array(
                'schemaMismatch'  => (bool) get_option('swift_search_algolia_schema_mismatch', false),
                'backgroundIndex' => get_option('swift_search_algolia_index_status', array()),
                'overrideDefault' => (bool) get_option('swift_search_algolia_override_default', false),
            ),
            'relevance'            => array(
                'weights'  => isset($settings['weights']) ? $settings['weights'] : array(),
                'synonyms' => isset($settings['synonyms']) ? $settings['synonyms'] : array(),
            ),
            'experience'           => isset($settings['experience']) ? $settings['experience'] : array(),
            'show_algolia_badge'   => isset($settings['show_algolia_badge']) ? (bool) $settings['show_algolia_badge'] : true,
            'credentials'          => array(
                'app_id'         => $settings['app_id'] ?? ($settings['application_id'] ?? ''),
                'admin_api_key'  => $settings['admin_api_key'] ?? ($settings['api_key'] ?? ''),
                'search_api_key' => $settings['search_api_key'] ?? ($settings['search_key'] ?? ''),
                'index_prefix'   => $settings['index_prefix'] ?? '',
                // Fallbacks
                'host'           => $settings['app_id'] ?? '',
                'api_key'        => $settings['admin_api_key'] ?? ($settings['api_key'] ?? ''),
                'search_key'     => $settings['search_api_key'] ?? ($settings['search_key'] ?? ''),
            ),
            'indexed_post_types'   => isset($settings['indexed_post_types']) ? $settings['indexed_post_types'] : ConfigLoader::get_default_post_types(),
            'indexed_taxonomies'   => isset($settings['indexed_taxonomies']) ? $settings['indexed_taxonomies'] : ConfigLoader::get_default_taxonomies(),
            'indexed_users'        => isset($settings['indexed_users']) ? (bool) $settings['indexed_users'] : false,
            'custom_fields'        => isset($settings['custom_fields']) ? $settings['custom_fields'] : array(),
            'facets_config'        => isset($settings['facets_config']) ? $settings['facets_config'] : array(),
            'styling'              => (function() use ($settings) {
                $s = isset($settings['styling']) ? $settings['styling'] : array();
                if (!empty($s['primary_color']) && in_array(strtolower($s['primary_color']), array('#0052cc', '#ff0055', '#2563eb'), true)) {
                    $s['primary_color'] = '#1e3a8a';
                }
                return $s;
            })(),
            'available_post_types' => $this->get_public_post_types(),
            'available_taxonomies' => $this->get_public_taxonomies(),
            'available_indices'    => $this->get_active_indices(),
            'texts'                => array(
                'connecting'    => __('Connecting to Algolia...', 'swiftsearch-for-algolia'),
                'success'       => __('Connected to Algolia Successfully!', 'swiftsearch-for-algolia'),
                'error'         => __('Connection to Algolia Failed.', 'swiftsearch-for-algolia'),
                'mismatch'      => __('⚠️ Settings Out of Sync: Please perform a Full Re-Index now to update your searchable attributes and facets in Algolia.', 'swiftsearch-for-algolia'),
                'apiKeyWarning' => __('Security Notice: Keep your Admin API Key confidential. Only your Search-Only API Key is exposed to the frontend browser.', 'swiftsearch-for-algolia'),
            ),
        );

        wp_localize_script('swift-search-algolia-admin', 'swiftSearchAlgoliaConfig', $admin_config);
        wp_localize_script('swift-search-algolia-admin', 'swiftSearchConfig', $admin_config);
    }

    /**
     * Get list of public post types.
     *
     * @return array List of post types with labels.
     */
    private function get_public_post_types()
    {
        $args = array(
            'public' => true,
        );
        $post_types = get_post_types($args, 'objects');

        $data = array();
        $exclude = array('attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation');

        foreach ($post_types as $pt) {
            if (in_array($pt->name, $exclude)) {
                continue;
            }
            $data[] = array(
                'name'        => (string) $pt->name,
                'label'       => (string) $pt->label,
                'description' => empty($pt->description) ? '' : substr(wp_strip_all_tags((string) $pt->description), 0, 100),
            );
        }

        return $data;
    }

    /**
     * Get list of public taxonomies.
     *
     * @return array List of taxonomies.
     */
    private function get_public_taxonomies()
    {
        $args = array(
            'public' => true,
        );
        $taxonomies = get_taxonomies($args, 'objects');

        $data    = array();
        $exclude = array('nav_menu', 'link_category', 'post_format', 'wp_theme_styles');

        foreach ($taxonomies as $tax) {
            if (in_array($tax->name, $exclude)) {
                continue;
            }

            $term_count = wp_count_terms($tax->name);
            if (!$term_count || (int) $term_count === 0) {
                continue;
            }

            $data[] = array(
                'name'        => (string) $tax->name,
                'label'       => (string) $tax->label,
                'description' => empty($tax->description) ? '' : substr(wp_strip_all_tags((string) $tax->description), 0, 100),
            );
        }

        return $data;
    }

    /**
     * Get active indices from Algolia.
     *
     * @return array List of index names.
     */
    private function get_active_indices()
    {
        $cache_key = 'swift_search_algolia_indices';
        $cached    = get_transient($cache_key);

        if ($cached !== false) {
            return (array) $cached;
        }

        $settings = get_option('swift_search_algolia_settings');
        if (empty($settings['admin_api_key']) && empty($settings['api_key'])) {
            return array();
        }

        $client  = new Client($settings);
        $indices = $client->list_indices();

        $active = array();
        if (is_array($indices)) {
            foreach ($indices as $item) {
                if (isset($item['name'])) {
                    $active[] = $item['name'];
                }
            }
        }

        set_transient($cache_key, $active, 5 * MINUTE_IN_SECONDS);

        return $active;
    }

    /**
     * Render the main Single Page Application (SPA) wrapper.
     */
    public function render_app()
    {
        $data = array(
            'available_post_types' => $this->get_public_post_types(),
        );

        require_once SWIFT_SEARCH_ALGOLIA_PATH . 'templates/admin/app.php';
    }
}
