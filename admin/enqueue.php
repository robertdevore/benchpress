<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
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

    wp_enqueue_style( 'benchpress-styles', plugin_dir_url( __FILE__ ) . '../assets/css/style.css', [], BENCHPRESS_VERSION );
    wp_enqueue_style( 'select2-css', plugin_dir_url( __FILE__ ) . '../assets/css/select2.min.css', [], BENCHPRESS_VERSION );
    wp_enqueue_script( 'select2-js', plugin_dir_url( __FILE__ ) . '../assets/js/select2.min.js', [ 'jquery' ], BENCHPRESS_VERSION, true );
    wp_enqueue_script( 'benchpress-admin-js', plugin_dir_url( __FILE__ ) . '../assets/js/benchpress-admin.js', [ 'jquery', 'select2' ], BENCHPRESS_VERSION, true );
    wp_enqueue_script( 'benchpress-ajax', plugin_dir_url( __FILE__ ) . '../assets/js/benchpress-ajax.js', ['jquery'], BENCHPRESS_VERSION, true );

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
        plugin_dir_url( __FILE__ ) . '../assets/css/admin-style.css',
        [],
        BENCHPRESS_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'benchpress_enqueue_admin_styles' );
