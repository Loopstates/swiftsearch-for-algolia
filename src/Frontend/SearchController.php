<?php

namespace SwiftSearchAlgolia\Frontend;

use SwiftSearchAlgolia\Engine\Schema;
use SwiftSearchAlgolia\Engine\ConfigLoader;
use SwiftSearchAlgolia\Client\Client;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class SearchController
 *
 * Handles frontend search scripts, styles, and shortcodes for SwiftSearch for Algolia.
 */
class SearchController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_shortcode('swift_search_algolia', array($this, 'render_shortcode'));
        if (!shortcode_exists('swift_search')) {
            add_shortcode('swift_search', array($this, 'render_shortcode'));
        }
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_filter('get_search_form', array($this, 'override_search_form'));
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_assets()
    {
        wp_register_style(
            'swift-search-algolia-frontend',
            SWIFT_SEARCH_ALGOLIA_URL . 'assets/css/search.css',
            array(),
            SWIFT_SEARCH_ALGOLIA_VERSION
        );

        wp_register_script(
            'swift-search-algolia-lite',
            SWIFT_SEARCH_ALGOLIA_URL . 'assets/vendor/algoliasearch-lite.js',
            array(),
            '4.24.0',
            true
        );

        wp_register_script(
            'swift-search-algolia-frontend',
            SWIFT_SEARCH_ALGOLIA_URL . 'assets/js/search.js',
            array('swift-search-algolia-lite'),
            SWIFT_SEARCH_ALGOLIA_VERSION,
            true
        );

        // Styling Custom Properties
        $settings = get_option('swift_search_algolia_settings', array());
        $styling  = isset($settings['styling']) ? $settings['styling'] : array();

        if (!empty($styling)) {
            $primary = !empty($styling['primary_color']) ? $styling['primary_color'] : '#1e3a8a';
            if (in_array(strtolower($primary), array('#0052cc', '#ff0055', '#2563eb'), true)) {
                $primary = '#1e3a8a';
            }
            $text    = !empty($styling['text_color']) ? $styling['text_color'] : '#1f2937';
            $bg      = !empty($styling['card_bg']) ? $styling['card_bg'] : '#ffffff';
            $radius  = isset($styling['border_radius']) ? (int) $styling['border_radius'] : 16;

            $primary_rgb = $this->hex2rgb($primary);

            $custom_css = "
                .ss-algolia-wrapper, .ss-wrapper[data-engine='algolia'] {
                    --ss-primary: " . esc_html($primary) . " !important;
                    --ss-primary-rgb: " . absint($primary_rgb[0]) . ", " . absint($primary_rgb[1]) . ", " . absint($primary_rgb[2]) . " !important;
                    --ss-text-main: " . esc_html($text) . " !important;
                    --ss-card-bg: " . esc_html($bg) . " !important;
                    --ss-radius: " . absint($radius) . "px !important;
                }
            ";
            wp_add_inline_style('swift-search-algolia-frontend', $custom_css);
        }

        $search_api_key = $settings['search_api_key'] ?? ($settings['search_key'] ?? '');
        $app_id         = $settings['app_id'] ?? ($settings['application_id'] ?? '');

        if (!empty($search_api_key) && !empty($app_id)) {
            $client     = new Client($settings);
            $base_index = $client->format_index_name('posts');

            $replicas = array(
                'price_asc'  => Schema::get_replica_index_name($base_index, 'price_asc'),
                'price_desc' => Schema::get_replica_index_name($base_index, 'price_desc'),
                'date_desc'  => Schema::get_replica_index_name($base_index, 'date_desc'),
                'date_asc'   => Schema::get_replica_index_name($base_index, 'date_asc'),
            );

            $currency_symbol = '$';
            $currency_pos    = 'left';
            if (function_exists('get_woocommerce_currency_symbol')) {
                $currency_symbol = get_woocommerce_currency_symbol();
            }
            if (function_exists('get_option')) {
                $currency_pos = get_option('woocommerce_currency_pos', 'left');
            }

            $vars = array(
                'appId'              => $app_id,
                'searchApiKey'       => $search_api_key,
                'indexName'          => $base_index,
                'replicas'           => $replicas,
                'showAlgoliaBadge'   => isset($settings['show_algolia_badge']) ? (bool) $settings['show_algolia_badge'] : true,
                'indexed_post_types' => isset($settings['indexed_post_types']) ? $settings['indexed_post_types'] : ConfigLoader::get_default_post_types(),
                'indexed_taxonomies' => isset($settings['indexed_taxonomies']) ? $settings['indexed_taxonomies'] : ConfigLoader::get_default_taxonomies(),
                'indexed_users'      => isset($settings['indexed_users']) ? (bool) $settings['indexed_users'] : false,
                'experience'         => isset($settings['experience']) ? $settings['experience'] : array(),
                'facets_config'      => isset($settings['facets_config']) ? $settings['facets_config'] : array(),
                'custom_fields'      => isset($settings['custom_fields']) ? $settings['custom_fields'] : array(),
                'pinned_items'       => get_option('swift_search_algolia_pinned_items', array()),
                'weights'            => isset($settings['weights']) ? $settings['weights'] : array(),
                'apiUrl'             => rest_url('swift-search-algolia/v1'),
                'nonce'              => wp_create_nonce('swift_search_algolia_log_nonce'),
                'currencySymbol'     => $currency_symbol,
                'currencyPosition'   => $currency_pos,
                // Legacy compatibility keys
                'apiKey'             => $search_api_key,
                'collection'         => $base_index,
            );

            $vars = apply_filters('swift_search_algolia_vars', $vars, $settings);

            wp_localize_script('swift-search-algolia-frontend', 'swiftSearchAlgoliaVars', $vars);
            wp_localize_script('swift-search-algolia-frontend', 'swiftSearchVars', $vars);
        }
    }

    /**
     * Override default WordPress search form.
     *
     * @param string $form
     * @return string
     */
    public function override_search_form($form)
    {
        $override = get_option('swift_search_algolia_override_default', false);
        if (!$override) {
            return $form;
        }

        return $this->render_shortcode(array());
    }

    /**
     * Render Shortcode.
     *
     * @param array $atts
     * @return string
     */
    public function render_shortcode($atts)
    {
        $settings = get_option('swift_search_algolia_settings', array());
        $exp      = isset($settings['experience']) ? $settings['experience'] : array();

        $default_limit   = isset($exp['limit']) ? intval($exp['limit']) : 10;
        $default_thumb   = (isset($exp['show_thumbnail']) && $exp['show_thumbnail'] === false) ? 'false' : 'true';
        $default_price   = (isset($exp['show_price']) && $exp['show_price'] === false) ? 'false' : 'true';
        $default_excerpt = (isset($exp['show_excerpt']) && $exp['show_excerpt'] === true) ? 'true' : 'false';
        $default_instant = (isset($exp['instant_search']) && $exp['instant_search'] === false) ? 'false' : 'true';
        $default_layout  = isset($exp['layout']) ? sanitize_text_field($exp['layout']) : ((isset($exp['browse_mode']) && $exp['browse_mode'] === true) ? 'catalog' : 'overlay');

        $a = shortcode_atts(array(
            'placeholder'    => __('Search...', 'swiftsearch-for-algolia'),
            'limit'          => $default_limit,
            'show_thumbnail' => $default_thumb,
            'show_price'     => $default_price,
            'show_excerpt'   => $default_excerpt,
            'instant_search' => $default_instant,
            'scope'          => 'default',
            'post_types'     => '',
            'layout'         => $default_layout,
            'browse_mode'    => '',
        ), $atts);

        $a = apply_filters('swift_search_algolia_shortcode_args', $a, $atts);

        if (!empty($a['browse_mode'])) {
            $a['layout'] = ($a['browse_mode'] === 'true') ? 'catalog' : 'overlay';
        }

        wp_enqueue_style('swift-search-algolia-frontend');
        wp_enqueue_script('swift-search-algolia-lite');
        wp_enqueue_script('swift-search-algolia-frontend');

        $instance_id = function_exists('wp_unique_id') ? wp_unique_id('ss-algolia-') : ('ss-algolia-' . uniqid());

        ob_start();
        ?>
        <div id="<?php echo esc_attr($instance_id); ?>" class="ss-wrapper ss-algolia-wrapper ss-layout-<?php echo esc_attr($a['layout']); ?>" 
            data-engine="algolia"
            data-limit="<?php echo esc_attr($a['limit']); ?>"
            data-thumb="<?php echo esc_attr($a['show_thumbnail']); ?>" data-price="<?php echo esc_attr($a['show_price']); ?>"
            data-excerpt="<?php echo esc_attr($a['show_excerpt']); ?>"
            data-instant="<?php echo esc_attr($a['instant_search']); ?>" data-scope="<?php echo esc_attr($a['scope']); ?>"
            data-post-types="<?php echo esc_attr($a['post_types']); ?>"
            data-layout="<?php echo esc_attr($a['layout']); ?>"
            data-browse="<?php echo esc_attr(($a['layout'] === 'catalog') ? 'true' : 'false'); ?>">
            <div class="ss-search-box">
                <input type="text" id="<?php echo esc_attr($instance_id . '-input'); ?>" class="ss-search-input" placeholder="<?php echo esc_attr($a['placeholder']); ?>"
                    autocomplete="off">
                <span class="ss-loader" style="display:none;"></span>
            </div>
            <div class="ss-results-container" style="<?php echo esc_attr(($a['layout'] === 'catalog') ? '' : 'display:none;'); ?>">
                <div class="ss-facets"></div>
                <div class="ss-hits"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Helper: Convert Hex to RGB array.
     */
    private function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return array($r, $g, $b);
    }
}
