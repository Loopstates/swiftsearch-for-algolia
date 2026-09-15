<?php
/**
 * Plugin Name: SwiftSearch for Algolia
 * Plugin URI:  https://docs.loopstates.com/swiftsearch-for-algolia/
 * Description: Lightning-fast, intelligent search for WordPress & WooCommerce powered by Algolia. Features instant modal search, visual facets builder, field weighting, synonyms, visual merchandising & pinning, and local search analytics.
 * Version:           1.0.0
 * Tested up to:      6.7
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:      Loopstates
 * Author URI:  https://loopstates.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: swiftsearch-for-algolia
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

// Define Constants
define('SWIFT_SEARCH_ALGOLIA_VERSION', '1.0.0');
define('SWIFT_SEARCH_ALGOLIA_DB_VERSION', '1.0.0');
define('SWIFT_SEARCH_ALGOLIA_FILE', __FILE__);
define('SWIFT_SEARCH_ALGOLIA_PATH', plugin_dir_path(__FILE__));
define('SWIFT_SEARCH_ALGOLIA_URL', plugin_dir_url(__FILE__));

// Require Autoloader
require_once untrailingslashit(SWIFT_SEARCH_ALGOLIA_PATH) . '/src/Core/Autoloader.php';

// Initialize Autoloader
\SwiftSearchAlgolia\Core\Autoloader::register();

// Require Vendor Autoloader (if exists)
if (file_exists(untrailingslashit(SWIFT_SEARCH_ALGOLIA_PATH) . '/vendor/autoload.php')) {
	require_once untrailingslashit(SWIFT_SEARCH_ALGOLIA_PATH) . '/vendor/autoload.php';
}

// Activation Hook
register_activation_hook(__FILE__, array(\SwiftSearchAlgolia\Core\DB::class, 'install'));

// Init Plugin
function swift_search_algolia_init()
{
	// Load the main plugin instance
	\SwiftSearchAlgolia\Core\Plugin::instance();
}
add_action('plugins_loaded', 'swift_search_algolia_init');
