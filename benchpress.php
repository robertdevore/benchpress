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

/**
 * Summary of benchpress_create_snapshots_table
 * 
 * @since  1.0.0
 * @return void
 */
function benchpress_create_snapshots_table() {
    global $wpdb;
    $table_name      = $wpdb->prefix . 'benchpress_snapshots';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        snapshot_data longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'benchpress_create_snapshots_table' );

// Include necessary files.
require_once BENCHPRESS_PLUGIN_DIR . 'classes/BenchPress_Table.php';
require_once BENCHPRESS_PLUGIN_DIR . 'classes/BenchPress_Snapshots_Table.php';
require_once BENCHPRESS_PLUGIN_DIR . 'includes/helper-functions.php';
require_once BENCHPRESS_PLUGIN_DIR . 'admin/settings.php';
// Include PHP 8-specific functions if the server's PHP version is 8.0 or above.
if ( version_compare( PHP_VERSION, '8.0', '>=' ) ) {
    require_once BENCHPRESS_PLUGIN_DIR . 'includes/php-8.0-functions.php';
}

/**
 * Enqueue BenchPress plugin assets.
 * 
 * @since  1.0.0
 * @return void
 */
function benchpress_enqueue_assets( $hook ) {
    // Bail early if we're not on BenchPress pages.
    if ( ! in_array( $hook, [
        'toplevel_page_benchpress',
        'benchpress_page_benchpress-snapshots',
        'benchpress_page_benchpress-settings'
    ], true ) ) {
        return;
    }

    wp_enqueue_style( 'benchpress-styles', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', [], BENCHPRESS_VERSION );
    wp_enqueue_style( 'select2-css', plugin_dir_url( __FILE__ ) . 'assets/css/select2.min.css', [], BENCHPRESS_VERSION );
    wp_enqueue_script( 'select2-js', plugin_dir_url( __FILE__ ) . 'assets/js/select2.min.js', [ 'jquery' ], BENCHPRESS_VERSION, true );
    wp_enqueue_script( 'benchpress-admin-js', plugin_dir_url( __FILE__ ) . 'assets/js/benchpress-admin.js', [ 'jquery', 'select2' ], BENCHPRESS_VERSION, true );
    wp_enqueue_script( 'benchpress-ajax', plugin_dir_url( __FILE__ ) . 'assets/js/benchpress-ajax.js', ['jquery'], BENCHPRESS_VERSION, true );

    wp_localize_script( 'benchpress-ajax', 'benchpress_ajax', [
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'benchpress_nonce' ),
        'site_name' => sanitize_title( get_bloginfo( 'name' ) ),
        'datetime'  => date( 'Y-m-d_H-i-s' ),
    ] );
}
add_action( 'admin_enqueue_scripts', 'benchpress_enqueue_assets' );

/**
 * Enqueue custom admin styles for BenchPress plugin.
 *
 * This function loads the custom CSS file for the BenchPress plugin's 
 * admin interface. It adds styling to the BenchPress menu icon in 
 * the WordPress® admin sidebar, including hover effects.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_enqueue_admin_styles() {
    wp_enqueue_style(
        'benchpress-admin-style',
        plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css',
        [],
        BENCHPRESS_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'benchpress_enqueue_admin_styles' );

/**
 * Handles AJAX request to refresh benchmark results.
 *
 * This function verifies the AJAX nonce, executes the benchmarks, 
 * and returns the results as an HTML table.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_ajax_refresh() {
    check_ajax_referer( 'benchpress_nonce' );

    // Run benchmarks and return HTML table.
    ob_start();
    $table = new BenchPress_Table();
    $table->prepare_items();
    $table->display();
    $output = ob_get_clean();

    wp_send_json_success( [ 'html' => $output ] );
}
add_action( 'wp_ajax_benchpress_refresh', 'benchpress_ajax_refresh' );

/**
 * Handles AJAX request to create a snapshot of benchmark results.
 *
 * This function verifies the AJAX nonce, runs benchmarks, saves the results 
 * to the database, and returns a success message upon completion.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_ajax_snapshot() {
    check_ajax_referer( 'benchpress_nonce' );

    // Run benchmarks and save to database.
    global $wpdb;
    $table_name = $wpdb->prefix . 'benchpress_snapshots';

    // Run the benchmarks
    $benchmarks    = benchpress_run_all_benchmarks();
    $snapshot_data = json_encode( $benchmarks );

    // Insert snapshot into database.
    $wpdb->insert(
        $table_name,
        [
            'snapshot_data' => $snapshot_data,
            'created_at'    => current_time( 'mysql' ),
        ]
    );

    wp_send_json_success( [ 'message' => esc_html__( 'Snapshot saved!', 'benchpress' ) ] );
}
add_action( 'wp_ajax_benchpress_snapshot', 'benchpress_ajax_snapshot' );

/**
 * Handles AJAX request to delete a specific snapshot.
 *
 * This function verifies the AJAX nonce, deletes the specified snapshot 
 * from the database, and returns a success or error message based on the result.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_delete_snapshot() {
    check_ajax_referer( 'benchpress_nonce' );

    if ( isset( $_POST['snapshot_id'] ) ) {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'benchpress_snapshots';
        $snapshot_id = intval( $_POST['snapshot_id'] );

        // Delete snapshot by ID.
        $deleted = $wpdb->delete( $table_name, [ 'id' => $snapshot_id ], [ '%d' ] );

        if ( $deleted ) {
            wp_send_json_success( [ 'message' => esc_html__( 'Snapshot deleted successfully.', 'benchpress' ) ] );
        } else {
            wp_send_json_error( [ 'message' => esc_html__( 'Failed to delete snapshot.', 'benchpress' ) ] );
        }
    } else {
        wp_send_json_error( [ 'message' => esc_html__( 'Invalid snapshot ID.', 'benchpress' ) ] );
    }
}
add_action( 'wp_ajax_benchpress_delete_snapshot', 'benchpress_delete_snapshot' );

/**
 * Handles AJAX request to clear all snapshots.
 *
 * This function verifies the AJAX nonce, removes all snapshots from 
 * the database, and returns a success or error message based on the result.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_clear_all_snapshots() {
    check_ajax_referer( 'benchpress_nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'benchpress_snapshots';

    // Delete all rows from the snapshots table.
    $deleted = $wpdb->query( "TRUNCATE TABLE $table_name" );

    if ( $deleted !== false ) {
        wp_send_json_success( [ 'message' => esc_html__( 'All snapshots deleted successfully.', 'benchpress' ) ] );
    } else {
        wp_send_json_error( [ 'message' => esc_html__( 'Failed to delete snapshots.', 'benchpress' ) ] );
    }
}
add_action( 'wp_ajax_benchpress_clear_all_snapshots', 'benchpress_clear_all_snapshots' );

/**
 * Handles AJAX request to download snapshots as a CSV file.
 *
 * This function verifies the AJAX nonce, retrieves all snapshots from 
 * the database, and outputs them as a CSV file for download.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_download_snapshots() {
    check_ajax_referer( 'benchpress_nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'benchpress_snapshots';

    // Fetch all snapshots.
    $snapshots = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A );

    if ( empty( $snapshots ) ) {
        wp_die( esc_html__( 'No snapshots available to download.', 'benchpress' ) );
    }

    // Set headers to initiate file download.
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=' . sanitize_title( get_bloginfo( 'name' ) ) . '-benchpress-' . date( 'Y-m-d_H-i-s' ) . '.csv' );

    // Open output stream for CSV.
    $output = fopen( 'php://output', 'w' );

    // Add CSV headers.
    fputcsv( $output, [ 'ID', 'Date', 'Benchmark Name', 'Execution Time', 'Description' ] );

    // Loop through each snapshot and format data.
    foreach ( $snapshots as $snapshot ) {
        $snapshot_data = json_decode( $snapshot['snapshot_data'], true );
        foreach ( $snapshot_data as $benchmark ) {
            fputcsv( $output, [
                $snapshot['id'],
                $snapshot['created_at'],
                $benchmark['name'],
                $benchmark['execution_time'],
                $benchmark['description'],
            ] );
        }
    }

    fclose( $output );
    exit;
}
add_action( 'wp_ajax_benchpress_download_snapshots', 'benchpress_download_snapshots' );
