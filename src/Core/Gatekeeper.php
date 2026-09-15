<?php

namespace SwiftSearchAlgolia\Core;

if (!defined('ABSPATH')) {
    exit;
}

use SwiftSearchAlgolia\Client\Client;

/**
 * Class Gatekeeper
 * 
 * Central Authority for Validation and Connection Security.
 */
class Gatekeeper
{
    /**
     * Check if Algolia is properly connected.
     * 
     * @param bool $ping_health If true, performs a live HTTP check.
     * @return bool
     */
    public static function is_connected($ping_health = false)
    {
        $settings = get_option('swift_search_algolia_settings', array());

        $app_id    = $settings['app_id'] ?? ($settings['application_id'] ?? '');
        $admin_key = $settings['admin_api_key'] ?? ($settings['api_key'] ?? '');

        // 1. Structural Check
        if (empty($app_id) || empty($admin_key)) {
            return false;
        }

        // 2. Live Health Check (Cached)
        if ($ping_health) {
            $transient = get_transient('swift_search_algolia_health_status');
            if ($transient !== false) {
                return $transient === 'ok';
            }

            $client = new Client($settings);
            if ($client->test_connection()) {
                set_transient('swift_search_algolia_health_status', 'ok', 5 * MINUTE_IN_SECONDS);
                return true;
            } else {
                set_transient('swift_search_algolia_health_status', 'fail', 1 * MINUTE_IN_SECONDS);
                return false;
            }
        }

        return true;
    }

    /**
     * Check if we are allowed to Index content.
     * 
     * @param string|null $post_type Optional post type.
     * @return bool
     */
    public static function can_index($post_type = null)
    {
        return self::is_connected();
    }

    /**
     * Check if features are available (All core features are 100% free).
     * 
     * @param string $feature e.g. 'analytics', 'synonyms', 'pinning'
     * @return bool
     */
    public static function can_use_features($feature = 'core')
    {
        return true;
    }

    /**
     * Check if User is Admin.
     * 
     * @return bool
     */
    public static function is_admin()
    {
        return current_user_can('manage_options');
    }
}
