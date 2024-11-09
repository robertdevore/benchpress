<?php
/**
 * Plugin Name: BenchPress
 * Description: A tool for benchmarking PHP code snippets and WordPress queries to help developers optimize performance.
 * Version: 1.1
 * Author: Your Name
 * Text Domain: benchpress
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants.
define( 'BENCHPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BENCHPRESS_VERSION', '1.0.0' );

register_activation_hook( __FILE__, 'benchpress_create_snapshots_table' );

/**
 * Summary of benchpress_create_snapshots_table
 * 
 * @since  1.0.0
 * @return void
 */
function benchpress_create_snapshots_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'benchpress_snapshots';
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

// Include necessary files.
require_once BENCHPRESS_PLUGIN_DIR . 'classes/BenchPress_Table.php';
require_once BENCHPRESS_PLUGIN_DIR . 'classes/BenchPress_Snapshots_Table.php';
require_once BENCHPRESS_PLUGIN_DIR . 'includes/helper-functions.php';

/**
 * Register BenchPress admin menu.
 * 
 * @since  1.0.0
 * @return void
 */
function benchpress_admin_menu() {
    add_menu_page(
        esc_html__( 'BenchPress', 'benchpress' ),
        esc_html__( 'BenchPress', 'benchpress' ),
        'manage_options',
        'benchpress',
        'benchpress_render_page',
        'dashicons-performance',
        25
    );

    add_submenu_page(
        'benchpress',
        esc_html__( 'Snapshots', 'benchpress' ),
        esc_html__( 'Snapshots', 'benchpress' ),
        'manage_options',
        'benchpress-snapshots',
        'benchpress_render_snapshots_page'
    );

    add_submenu_page(
        'benchpress',
        esc_html__( 'Settings', 'benchpress' ),
        esc_html__( 'Settings', 'benchpress' ),
        'manage_options',
        'benchpress-settings',
        'benchpress_render_settings_page'
    );
}
add_action( 'admin_menu', 'benchpress_admin_menu' );

function benchpress_render_snapshots_page() {
    echo '<div class="wrap"><h1>' . esc_html__( 'BenchPress Snapshots', 'benchpress' );
    
    // Clear Snapshots and Download Snapshots buttons
    echo ' <button id="benchpress-clear-snapshots-btn" class="button" style="margin-left: 10px;"><span class="dashicons dashicons-trash"></span> ' . esc_html__( 'Clear Snapshots', 'benchpress' ) . '</button>';
    echo ' <button id="benchpress-download-snapshots-btn" class="button" style="margin-left: 10px;"><span class="dashicons dashicons-download"></span> ' . esc_html__( 'Download Snapshots', 'benchpress' ) . '</button>';
    
    echo '</h1><hr />';

    echo '<p><a href="https://robertdevore.com/benchpress-documentation/" target="_blank">' . esc_html__( 'Documentation', 'acme' ) . '</a> &middot; <a href="https://robertdevore.com/contact/" target="_blank">' . esc_html__( 'Support', 'acme' ) . '</a> &middot; <a href="https://robertdevore.com/wordpress-and-woocommerce-plugins/" target="_blank">' . esc_html__( 'More Plugins', 'acme' ) . '</a></p>';

    $table = new BenchPress_Snapshots_Table();
    $table->prepare_items();
    $table->display();

    // Modal HTML for viewing snapshot data
    echo '
        <div id="snapshotModal" class="snapshot-modal" style="display:none;">
            <div class="snapshot-modal-content">
                <span class="snapshot-modal-close" style="cursor:pointer;">&times;</span>
                <h2>' . esc_html__( 'Snapshot Data', 'benchpress' ) . '</h2>
                <div id="snapshotModalData"></div>
            </div>
        </div>';

    // CSS for modal styling
    echo '
        <style>
            .snapshot-modal { display: none; position: fixed; z-index: 1000; padding-top: 60px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
            .snapshot-modal-content { background-color: #fff; margin: auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; }
            .snapshot-modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
            .snapshot-modal-close:hover, .snapshot-modal-close:focus { color: #000; text-decoration: none; cursor: pointer; }
        </style>';
}

/**
 * Render the BenchPress plugin's main page with a refresh button.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_render_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html__( 'BenchPress', 'benchpress' );

    // Add Snapshot Button.
    echo ' <button id="benchpress-snapshot-btn" class="button"><span class="dashicons dashicons-camera"></span> ' . esc_html__( 'Save Snapshot', 'benchpress' ) . '</button>';
    // Add Refresh Button.
    echo ' <button id="benchpress-refresh-btn" class="button"><span class="dashicons dashicons-update"></span> ' . esc_html__( 'Refresh Tests', 'benchpress' ) . '</button>';

    echo '</h1><hr />';

    echo '<p><a href="https://robertdevore.com/benchpress-documentation/" target="_blank">' . esc_html__( 'Documentation', 'acme' ) . '</a> &middot; <a href="https://robertdevore.com/contact/" target="_blank">' . esc_html__( 'Support', 'acme' ) . '</a> &middot; <a href="https://robertdevore.com/wordpress-and-woocommerce-plugins/" target="_blank">' . esc_html__( 'More Plugins', 'acme' ) . '</a></p>';

    echo '<div id="benchpress-results">';
    // Initial display of benchmark results
    $table = new BenchPress_Table();
    $table->prepare_items();
    $table->display();
    echo '</div></div>';
}

/**
 * Render the BenchPress settings page.
 *
 * @since 1.0.0
 * @return void
 */
function benchpress_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Save settings if the form is submitted and nonce is verified
    if ( isset( $_POST['benchpress_settings_nonce'] ) && wp_verify_nonce( $_POST['benchpress_settings_nonce'], 'benchpress_save_settings' ) ) {
        update_option( 'benchpress_loop_count', absint( $_POST['benchpress_loop_count'] ) );
        update_option( 'benchpress_enable_switch_vs_match', isset( $_POST['benchpress_enable_switch_vs_match'] ) ? 1 : 0 );
        update_option( 'benchpress_query_type', sanitize_text_field( $_POST['benchpress_query_type'] ) );
        update_option( 'benchpress_post_id', array_map( 'absint', $_POST['benchpress_post_id'] ?? [] ) );
        update_option( 'benchpress_post_type', sanitize_text_field( $_POST['benchpress_post_type'] ) );
        update_option( 'benchpress_post_count', absint( $_POST['benchpress_post_count'] ) );
        update_option( 'benchpress_taxonomy', sanitize_text_field( $_POST['benchpress_taxonomy'] ) );
        update_option( 'benchpress_tax_terms', sanitize_text_field( $_POST['benchpress_tax_terms'] ) );
        update_option( 'benchpress_orderby', sanitize_text_field( $_POST['benchpress_orderby'] ) );
        update_option( 'benchpress_order', in_array( $_POST['benchpress_order'], ['ASC', 'DESC'] ) ? $_POST['benchpress_order'] : 'ASC' );
    }

    // Retrieve saved settings
    $loop_count             = get_option( 'benchpress_loop_count', 1000000 );
    $enable_switch_vs_match = get_option( 'benchpress_enable_switch_vs_match', 1 );
    $query_type             = get_option( 'benchpress_query_type', 'single' );
    $post_id                = get_option( 'benchpress_post_id', [] );
    $post_type              = get_option( 'benchpress_post_type', 'post' );
    $post_count             = get_option( 'benchpress_post_count', 5 );
    $taxonomy               = get_option( 'benchpress_taxonomy', '' );
    $tax_terms              = get_option( 'benchpress_tax_terms', '' );
    $orderby                = get_option( 'benchpress_orderby', 'date' );
    $order                  = get_option( 'benchpress_order', 'ASC' );

    // Get public post types and taxonomies for dropdown options
    $post_types = get_post_types( [ 'public' => true ], 'objects' );
    $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

    echo '<div class="wrap"><h1>' . esc_html__( 'BenchPress Settings', 'benchpress' ) . '</h1><hr />';
    echo '<form method="post">';
    wp_nonce_field( 'benchpress_save_settings', 'benchpress_settings_nonce' );

    // Benchmark Options Section
    echo '<h2>' . esc_html__( 'Benchmark Options', 'benchpress' ) . '</h2>';
    echo '<table class="form-table">';
    echo '<tr><th>' . esc_html__( 'Loop Count for Benchmarks', 'benchpress' ) . '</th>';
    echo '<td><input type="number" name="benchpress_loop_count" value="' . esc_attr( $loop_count ) . '" /></td></tr>';
    echo '<tr><th>' . esc_html__( 'Enable Switch vs Match Benchmark', 'benchpress' ) . '</th>';
    echo '<td><input type="checkbox" name="benchpress_enable_switch_vs_match" ' . checked( 1, $enable_switch_vs_match, false ) . ' /></td></tr>';
    echo '</table>';

    // WP_Query Customization Section
    echo '<h2>' . esc_html__( 'WP_Query Settings', 'benchpress' ) . '</h2>';
    echo '<table class="form-table">';

    // Query Type
    echo '<tr><th>' . esc_html__( 'Query Type', 'benchpress' ) . '</th>';
    echo '<td>
            <select name="benchpress_query_type">
                <option value="single" ' . selected( $query_type, 'single', false ) . '>' . esc_html__( 'Single Post', 'benchpress' ) . '</option>
                <option value="multiple" ' . selected( $query_type, 'multiple', false ) . '>' . esc_html__( 'Multiple Posts', 'benchpress' ) . '</option>
            </select>
          </td></tr>';
    echo '</table>';

    // Single Post ID selection (displayed only when "Single Post" is selected)
    echo '<div id="single-post-fields" style="display:none;">';
    echo '<h3>' . esc_html__( 'Single Post Query', 'benchpress' ) . '</h3>';
    echo '<table class="form-table">';
    echo '<tr><th>' . esc_html__( 'Post ID', 'benchpress' ) . '</th>';
    echo '<td><input type="number" name="benchpress_post_id[]" value="' . esc_attr( is_array( $post_id ) ? reset( $post_id ) : '' ) . '" /></td></tr>';
    echo '</table>';
    echo '</div>';

    // Multiple Post Query settings (displayed only when "Multiple Posts" is selected)
    echo '<div id="multiple-post-fields" style="display:none;">';
    echo '<h3>' . esc_html__( 'Multiple Post Query', 'benchpress' ) . '</h3>';
    echo '<table class="form-table">';

    // Post Type
    echo '<tr><th>' . esc_html__( 'Post Type', 'benchpress' ) . '</th>';
    echo '<td><select name="benchpress_post_type">';
    foreach ( $post_types as $type => $obj ) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $type ),
            selected( $post_type, $type, false ),
            esc_html( $obj->label )
        );
    }
    echo '</select></td></tr>';

    // Post Count
    echo '<tr><th>' . esc_html__( 'Number of Posts', 'benchpress' ) . '</th>';
    echo '<td><input type="number" name="benchpress_post_count" value="' . esc_attr( $post_count ) . '" /></td></tr>';

    // Taxonomy and Terms
    echo '<tr><th>' . esc_html__( 'Taxonomy', 'benchpress' ) . '</th>';
    echo '<td><select name="benchpress_taxonomy">';
    echo '<option value="">' . esc_html__( 'Select Taxonomy', 'benchpress' ) . '</option>';
    foreach ( $taxonomies as $tax => $obj ) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $tax ),
            selected( $taxonomy, $tax, false ),
            esc_html( $obj->label )
        );
    }
    echo '</select></td></tr>';

    // Terms (comma-separated)
    echo '<tr><th>' . esc_html__( 'Terms (comma-separated)', 'benchpress' ) . '</th>';
    echo '<td><input type="text" name="benchpress_tax_terms" value="' . esc_attr( $tax_terms ) . '" /></td></tr>';

    // Order By and Order
    echo '<tr><th>' . esc_html__( 'Order By', 'benchpress' ) . '</th>';
    echo '<td><select name="benchpress_orderby">';
    $orderby_options = [
        'date'       => esc_html__( 'Date', 'benchpress' ),
        'title'      => esc_html__( 'Title', 'benchpress' ),
        'ID'         => esc_html__( 'ID', 'benchpress' ),
        'name'       => esc_html__( 'Name', 'benchpress' ),
        'author'     => esc_html__( 'Author', 'benchpress' ),
        'modified'   => esc_html__( 'Modified', 'benchpress' ),
        'rand'       => esc_html__( 'Random', 'benchpress' ),
    ];
    foreach ( $orderby_options as $value => $label ) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $value ),
            selected( $orderby, $value, false ),
            esc_html( $label )
        );
    }
    echo '</select></td></tr>';

    echo '<tr><th>' . esc_html__( 'Order', 'benchpress' ) . '</th>';
    echo '<td>
            <select name="benchpress_order">
                <option value="ASC" ' . selected( $order, 'ASC', false ) . '>' . esc_html__( 'Ascending', 'benchpress' ) . '</option>
                <option value="DESC" ' . selected( $order, 'DESC', false ) . '>' . esc_html__( 'Descending', 'benchpress' ) . '</option>
            </select>
          </td></tr>';
    echo '</table>';
    echo '</div>';

    submit_button( __( 'Save Settings', 'benchpress' ) );
    echo '</form></div>';
}

/**
 * Enqueue BenchPress plugin assets.
 * 
 * @since  1.0.0
 * @return void
 */
function benchpress_enqueue_assets() {
    wp_enqueue_style( 'benchpress-styles', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', [], BENCHPRESS_VERSION );
    wp_enqueue_script( 'benchpress-admin-js', plugin_dir_url( __FILE__ ) . 'assets/js/benchpress-admin.js', [ 'jquery', 'select2' ], BENCHPRESS_VERSION, true );
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', [ 'jquery' ] );
}
add_action( 'admin_enqueue_scripts', 'benchpress_enqueue_assets' );

// Enqueue JavaScript for AJAX handling
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'benchpress-ajax', plugin_dir_url( __FILE__ ) . 'assets/js/benchpress-ajax.js', ['jquery'], BENCHPRESS_VERSION, true );

    wp_localize_script( 'benchpress-ajax', 'benchpress_ajax', [
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'benchpress_nonce' ),
        'site_name' => sanitize_title( get_bloginfo( 'name' ) ),
        'datetime'  => date( 'Y-m-d_H-i-s' ),
    ] );
} );

// AJAX: Refresh the benchmark results
add_action( 'wp_ajax_benchpress_refresh', 'benchpress_ajax_refresh' );
function benchpress_ajax_refresh() {
    check_ajax_referer( 'benchpress_nonce' );

    // Run benchmarks and return HTML table
    ob_start();
    $table = new BenchPress_Table();
    $table->prepare_items();
    $table->display();
    $output = ob_get_clean();

    wp_send_json_success( [ 'html' => $output ] );
}

// AJAX: Snapshot benchmark results
function benchpress_ajax_snapshot() {
    check_ajax_referer( 'benchpress_nonce' );

    // Run benchmarks and save to database
    global $wpdb;
    $table_name = $wpdb->prefix . 'benchpress_snapshots';

    // Run the benchmarks
    $benchmarks = benchpress_run_all_benchmarks();
    $snapshot_data = json_encode( $benchmarks );

    // Insert snapshot into database
    $wpdb->insert(
        $table_name,
        [
            'snapshot_data' => $snapshot_data,
            'created_at'    => current_time( 'mysql' ),
        ]
    );

    wp_send_json_success( [ 'message' => __( 'Snapshot saved!', 'benchpress' ) ] );
}
add_action( 'wp_ajax_benchpress_snapshot', 'benchpress_ajax_snapshot' );

// AJAX: Delete snapshot
function benchpress_delete_snapshot() {
    check_ajax_referer( 'benchpress_nonce' );

    if ( isset( $_POST['snapshot_id'] ) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'benchpress_snapshots';
        $snapshot_id = intval( $_POST['snapshot_id'] );

        // Delete snapshot by ID
        $deleted = $wpdb->delete( $table_name, [ 'id' => $snapshot_id ], [ '%d' ] );

        if ( $deleted ) {
            wp_send_json_success( [ 'message' => __( 'Snapshot deleted successfully.', 'benchpress' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Failed to delete snapshot.', 'benchpress' ) ] );
        }
    } else {
        wp_send_json_error( [ 'message' => __( 'Invalid snapshot ID.', 'benchpress' ) ] );
    }
}
add_action( 'wp_ajax_benchpress_delete_snapshot', 'benchpress_delete_snapshot' );

// AJAX: Clear all snapshots
function benchpress_clear_all_snapshots() {
    check_ajax_referer( 'benchpress_nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'benchpress_snapshots';

    // Delete all rows from the snapshots table
    $deleted = $wpdb->query( "TRUNCATE TABLE $table_name" );

    if ( $deleted !== false ) {
        wp_send_json_success( [ 'message' => __( 'All snapshots deleted successfully.', 'benchpress' ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Failed to delete snapshots.', 'benchpress' ) ] );
    }
}
add_action( 'wp_ajax_benchpress_clear_all_snapshots', 'benchpress_clear_all_snapshots' );

// AJAX: Download snapshots as CSV
function benchpress_download_snapshots() {
    check_ajax_referer( 'benchpress_nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'benchpress_snapshots';

    // Fetch all snapshots
    $snapshots = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A );

    if ( empty( $snapshots ) ) {
        wp_die( __( 'No snapshots available to download.', 'benchpress' ) );
    }

    // Set headers to initiate a file download
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=snapshots.csv' );

    // Open output stream for writing CSV data
    $output = fopen( 'php://output', 'w' );

    // Add CSV headers
    fputcsv( $output, [ 'ID', 'Date', 'Benchmark Name', 'Execution Time', 'Description' ] );

    // Populate CSV with snapshot data
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
