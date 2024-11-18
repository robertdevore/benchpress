<?php
/**
 * Class BenchPress_Table
 *
 * Extends WP_List_Table to display benchmarking results in the WordPress admin.
 *
 * @package BenchPress
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BenchPress_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => esc_html__( 'Benchmark', 'benchpress' ),
            'plural'   => esc_html__( 'Benchmarks', 'benchpress' ),
            'ajax'     => false,
        ] );
    }

    /**
     * Define table columns.
     * 
     * @since  1.0.0
     * @return array List of columns.
     */
    public function get_columns() {
        return [
            'name'          => esc_html__( 'Benchmark Name', 'benchpress' ),
            'execution_time'=> esc_html__( 'Execution Time (s)', 'benchpress' ),
            'description'   => esc_html__( 'Description', 'benchpress' ),
        ];
    }

    /**
     * Prepare items for the table.
     * 
     * @since  1.0.0
     * @return void
     */
    public function prepare_items() {
        $benchmarks  = benchpress_run_all_benchmarks();
        $this->items = $benchmarks;

        $columns = $this->get_columns();
        $this->_column_headers = [ $columns, [], [] ];
    }

    /**
     * Default column display.
     *
     * @param array  $item        Item data.
     * @param string $column_name Column name.
     * 
     * @since  1.0.0
     * @return mixed Column output.
     */
    public function column_default( $item, $column_name ) {
        return $item[ $column_name ] ?? '';
    }

    /**
     * Override the parent display_tablenav method to remove empty divs.
     *
     * @param string $which The position of the tablenav (top or bottom).
     * 
     * @since  1.0.0
     * @return void
     */
    public function display_tablenav( $which ) {
        // Do nothing.
    }

    /**
     * Override the parent get_bulk_actions method to remove bulk actions.
     *
     * 
     * @since  1.0.0
     * @return array An empty array of bulk actions.
     */
    public function get_bulk_actions() {
        return [];
    }

    /**
     * Override pagination to prevent showing pagination controls.
     *
     * 
     * @since  1.0.0
     * @return void
     */
    public function pagination( $which ) {
        // No pagination.
    }

}
