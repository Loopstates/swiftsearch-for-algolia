<?php
namespace SwiftSearchAlgolia\Engine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Schema
 * 
 * Generates Algolia Index Settings, searchableAttributes priority,
 * attributesForFaceting, and replica sort ranking definitions.
 */
class Schema
{
    const VERSION = '2.0.0';

    /**
     * Get settings payload for the main posts index.
     *
     * @param array  $config           Plugin configuration.
     * @param string $base_index_name  Base index name (e.g. "posts" or "wp_posts").
     * @return array
     */
    public static function get_settings($config = array(), $base_index_name = 'posts')
    {
        // 1. Searchable Attributes ordered by weight (descending)
        $weights = isset($config['weights']) && is_array($config['weights']) ? $config['weights'] : array(
            'post_title'   => 10,
            'sku'          => 8,
            'category'     => 6,
            'tag'          => 5,
            'post_excerpt' => 3,
            'post_content' => 2,
        );

        arsort($weights);

        $searchable_attributes = array();
        foreach ($weights as $field => $weight) {
            if ($weight <= 0) {
                continue;
            }
            // Use unordered for heavy text fields to improve performance
            if ($field === 'post_content') {
                $searchable_attributes[] = 'unordered(post_content)';
            } else {
                $searchable_attributes[] = $field;
            }
        }

        // Add custom fields if not already present
        if (isset($config['custom_fields']) && is_array($config['custom_fields'])) {
            foreach ($config['custom_fields'] as $pt => $cf_list) {
                if (!is_array($cf_list)) continue;
                foreach ($cf_list as $cf) {
                    if (empty($cf['name'])) continue;
                    $field_name = $cf['name'];
                    if (!in_array($field_name, $searchable_attributes) && !in_array("unordered($field_name)", $searchable_attributes)) {
                        $searchable_attributes[] = $field_name;
                    }
                }
            }
        }

        if (empty($searchable_attributes)) {
            $searchable_attributes = array('post_title', 'unordered(post_content)', 'post_excerpt');
        }

        // 2. Attributes for Faceting
        $facets = array(
            'filterOnly(post_type)',
            'price',
            'category',
            'tag',
            'author_name',
        );

        if (isset($config['facets_config']) && is_array($config['facets_config'])) {
            foreach ($config['facets_config'] as $f) {
                if (empty($f['enabled'])) continue;

                $target = !empty($f['target']) ? $f['target'] : null;
                if (!$target) {
                    if (($f['type'] ?? '') === 'taxonomy') {
                        if ($f['source'] === 'category') $target = 'category';
                        elseif ($f['source'] === 'post_tag') $target = 'tag';
                        else $target = 'tax_' . $f['source'];
                    } elseif (($f['source'] ?? '') === '_sku') {
                        $target = 'sku';
                    } elseif (($f['source'] ?? '') === '_price') {
                        $target = 'price';
                    } elseif (($f['source'] ?? '') === '_stock_status') {
                        $target = 'in_stock';
                    } else {
                        $target = $f['source'] ?? '';
                    }
                }

                if (!empty($target) && !in_array($target, $facets) && !in_array("filterOnly($target)", $facets) && !in_array("searchable($target)", $facets)) {
                    // For taxonomies, enable searchable facets for large collections
                    if (($f['type'] ?? '') === 'taxonomy') {
                        $facets[] = 'searchable(' . $target . ')';
                    } else {
                        $facets[] = $target;
                    }
                }
            }
        }

        // 3. Dynamic Replicas for Sorting
        $sort_options = isset($config['sort_options']) && is_array($config['sort_options'])
            ? $config['sort_options']
            : array('price_asc', 'price_desc', 'date_desc');

        $replicas = array();
        foreach ($sort_options as $sort_key) {
            $replicas[] = self::get_replica_index_name($base_index_name, $sort_key);
        }

        // 4. Custom Ranking (Default is newest posts first)
        $custom_ranking = array('desc(published_at)');

        // 5. Build settings array
        $settings = array(
            'searchableAttributes'  => array_values($searchable_attributes),
            'attributesForFaceting' => array_values(array_unique($facets)),
            'customRanking'         => $custom_ranking,
            'attributesToRetrieve'  => array('*'),
            'attributesToHighlight' => array('post_title', 'post_content', 'post_excerpt'),
            'highlightPreTag'       => '<mark>',
            'highlightPostTag'      => '</mark>',
            'typoTolerance'         => $config['typo_tolerance'] ?? true,
        );

        if (!empty($replicas)) {
            $settings['replicas'] = array_values(array_unique($replicas));
        }

        return apply_filters('swift_search_algolia_settings', $settings, $config, $base_index_name);
    }

    /**
     * Get the replica index name for a given sort option.
     *
     * @param string $base_index_name
     * @param string $sort_key
     * @return string
     */
    public static function get_replica_index_name($base_index_name, $sort_key)
    {
        return $base_index_name . '_' . sanitize_key($sort_key);
    }

    /**
     * Get ranking rules and settings for a specific replica index.
     *
     * @param string $sort_key
     * @return array
     */
    public static function get_replica_settings($sort_key)
    {
        $custom_ranking_rule = '';

        switch ($sort_key) {
            case 'price_asc':
                $custom_ranking_rule = 'asc(price)';
                break;
            case 'price_desc':
                $custom_ranking_rule = 'desc(price)';
                break;
            case 'date_asc':
                $custom_ranking_rule = 'asc(published_at)';
                break;
            case 'date_desc':
            default:
                $custom_ranking_rule = 'desc(published_at)';
                break;
        }

        return array(
            'ranking' => array(
                $custom_ranking_rule,
                'typo',
                'geo',
                'words',
                'filters',
                'proximity',
                'attribute',
                'exact',
                'custom',
            ),
        );
    }

    /**
     * Get settings for the terms index.
     *
     * @param array $config
     * @return array
     */
    public static function get_terms_settings($config = array())
    {
        return array(
            'searchableAttributes'  => array('name', 'slug', 'taxonomy'),
            'attributesForFaceting' => array('filterOnly(taxonomy)'),
            'customRanking'         => array('desc(count)'),
            'highlightPreTag'       => '<mark>',
            'highlightPostTag'      => '</mark>',
        );
    }

    /**
     * Get settings for the users index.
     *
     * @param array $config
     * @return array
     */
    public static function get_users_settings($config = array())
    {
        return array(
            'searchableAttributes'  => array('display_name', 'user_login'),
            'highlightPreTag'       => '<mark>',
            'highlightPostTag'      => '</mark>',
        );
    }

    /**
     * Generate a deterministic hash of all settings to detect configuration changes.
     *
     * @param array  $config
     * @param string $base_index_name
     * @return string
     */
    public static function get_hash($config, $base_index_name = 'posts')
    {
        $composite = array(
            'posts' => self::get_settings($config, $base_index_name),
            'terms' => self::get_terms_settings($config),
            'users' => self::get_users_settings($config),
        );

        return md5(serialize($composite));
    }
}
