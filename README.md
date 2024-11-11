# BenchPress

**BenchPress** is a WordPress® plugin for benchmarking PHP code snippets, WordPress® queries, and other critical operations. 

It's designed to help developers evaluate and optimize code performance by running benchmarks and capturing snapshots for later comparison.
* * *

## Table of Contents

- [Installation](#installation)
- [Setup](#setup)
- [Usage](#usage)
- [Available Benchmarks](#available-benchmarks)
- [Customizing Benchmarks](#customizing-benchmarks)
- [Snapshots](#snapshots)
- [Plugin Settings](#plugin-settings)
- [Contributing](#contributing)
- [License](#license)
* * *

## Installation

1. **Download** the plugin ZIP from the [GitHub repository](https://github.com/robertdevore/benchpress).
2. **Upload** it via WordPress® Admin:
    - Go to `Plugins` > `Add New`.
    - Click `Upload Plugin`, select the downloaded ZIP, and click `Install Now`.
3. **Activate** the plugin via the `Plugins` page in the WordPress® Admin.
* * *

## Setup

BenchPress automatically creates a custom database table to store snapshots of benchmark results on activation. The plugin also includes settings to customize benchmark runs. To configure these:

1. Go to `BenchPress > Settings` in your WordPress® admin sidebar.
2. Adjust loop counts, post IDs, and other options to customize how each benchmark runs.

* * *

## Usage

Once installed and configured, you can run benchmarks, view results, and save snapshots for later reference:

1. **Running Benchmarks**:

    - Navigate to `BenchPress` in your WordPress® admin menu.
    - Click `Refresh Tests` to run all enabled benchmarks and view results.
2. **Saving Snapshots**:

    - On the main `BenchPress` page, click `Save Snapshot` to save a record of the current benchmark results.
3. **Viewing Snapshots**:

    - Go to `BenchPress > Snapshots` to view all saved snapshots. Each snapshot can be viewed, downloaded, or deleted.
* * *

## Available Benchmarks

BenchPress comes with several built-in benchmarks. Here's a quick overview:

- **Switch vs. Match**: Compares the performance of PHP `switch` vs `match` statements.
- **Transient vs. Direct Query**: Tests the speed of transient caching against direct database queries.
- **Post Meta Access**: Compares `get_post_meta()` with `WP_Meta_Query` for retrieving post meta data.
- **WP_Query by ID**: Measures query performance for retrieving single or multiple posts.
- **Array Merge vs. Union**: Compares `array_merge` with the array union (`+`) operator.
- **String Concatenation**: Benchmarks PHP's `.` operator vs `sprintf`.
* * *

## Customizing Benchmarks

### Adding Custom Benchmarks

BenchPress includes a `benchpress_run_all_benchmarks` filter to allow you to add custom benchmarks. Here's an example of how to add your own benchmark:

```
add_filter( 'benchpress_run_all_benchmarks', function( $benchmarks ) {
    // Define your custom benchmark
    $benchmarks[] = custom_benchmark_example();
    return $benchmarks;
});

// Custom benchmark function
function custom_benchmark_example() {
    $loop_count = get_option( 'custom_benchmark_loop_count', 100000 );
    $start_time = microtime( true );

    for ( $i = 0; $i < $loop_count; $i++ ) {
        $result = $i * 2; // Example operation
    }

    $execution_time = microtime( true ) - $start_time;

    return [
        'name'          => esc_html__( 'Custom Benchmark', 'benchpress' ),
        'execution_time'=> round( $execution_time, 5 ),
        'description'   => sprintf( esc_html__( 'Executed a loop of %d iterations.', 'benchpress' ), $loop_count ),
    ];
}
```

### Removing Benchmarks

If you want to remove a specific benchmark, you can use the same `benchpress_run_all_benchmarks` filter. For example, to remove the "Switch vs Match" benchmark:
```
add_filter( 'benchpress_run_all_benchmarks', function( $benchmarks ) {
    return array_filter( $benchmarks, function( $benchmark ) {
        return $benchmark['name'] !== esc_html__( 'Switch vs Match', 'benchpress' );
    });
});
```

### Accessing Benchmark Data

To access saved snapshots for analysis or custom display, use the custom database table created by BenchPress, which stores each snapshot as JSON data.

* * *

## Snapshots

Snapshots are records of benchmark results that you can refer back to later. Snapshots can be managed in the `BenchPress > Snapshots` page:

- **Clear Snapshots**: Clears all stored snapshots.
- **Download Snapshots**: Downloads all snapshots as a CSV file.
* * *

## Plugin Settings

To configure BenchPress benchmarks, navigate to `BenchPress > Settings`. Options include:

- **Loop Count for Benchmarks**: Set the number of iterations for each benchmark.
- **Enable Benchmarks**: Select which benchmarks to run.
- **WP_Query Settings**: Configure `WP_Query` options, including post types, IDs, taxonomy terms, etc.
* * *

## Contributing

BenchPress is an open-source project, and contributions are welcome! 

To contribute:

1. **Fork** the repository.
2. **Create** a new branch for your feature or fix.
3. **Submit** a pull request with a clear description of your changes.
* * *