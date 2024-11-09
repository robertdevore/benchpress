<?php
/**
 * Benchmark functions for BenchPress plugin.
 *
 * @package BenchPress
 */

/**
 * Benchmark Switch vs Match performance.
 *
 * @since  1.1.0
 * @return void|array Benchmark data showing performance difference between switch and match.
 */
function benchpress_benchmark_switch_vs_match() {
    if ( ! get_option( 'benchpress_enable_switch_vs_match', 1 ) ) {
        return;
    }

    $loop_count = get_option( 'benchpress_loop_count', 1000000 );

    // Measure execution time for switch statement
    $start_switch = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        switch ( $i % 3 ) {
            case 0:
                $result = 'zero';
                break;
            case 1:
                $result = 'one';
                break;
            case 2:
                $result = 'two';
                break;
        }
    }
    $end_switch  = microtime( true );
    $switch_time = $end_switch - $start_switch;

    // Measure execution time for match expression
    $start_match = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = match ( $i % 3 ) {
            0 => 'zero',
            1 => 'one',
            2 => 'two',
        };
    }
    $end_match = microtime( true );
    $match_time = $end_match - $start_match;

    // Calculate the difference.
    $difference       = $switch_time - $match_time;
    $faster_or_slower = $difference > 0 ? 'slower' : 'faster';

    return [
        'name'          => esc_html__( 'Switch vs Match', 'benchpress' ),
        'execution_time'=> round( abs( $difference ), 5 ),
        'description'   => sprintf(
            esc_html__( 'The switch statement is %s by %s seconds compared to match.', 'benchpress' ),
            $faster_or_slower,
            round( abs( $difference ), 5 )
        ),
    ];
}

/**
 * Benchmark WP_Query by ID with customizable options.
 *
 * @since  1.1.0
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

    // Add single post ID if it's a single post query
    if ( $query_type === 'single' && ! empty( $post_id ) ) {
        $args['p'] = is_array( $post_id ) ? reset( $post_id ) : $post_id;
    }

    // Add taxonomy query if it's a multiple posts query
    if ( $query_type === 'multiple' && ! empty( $taxonomy ) && ! empty( $tax_terms ) ) {
        $args['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => explode( ',', $tax_terms ),
            ],
        ];
    }

    // Benchmark execution time
    $start = microtime( true );
    for ( $i = 0; $i < $iterations; $i++ ) {
        $query = new WP_Query( $args );
        wp_reset_postdata();
    }
    $end = microtime( true );

    $total_time   = $end - $start;
    $average_time = $total_time / $iterations;

    // Generate description based on query type
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

    // Measure execution time for array_merge
    $start_merge = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = array_merge( $array1, $array2 );
    }
    $end_merge = microtime( true );
    $merge_time = $end_merge - $start_merge;

    // Measure execution time for array union (+)
    $start_union = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = $array1 + $array2;
    }
    $end_union = microtime( true );
    $union_time = $end_union - $start_union;

    // Calculate difference and which method is faster
    $difference = $merge_time - $union_time;
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

    // Measure execution time for dot operator
    $start_dot = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = $string1 . ' ' . $string2;
    }
    $end_dot = microtime( true );
    $dot_time = $end_dot - $start_dot;

    // Measure execution time for sprintf
    $start_sprintf = microtime( true );
    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = sprintf( "%s %s", $string1, $string2 );
    }
    $end_sprintf = microtime( true );
    $sprintf_time = $end_sprintf - $start_sprintf;

    // Calculate difference and determine which method is faster
    $difference = $dot_time - $sprintf_time;
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

    if ( $switch_vs_match = benchpress_benchmark_switch_vs_match() ) {
        $benchmarks[] = $switch_vs_match;
    }

    $benchmarks[] = benchpress_benchmark_wp_query_by_id();
    $benchmarks[] = benchpress_benchmark_array_merge();
    $benchmarks[] = benchpress_benchmark_string_concatenation();

    return $benchmarks;
}
