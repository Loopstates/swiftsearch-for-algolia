<?php
namespace SwiftSearchAlgolia\Engine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ConfigLoader
 *
 * Defines the Search Configuration locally.
 * All features are unlocked and free by default.
 */
class ConfigLoader
{
    /**
     * Centralized default post types.
     * Core WordPress defaults to ['post', 'page']. Adds 'product' only if WooCommerce is active.
     *
     * @return array
     */
    public static function get_default_post_types()
    {
        $defaults = array('post', 'page');
        if (class_exists('WooCommerce')) {
            $defaults[] = 'product';
        }
        return apply_filters('swift_search_algolia_default_post_types', $defaults);
    }

    /**
     * Centralized default taxonomies.
     * Core WordPress defaults to ['category', 'post_tag']. Adds 'product_cat' only if WooCommerce is active.
     *
     * @return array
     */
    public static function get_default_taxonomies()
    {
        $defaults = array('category', 'post_tag');
        if (class_exists('WooCommerce')) {
            $defaults[] = 'product_cat';
        }
        return apply_filters('swift_search_algolia_default_taxonomies', $defaults);
    }

    /**
     * Centralized default sort options.
     * Core WordPress defaults to ['date_desc']. Adds 'price_asc' and 'price_desc' only if WooCommerce is active.
     *
     * @return array
     */
    public static function get_default_sort_options()
    {
        $defaults = array('date_desc');
        if (class_exists('WooCommerce')) {
            $defaults = array('price_asc', 'price_desc', 'date_desc');
        }
        return apply_filters('swift_search_algolia_default_sort_options', $defaults);
    }

    /**
     * Get the unified configuration.
     *
     * @return array
     */
    public function get_config()
    {
        $saved = get_option('swift_search_algolia_settings', array());

        // Algolia Defaults
        $defaults = array(
            'ranking_rules' => array('typo', 'geo', 'words', 'filters', 'proximity', 'attribute', 'exact', 'custom'),
            'synonyms' => array(),
            'weights' => array(
                'post_title'   => 10,
                'sku'          => 8,
                'category'     => 6,
                'tag'          => 5,
                'post_excerpt' => 3,
                'post_content' => 2,
            ),
            'enable_facets'       => true,
            'show_algolia_badge'  => true,
            'indexed_post_types'  => self::get_default_post_types(),
            'indexed_taxonomies'  => self::get_default_taxonomies(),
            'sort_options'        => self::get_default_sort_options(),
            'typo_tolerance'      => true,
        );

        // Merge User Settings
        if (!empty($saved['weights']) && is_array($saved['weights'])) {
            $defaults['weights'] = $saved['weights'];
        }

        if (isset($saved['synonyms']) && is_array($saved['synonyms'])) {
            $defaults['synonyms'] = $saved['synonyms'];
        }

        if (isset($saved['facets_config']) && is_array($saved['facets_config'])) {
            $defaults['facets_config'] = $saved['facets_config'];
        }

        if (!empty($saved['indexed_post_types'])) {
            $defaults['indexed_post_types'] = (array) $saved['indexed_post_types'];
        }

        if (!empty($saved['indexed_taxonomies'])) {
            $defaults['indexed_taxonomies'] = (array) $saved['indexed_taxonomies'];
        }

        if (isset($saved['experience']) && is_array($saved['experience'])) {
            $defaults['experience'] = $saved['experience'];
        }

        if (isset($saved['custom_fields']) && is_array($saved['custom_fields'])) {
            $defaults['custom_fields'] = $saved['custom_fields'];
        }

        if (isset($saved['show_algolia_badge'])) {
            $defaults['show_algolia_badge'] = (bool) $saved['show_algolia_badge'];
        }

        if (isset($saved['sort_options']) && is_array($saved['sort_options'])) {
            $defaults['sort_options'] = $saved['sort_options'];
        }

        if (isset($saved['typo_tolerance'])) {
            $defaults['typo_tolerance'] = $saved['typo_tolerance'];
        }

        return apply_filters('swift_search_algolia_engine_config', $defaults);
    }
}
