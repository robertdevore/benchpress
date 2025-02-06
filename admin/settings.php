<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

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
        plugin_dir_url( __FILE__ ) . '../assets/img/barbell-icon.svg',
        2
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

/**
 * Renders the BenchPress Snapshots page in the WordPress® admin area.
 *
 * This function outputs the HTML markup for the BenchPress Snapshots page, 
 * including buttons for clearing and downloading snapshots, a table displaying 
 * snapshots, and a modal for viewing detailed snapshot data. 
 * 
 * @since  1.0.0
 * @return void
 */
function benchpress_render_snapshots_page() {
    echo '<div class="wrap">';
    benchpress_admin_header( esc_html__( 'BenchPress Settings', 'benchpress' ) );

    $table = new BenchPress_Snapshots_Table();
    $table->prepare_items();
    $table->display();

    // Modal HTML for viewing the snapshot data.
    echo '
        <div id="snapshotModal" class="snapshot-modal" style="display:none;">
            <div class="snapshot-modal-content">
                <span class="snapshot-modal-close" style="cursor:pointer;">&times;</span>
                <h2>' . esc_html__( 'Snapshot Data', 'benchpress' ) . '</h2>
                <div id="snapshotModalData"></div>
            </div>
        </div>';
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

    global $benchpress_start_time;
    $benchpress_execution_time = microtime( true ) - $benchpress_start_time;
    $formatted_execution_time  = number_format( $benchpress_execution_time, 4 );

    echo '<div class="wrap">';
    benchpress_admin_header( esc_html__( 'BenchPress', 'benchpress' ) );

    echo '<div id="benchpress-results">';
    $table = new BenchPress_Table();
    $table->prepare_items();
    $table->display();
    echo '<p>' . sprintf( esc_html__( 'Total Execution Time: %s seconds', 'benchpress' ), $formatted_execution_time ) . '</p>';
    echo '</div></div>';
}

/**
 * Render the BenchPress settings page.
 *
 * @since  1.0.0
 * @return void
 */
function benchpress_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Save settings if the form is submitted and nonce is verified.
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
        update_option( 'benchpress_enable_transient_vs_query', isset( $_POST['benchpress_enable_transient_vs_query'] ) ? 1 : 0 );
        update_option( 'benchpress_enable_meta_query_test', isset( $_POST['benchpress_enable_meta_query_test'] ) ? 1 : 0 );
    }

    // Retrieve saved settings.
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

    // Get public post types and taxonomies for dropdown options.
    $post_types = get_post_types( [ 'public' => true ], 'objects' );
    $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

    echo '<div class="wrap">';
    benchpress_admin_header( esc_html__( 'BenchPress Settings', 'benchpress' ) );
    echo '<form method="post">';
    wp_nonce_field( 'benchpress_save_settings', 'benchpress_settings_nonce' );

    // Benchmark Options Section.
    echo '<h2>' . esc_html__( 'Benchmark Options', 'benchpress' ) . '</h2>';
    echo '<table class="form-table">';
    echo '<tr><th>' . esc_html__( 'Loop Count for Benchmarks', 'benchpress' ) . '</th>';
    echo '<td><input type="number" name="benchpress_loop_count" value="' . esc_attr( $loop_count ) . '" /></td></tr>';
    // Only add this setting when PHP 8.0+ is installed.
    if ( version_compare( PHP_VERSION, '8.0', '>=' ) ) {
        echo '<tr><th>' . esc_html__( 'Enable Switch vs Match Benchmark', 'benchpress' ) . '</th>';
        echo '<td><input type="checkbox" name="benchpress_enable_switch_vs_match" ' . checked( 1, $enable_switch_vs_match, false ) . ' /></td></tr>';
    }
    echo '<tr><th>' . esc_html__( 'Enable Transient vs Direct Query Benchmark', 'benchpress' ) . '</th>';
    echo '<td><input type="checkbox" name="benchpress_enable_transient_vs_query" ' . checked( 1, get_option( 'benchpress_enable_transient_vs_query', 1 ), false ) . ' /></td></tr>';
    echo '<tr><th>' . esc_html__( 'Enable Post Meta Access Benchmark', 'benchpress' ) . '</th>';
    echo '<td><input type="checkbox" name="benchpress_enable_meta_query_test" ' . checked( 1, get_option( 'benchpress_enable_meta_query_test', 1 ), false ) . ' /></td></tr>';

    echo '</table>';

    // WP_Query Customization Section.
    echo '<h2>' . esc_html__( 'WP_Query Settings', 'benchpress' ) . '</h2>';
    echo '<table class="form-table">';

    // Query Type.
    echo '<tr><th>' . esc_html__( 'Query Type', 'benchpress' ) . '</th>';
    echo '<td>
            <select name="benchpress_query_type">
                <option value="single" ' . selected( $query_type, 'single', false ) . '>' . esc_html__( 'Single Post', 'benchpress' ) . '</option>
                <option value="multiple" ' . selected( $query_type, 'multiple', false ) . '>' . esc_html__( 'Multiple Posts', 'benchpress' ) . '</option>
            </select>
          </td></tr>';
    echo '</table>';

    // Single Post ID selection (displayed only when "Single Post" is selected).
    echo '<div id="single-post-fields" style="display:none;">';
    echo '<h3>' . esc_html__( 'Single Post Query', 'benchpress' ) . '</h3>';
    echo '<table class="form-table">';
    echo '<tr><th>' . esc_html__( 'Post ID', 'benchpress' ) . '</th>';
    echo '<td><input type="number" name="benchpress_post_id[]" value="' . esc_attr( is_array( $post_id ) ? reset( $post_id ) : '' ) . '" /></td></tr>';
    echo '</table>';
    echo '</div>';

    // Multiple Post Query settings (displayed only when "Multiple Posts" is selected).
    echo '<div id="multiple-post-fields" style="display:none;">';
    echo '<h3>' . esc_html__( 'Multiple Post Query', 'benchpress' ) . '</h3>';
    echo '<table class="form-table">';

    // Post Type.
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

    // Post Count.
    echo '<tr><th>' . esc_html__( 'Number of Posts', 'benchpress' ) . '</th>';
    echo '<td><input type="number" name="benchpress_post_count" value="' . esc_attr( $post_count ) . '" /></td></tr>';

    // Taxonomy and Terms.
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

    // Terms (comma-separated).
    echo '<tr><th>' . esc_html__( 'Terms (comma-separated)', 'benchpress' ) . '</th>';
    echo '<td><input type="text" name="benchpress_tax_terms" value="' . esc_attr( $tax_terms ) . '" /></td></tr>';

    // Order By and Order.
    echo '<tr><th>' . esc_html__( 'Order By', 'benchpress' ) . '</th>';
    echo '<td><select name="benchpress_orderby">';
    $orderby_options = [
        'date'     => esc_html__( 'Date', 'benchpress' ),
        'title'    => esc_html__( 'Title', 'benchpress' ),
        'ID'       => esc_html__( 'ID', 'benchpress' ),
        'name'     => esc_html__( 'Name', 'benchpress' ),
        'author'   => esc_html__( 'Author', 'benchpress' ),
        'modified' => esc_html__( 'Modified', 'benchpress' ),
        'rand'     => esc_html__( 'Random', 'benchpress' ),
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

    submit_button( esc_html__( 'Save Settings', 'benchpress' ) );
    echo '</form></div>';
}
