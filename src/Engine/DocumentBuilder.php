<?php

namespace SwiftSearchAlgolia\Engine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DocumentBuilder
 *
 * Transforms a WP_Post into an Algolia document object.
 */
class DocumentBuilder
{
    /**
     * Build document from post ID or WP_Post.
     *
     * @param int|\WP_Post $post_id Post ID or object.
     * @return array|false Document array or false if ignored.
     */
    public function build($post_id)
    {
        $post = is_object($post_id) ? $post_id : get_post($post_id);

        if (!$post || 'publish' !== $post->post_status) {
            return false;
        }

        $id = $post->ID;

        // Allow third-party exclusion of post
        if (!apply_filters('swift_search_algolia_should_index_post', true, $id, $post)) {
            return false;
        }

        // 1. Strict Whitelist Check
        $settings      = get_option('swift_search_algolia_settings', array());
        $allowed_types = isset($settings['indexed_post_types']) ? (array) $settings['indexed_post_types'] : ConfigLoader::get_default_post_types();

        if (!in_array($post->post_type, $allowed_types)) {
            return false;
        }

        // 2. Safe Content Extraction
        $clean_content = wp_strip_all_tags(strip_shortcodes($post->post_content));

        $excerpt = $post->post_excerpt
            ? wp_strip_all_tags($post->post_excerpt)
            : wp_html_excerpt($clean_content, 250);

        // Prepare document with Algolia's required 'objectID'
        $document = array(
            'objectID'     => (string) $id,
            'id'           => (string) $id,
            'post_id'      => (int) $id,
            'post_title'   => $post->post_title,
            'post_content' => $clean_content,
            'post_excerpt' => $excerpt,
            'post_type'    => $post->post_type,
            'permalink'    => get_permalink($post),
            'published_at' => get_post_timestamp($post),
            'author_name'  => get_the_author_meta('display_name', $post->post_author),
        );

        // Thumbnail
        if (has_post_thumbnail($post)) {
            $document['thumbnail_url'] = get_the_post_thumbnail_url($post, 'medium');
        }

        // WooCommerce Product Data
        if (function_exists('wc_get_product') && 'product' === $post->post_type) {
            $product = wc_get_product($post);
            if ($product) {
                $price = $product->get_price();
                if (is_numeric($price)) {
                    $document['price'] = (float) $price;
                }
                $document['sku']      = (string) $product->get_sku();
                $document['in_stock'] = (bool) $product->is_in_stock();
            }
        }

        // 3. Taxonomies & Facets
        if (isset($settings['facets_config']) && is_array($settings['facets_config'])) {
            foreach ($settings['facets_config'] as $f) {
                if (empty($f['enabled'])) continue;

                $source = !empty($f['source']) ? $f['source'] : '';
                $type   = !empty($f['type']) ? $f['type'] : 'taxonomy';

                // Target field resolution
                $target = !empty($f['target']) ? $f['target'] : null;
                if (!$target) {
                    if ($type === 'taxonomy') {
                        if ($source === 'category') $target = 'category';
                        elseif ($source === 'post_tag') $target = 'tag';
                        else $target = 'tax_' . $source;
                    } elseif ($source === '_sku') {
                        $target = 'sku';
                    } elseif ($source === '_price') {
                        $target = 'price';
                    } elseif ($source === '_stock_status') {
                        $target = 'in_stock';
                    } else {
                        $target = $source;
                    }
                }

                if (isset($document[$target])) continue;

                if ($type === 'taxonomy') {
                    $terms = get_the_terms($id, $source);
                    if (!empty($terms) && !is_wp_error($terms)) {
                        $term_names = wp_list_pluck($terms, 'name');
                        $document[$target] = array_values(array_unique($term_names));
                    }
                } else {
                    $meta_val = get_post_meta($id, $source, true);

                    // Handle WooCommerce getters
                    if (function_exists('wc_get_product') && ('_sku' === $source || '_price' === $source || '_stock_status' === $source)) {
                        $product = wc_get_product($id);
                        if ($product) {
                            if ($source === '_sku') $meta_val = $product->get_sku();
                            elseif ($source === '_price') $meta_val = $product->get_price();
                            elseif ($source === '_stock_status') $meta_val = $product->is_in_stock();
                        }
                    }

                    $data_type = !empty($f['data_type']) ? $f['data_type'] : 'string';
                    if ($data_type === 'int32' || $data_type === 'int64') {
                        $document[$target] = (int) $meta_val;
                    } elseif ($data_type === 'float') {
                        $document[$target] = (float) $meta_val;
                    } elseif ($data_type === 'bool') {
                        $document[$target] = filter_var($meta_val, FILTER_VALIDATE_BOOLEAN);
                    } elseif ($data_type === 'string[]') {
                        if (is_array($meta_val)) {
                            $document[$target] = array_values($meta_val);
                        } else {
                            $document[$target] = array_map('trim', explode(',', (string) $meta_val));
                        }
                    } else {
                        $document[$target] = (string) $meta_val;
                    }
                }
            }
        }

        // Standard taxonomies fallback if not configured in facets_config
        if (!isset($document['category'])) {
            $categories = get_the_category($id);
            if (!empty($categories) && !is_wp_error($categories)) {
                $document['category'] = array_values(wp_list_pluck($categories, 'name'));
            }
        }
        if (!isset($document['tag'])) {
            $tags = get_the_tags($id);
            if (!empty($tags) && !is_wp_error($tags)) {
                $document['tag'] = array_values(wp_list_pluck($tags, 'name'));
            }
        }

        // 4. Custom Meta Fields
        if (isset($settings['custom_fields']) && is_array($settings['custom_fields'])) {
            $pt = $post->post_type;
            if (isset($settings['custom_fields'][$pt])) {
                foreach ($settings['custom_fields'][$pt] as $field) {
                    if (empty($field['key']) || empty($field['name'])) continue;
                    if (isset($document[$field['name']])) continue;

                    $meta_val = get_post_meta($id, $field['key'], true);

                    if ($field['type'] === 'int32' || $field['type'] === 'int64') {
                        $document[$field['name']] = (int) $meta_val;
                    } elseif ($field['type'] === 'float') {
                        $document[$field['name']] = (float) $meta_val;
                    } elseif ($field['type'] === 'bool') {
                        $document[$field['name']] = (bool) $meta_val;
                    } elseif ($field['type'] === 'string[]') {
                        if (is_array($meta_val)) {
                            $document[$field['name']] = array_values($meta_val);
                        } else {
                            $document[$field['name']] = array_map('trim', explode(',', (string) $meta_val));
                        }
                    } else {
                        $document[$field['name']] = (string) $meta_val;
                    }
                }
            }
        }

        return apply_filters('swift_search_algolia_post_document', $document, $id, $post);
    }
}
