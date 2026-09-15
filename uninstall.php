<?php
/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// 1. Delete Tables
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}swift_search_algolia_logs");
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}swift_search_algolia_batch_logs");

// 2. Delete Options
delete_option('swift_search_algolia_settings');
delete_option('swift_search_algolia_index_status');
delete_option('swift_search_algolia_sync_errors');
delete_option('swift_search_algolia_db_version');
delete_option('swift_search_algolia_debug_injected_v2');
delete_option('swift_search_algolia_pinned_items');
delete_option('swift_search_algolia_override_default');
delete_option('swift_search_algolia_schema_hash');
delete_option('swift_search_algolia_schema_mismatch');
delete_option('swift_search_algolia_debug_loopback');
delete_option('swift_search_algolia_bg_secret');

// 3. Clear Async Actions
if (class_exists('ActionScheduler_QueueRunner')) {
    \ActionScheduler_QueueRunner::instance()->unhook_dispatch_async_request();
}
