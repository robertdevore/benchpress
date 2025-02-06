<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

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
