<?php
/**
 * PHP 8-specific benchmark functions for BenchPress plugin.
 *
 * @package BenchPress
 */

/**
 * Benchmark Switch vs Match performance.
 *
 * @since  1.0.0
 * @return void|array Benchmark data showing performance difference between switch and match.
 */
function benchpress_benchmark_switch_vs_match() {
    if ( ! get_option( 'benchpress_enable_switch_vs_match', 1 ) ) {
        return;
    }

    $loop_count = get_option( 'benchpress_loop_count', 1000000 );

    // Measure execution time for switch statement.
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

    // Measure execution time for match expression.
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
        'name'           => esc_html__( 'Switch vs Match', 'benchpress' ),
        'execution_time' => round( abs( $difference ), 5 ),
        'description'    => sprintf(
            esc_html__( 'The switch statement is %s by %s seconds compared to match.', 'benchpress' ),
            $faster_or_slower,
            round( abs( $difference ), 5 )
        ),
    ];
}
