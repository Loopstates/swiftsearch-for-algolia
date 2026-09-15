<?php

namespace SwiftSearchAlgolia\Core;

use SwiftSearchAlgolia\Admin\AdminController;
use SwiftSearchAlgolia\Core\SettingsIntegrity;
use SwiftSearchAlgolia\Frontend\SearchController;
use SwiftSearchAlgolia\Engine\EngineController;
use SwiftSearchAlgolia\Integrations\IntegrationManager;
use SwiftSearchAlgolia\Core\Cron;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Plugin
 *
 * Main plugin container.
 */
class Plugin
{

    /**
     * Instance of this class.
     *
     * @var Plugin
     */
    protected static $instance = null;

    /**
     * Return an instance of this class.
     *
     * @return Plugin
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Plugin constructor.
     */
    private function __construct()
    {
        $this->define_hooks();
        $this->load_modules();
    }

    /**
     * Define basic hooks.
     */
    private function define_hooks()
    {
        // Localization
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Security & Integrity
        add_action('updated_option', array('\SwiftSearchAlgolia\Core\SettingsIntegrity', 'sign_option'), 10, 3);
        add_action('added_option', array('\SwiftSearchAlgolia\Core\SettingsIntegrity', 'sign_option'), 10, 2);
        add_action('admin_init', array($this, 'verify_integrity'));
    }

    /**
     * Load plugin modules.
     */
    private function load_modules()
    {
        // Admin Area
        if (is_admin()) {
            new AdminController();
        }

        // API
        new \SwiftSearchAlgolia\Api\RestController();

        // Core Engine (Indexing, Sync, etc.)
        new EngineController();

        // Frontend Search
        if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            new SearchController();
        }

        // Integrations
        new IntegrationManager();

        // Background Processor (Async Chain)
        new \SwiftSearchAlgolia\Engine\BackgroundProcess();

        // Scheduled Tasks
        (new Cron())->init();

        // WP-CLI Registration
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('swift-search-algolia', '\SwiftSearchAlgolia\CLI\IndexCommand');
        }
    }

    /**
     * Load text domain.
     */
    public function load_textdomain()
    {
        // Note: load_plugin_textdomain is removed for WP.org compliance as translations are managed centrally.
    }

    /**
     * Verify settings integrity.
     */
    public function verify_integrity()
    {
        if (!SettingsIntegrity::verify_option('swift_search_algolia_settings')) {
            // Tampering Detected!
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>' . esc_html__('Security Warning:', 'swiftsearch-for-algolia') . '</strong> ' . esc_html__('SwiftSearch settings appear to have been modified externally. Please verify your Algolia credentials.', 'swiftsearch-for-algolia') . '</p></div>';
            });
        }
    }
}
