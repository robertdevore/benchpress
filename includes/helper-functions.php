<?php
/**
 * Benchmark functions for BenchPress plugin.
 *
 * @package BenchPress
 */

/**
 * Benchmark WP_Query by ID with customizable options.
 *
 * @since  1.0.0
 * @return array Benchmark data for WP_Query by ID.
 */
function benchpress_benchmark_wp_query_by_id() {
    // Retrieve settings
    $query_type   = get_option( 'benchpress_query_type', 'single' );
    $post_id      = get_option( 'benchpress_post_id', [] );
    $post_type    = get_option( 'benchpress_post_type', 'post' );
    $post_count   = get_option( 'benchpress_post_count', 5 );
    $taxonomy     = get_option( 'benchpress_taxonomy', '' );
    $tax_terms    = get_option( 'benchpress_tax_terms', '' );
    $orderby      = get_option( 'benchpress_orderby', 'date' );
    $order        = get_option( 'benchpress_order', 'ASC' );
    $iterations   = get_option( 'benchpress_query_iterations', 1 );

    $args = [
        'post_type'      => $post_type,
        'posts_per_page' => $query_type === 'multiple' ? $post_count : 1,
        'orderby'        => $orderby,
        'order'          => $order,
    ];

    // Add single post ID if it's a single post query.
    if ( $query_type === 'single' && ! empty( $post_id ) ) {
        $args['p'] = is_array( $post_id ) ? reset( $post_id ) : $post_id;
    }

    // Add taxonomy query if it's a multiple posts query.
    if ( $query_type === 'multiple' && ! empty( $taxonomy ) && ! empty( $tax_terms ) ) {
        $args['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => explode( ',', $tax_terms ),
            ],
        ];
    }

    // Benchmark execution time.
    $start = microtime( true );
    for ( $i = 0; $i < $iterations; $i++ ) {
        $query = new WP_Query( $args );
        wp_reset_postdata();
    }
    $end = microtime( true );

    $total_time   = $end - $start;
    $average_time = $total_time / $iterations;

    // Generate description based on query type.
    if ( $query_type === 'single' ) {
        $description = sprintf(
            esc_html__( 'Time to execute WP_Query by post ID %d, post type %s, ordering by %s (%s), averaged over %d iteration(s).', 'benchpress' ),
            reset( $post_id ),
            $post_type,
            $orderby,
            $order,
            $iterations
        );
    } else {
        $description = sprintf(
            esc_html__( 'Time to execute WP_Query for %d posts of post type %s, ordering by %s (%s), with taxonomy (%s) and terms (%s), averaged over %d iteration(s).', 'benchpress' ),
            $post_count,
            $post_type,
            $orderby,
            $order,
            $taxonomy ? $taxonomy : esc_html__( 'none', 'benchpress' ),
            $tax_terms ? $tax_terms : esc_html__( 'none', 'benchpress' ),
            $iterations
        );
    }

    return [
        'name'          => esc_html__( 'WP_Query Benchmark', 'benchpress' ),
        'execution_time'=> round( $average_time, 5 ),
        'description'   => $description,
    ];
}

/**
 * Benchmark Array Merge Methods.
 *
 * @since 1.0.0
 * @return array Benchmark data for array merge.
 */
function benchpress_benchmark_array_merge() {
    $loop_count = get_option( 'benchpress_loop_count', 1000000 );

    // Sample arrays for benchmarking.
    $array1 = range( 1, 100 );
    $array2 = range( 101, 200 );

    // Measure execution time for array_merge.
    $start_merge = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = array_merge( $array1, $array2 );
    }
    $end_merge  = microtime( true );
    $merge_time = $end_merge - $start_merge;

    // Measure execution time for array union (+).
    $start_union = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = $array1 + $array2;
    }
    $end_union = microtime( true );
    $union_time = $end_union - $start_union;

    // Calculate difference and which method is faster.
    $difference       = $merge_time - $union_time;
    $faster_or_slower = $difference > 0 ? 'slower' : 'faster';

    return [
        'name'          => esc_html__( 'Array Merge vs Union', 'benchpress' ),
        'execution_time'=> round( abs( $difference ), 5 ),
        'description'   => sprintf(
            esc_html__( 'Comparing array_merge and array union (+). The array_merge is %s by %s seconds on average over %d iterations.', 'benchpress' ),
            $difference > 0 ? 'slower' : 'faster',
            round( abs( $difference ), 5 ),
            $loop_count
        ),
    ];
}

/**
 * Benchmark String Concatenation Methods.
 *
 * @since  1.0.0
 * @return array Benchmark data for string concatenation.
 */
function benchpress_benchmark_string_concatenation() {
    $loop_count = get_option( 'benchpress_loop_count', 1000000 );

    $string1 = "Hello";
    $string2 = "World";

    // Measure execution time for dot operator.
    $start_dot = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = $string1 . ' ' . $string2;
    }
    $end_dot  = microtime( true );
    $dot_time = $end_dot - $start_dot;

    // Measure execution time for sprintf.
    $start_sprintf = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = sprintf( "%s %s", $string1, $string2 );
    }
    $end_sprintf  = microtime( true );
    $sprintf_time = $end_sprintf - $start_sprintf;

    // Calculate difference and determine which method is faster.
    $difference       = $dot_time - $sprintf_time;
    $faster_or_slower = $difference > 0 ? 'slower' : 'faster';

    return [
        'name'          => esc_html__( 'Dot Operator vs sprintf', 'benchpress' ),
        'execution_time'=> round( abs( $difference ), 5 ),
        'description'   => sprintf(
            esc_html__( 'Comparing dot operator (.) and sprintf for string concatenation. The dot operator is %s by %s seconds on average over %d iterations.', 'benchpress' ),
            $difference > 0 ? 'slower' : 'faster',
            round( abs( $difference ), 5 ),
            $loop_count
        ),
    ];
}

/**
 * Run all benchmarks.
 *
 * @since  1.0.0
 * @return array List of benchmark results.
 */
function benchpress_run_all_benchmarks() {
    $benchmarks = [];

    // Only add Switch vs Match benchmark if PHP 8.0+.
    if ( function_exists( 'benchpress_benchmark_switch_vs_match' ) && $switch_vs_match = benchpress_benchmark_switch_vs_match() ) {
        $benchmarks[] = $switch_vs_match;
    }

    if ( get_option( 'benchpress_enable_transient_vs_query', 1 ) ) {
        $benchmarks[] = benchpress_benchmark_transient_vs_direct_query();
    }

    if ( get_option( 'benchpress_enable_meta_query_test', 1 ) ) {
        $benchmarks[] = benchpress_benchmark_post_meta_access();
    }

    $benchmarks[] = benchpress_benchmark_wp_query_by_id();
    $benchmarks[] = benchpress_benchmark_array_merge();
    $benchmarks[] = benchpress_benchmark_string_concatenation();

    $benchmarks = apply_filters( 'benchpress_run_all_benchmarks', $benchmarks );

    return $benchmarks;
}

/**
 * Benchmark Transient Caching vs. Direct Database Queries.
 *
 * @since  1.0.0
 * @return array Benchmark data showing performance difference between transient caching and direct database querying.
 */
function benchpress_benchmark_transient_vs_direct_query() {
    // Retrieve settings.
    $loop_count = get_option( 'benchpress_loop_count', 10 );
    $post_type  = get_option( 'benchpress_post_type', 'post' );
    global $wpdb;

    // Build dynamic query.
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE post_type = %s LIMIT %d",
        $post_type,
        $loop_count
    );

    // Measure execution time for direct query..
    $start_direct = microtime( true );
    $wpdb->get_results( $query );
    $end_direct = microtime( true );
    $direct_query_time = $end_direct - $start_direct;

    // Measure execution time for transient caching.
    $start_transient = microtime( true );
    $cache_key       = 'benchpress_transient_query';
    $results         = get_transient( $cache_key );

    if ( false === $results ) {
        $results = $wpdb->get_results( $query );
        set_transient( $cache_key, $results, HOUR_IN_SECONDS );
    }
    $end_transient  = microtime( true );
    $transient_time = $end_transient - $start_transient;

    $difference       = $direct_query_time - $transient_time;
    $faster_or_slower = $difference > 0 ? 'slower' : 'faster';

    return [
        'name'          => esc_html__( 'Transient Caching vs Direct Query', 'benchpress' ),
        'execution_time'=> round( abs( $difference ), 5 ),
        'description'   => sprintf(
            esc_html__( 'Direct query is %s by %s seconds compared to transient caching.', 'benchpress' ),
            $faster_or_slower,
            round( abs( $difference ), 5 )
        ),
    ];
}

/**
 * Benchmark Post Meta Access Methods.
 *
 * Compares the performance of retrieving post meta using `get_post_meta()` vs. `WP_Meta_Query`.
 *
 * @since  1.0.0
 * @return array|null Benchmark data showing performance difference, or null if settings are missing.
 */
function benchpress_benchmark_post_meta_access() {
    if ( ! get_option( 'benchpress_enable_meta_query_test', 1 ) ) {
        return;
    }

    $loop_count = get_option( 'benchpress_loop_count', 10 );
    $meta_key   = get_option( 'benchpress_meta_key', '_sample_meta_key' );
    $query_type = get_option( 'benchpress_query_type', 'single' );
    $post_ids   = get_option( 'benchpress_post_id', [] );

    // Handle case where there are no post IDs.
    if ( empty( $post_ids ) || empty( $meta_key ) ) {
        return;
    }

    // Prepare the post ID(s) for single or multiple post queries.
    $post_ids = (array) $post_ids;

    // Benchmark `get_post_meta`.
    $start_meta = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        foreach ( $post_ids as $post_id ) {
            $meta_value = get_post_meta( $post_id, $meta_key, true );
        }
    }
    $end_meta           = microtime( true );
    $get_post_meta_time = $end_meta - $start_meta;

    // Benchmark `WP_Meta_Query`.
    $start_query = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $meta_query = new WP_Meta_Query( [
            'relation' => 'OR',
            array_map( function( $post_id ) use ( $meta_key, $meta_value ) {
                return [
                    'key'     => $meta_key,
                    'value'   => $meta_value ?? '',
                    'compare' => '=',
                ];
            }, $post_ids ),
        ] );
    }
    $end_query       = microtime( true );
    $meta_query_time = $end_query - $start_query;

    // Calculate the difference.
    $difference       = $get_post_meta_time - $meta_query_time;
    $faster_or_slower = $difference > 0 ? 'slower' : 'faster';

    // Format the description based on single or multiple posts.
    $description = $query_type === 'single'
        ? sprintf(
            esc_html__( 'Retrieving post meta for post ID %d using get_post_meta is %s by %s seconds compared to WP_Meta_Query.', 'benchpress' ),
            $post_ids[0],
            $faster_or_slower,
            round( abs( $difference ), 5 )
        )
        : sprintf(
            esc_html__( 'Retrieving post meta for %d posts using get_post_meta is %s by %s seconds compared to WP_Meta_Query.', 'benchpress' ),
            count( $post_ids ),
            $faster_or_slower,
            round( abs( $difference ), 5 )
        );

    return [
        'name'          => esc_html__( 'get_post_meta() vs WP_Meta_Query', 'benchpress' ),
        'execution_time'=> round( abs( $difference ), 5 ),
        'description'   => $description,
    ];
}
