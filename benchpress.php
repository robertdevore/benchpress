<?php

 /**
  * The plugin bootstrap file
  *
  * @link              https://robertdevore.com
  * @since             1.0.0
  * @package           BenchPress
  *
  * @wordpress-plugin
  *
  * Plugin Name: BenchPress
  * Description: A tool for benchmarking PHP code snippets and WordPress® queries to help developers optimize performance.
  * Plugin URI:  https://github.com/robertdevore/benchpress/
  * Version:     1.1.0
  * Author:      Robert DeVore
  * Author URI:  https://robertdevore.com/
  * License:     GPL-2.0+
  * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
  * Text Domain: benchpress
  * Domain Path: /languages
  * Update URI:  https://github.com/robertdevore/benchpress/
  */
 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Track the starting time of the plugin.
$benchpress_start_time = microtime( true );

// Define constants.
define( 'BENCHPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BENCHPRESS_VERSION', '1.1.0' );

// Create variable for settings link filter.
$plugin_name = plugin_basename( __FILE__ );

/**
 * Add settings link on plugin page
 *
 * @param array $links an array of links related to the plugin.
 * 
 * @since  1.1.0
 * @return array updatead array of links related to the plugin.
 */
function benchpress_settings_link( $links ) {
    // Settings link.
    $settings_link = '<a href="admin.php?page=benchpress">' . esc_html__( 'Settings', 'benchpress' ) . '</a>';
    // Add the settings link to the $links array.
    array_unshift( $links, $settings_link );

    return $links;
}
add_filter( "plugin_action_links_$plugin_name", 'benchpress_settings_link' );

// Add the Plugin Update Checker.
require 'vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/robertdevore/benchpress/',
    __FILE__,
    'benchpress'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

// Check if Composer's autoloader is already registered globally.
if ( ! class_exists( 'RobertDevore\WPComCheck\WPComPluginHandler' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );

/**
 * Load plugin text domain for translations
 * 
 * @since  1.2.0
 * @return void
 */
function benchpress_load_textdomain() {
    load_plugin_textdomain( 
        'customer-loyalty-for-woocommerce',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'benchpress_load_textdomain' );

// Include necessary files.
require_once BENCHPRESS_PLUGIN_DIR . 'admin/db-table.php';
require_once BENCHPRESS_PLUGIN_DIR . 'classes/BenchPress_Table.php';
require_once BENCHPRESS_PLUGIN_DIR . 'classes/BenchPress_Snapshots_Table.php';
require_once BENCHPRESS_PLUGIN_DIR . 'includes/helper-functions.php';
require_once BENCHPRESS_PLUGIN_DIR . 'admin/enqueue.php';
require_once BENCHPRESS_PLUGIN_DIR . 'admin/ajax.php';
require_once BENCHPRESS_PLUGIN_DIR . 'admin/settings.php';

// Include PHP 8-specific functions if the server's PHP version is 8.0 or above.
if ( version_compare( PHP_VERSION, '8.0', '>=' ) ) {
    require_once BENCHPRESS_PLUGIN_DIR . 'includes/php-8.0-functions.php';
}
