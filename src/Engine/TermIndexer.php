<?php
namespace SwiftSearchAlgolia\Engine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TermIndexer
 *
 * Handles building Algolia documents for WP_Term objects (Categories, Tags).
 */
class TermIndexer
{
    private $config;

    public function __construct($config_loader = null)
    {
        if ($config_loader) {
            $this->config = $config_loader->get_config();
        } else {
            $this->config = get_option('swift_search_algolia_settings', array());
        }
    }

    /**
     * Build a term document for Algolia.
     *
     * @param int|object $term Term ID or object.
     * @return array|false Document or false if invalid.
     */
    public function build($term)
    {
        if (is_numeric($term)) {
            $term = get_term($term);
        }

        if (!$term || is_wp_error($term)) {
            return false;
        }

        $allowed_taxonomies = isset($this->config['indexed_taxonomies'])
            ? $this->config['indexed_taxonomies']
            : array('category', 'post_tag', 'product_cat');

        if (!in_array($term->taxonomy, $allowed_taxonomies)) {
            return false;
        }

        $url = get_term_link($term);
        if (is_wp_error($url)) {
            $url = '';
        }

        return array(
            'objectID' => 'term_' . $term->term_id,
            'id'       => (string) $term->term_id,
            'term_id'  => (int) $term->term_id,
            'name'     => $term->name,
            'slug'     => $term->slug,
            'taxonomy' => $term->taxonomy,
            'count'    => (int) $term->count,
            'url'      => (string) $url,
        );
    }
}
