<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

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
