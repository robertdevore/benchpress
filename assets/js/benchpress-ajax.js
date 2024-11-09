jQuery(document).ready(function($) {
    // Refresh button click handler
    $('#benchpress-refresh-btn').on('click', function() {
        $.ajax({
            url: benchpress_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'benchpress_refresh',
                _ajax_nonce: benchpress_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#benchpress-results').html(response.data.html);
                }
            }
        });
    });

    // Clear all snapshots click handler
    $('#benchpress-clear-snapshots-btn').on('click', function() {
        if (confirm('Are you sure you want to delete all snapshots? This action cannot be undone.')) {
            $.ajax({
                url: benchpress_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'benchpress_clear_all_snapshots',
                    _ajax_nonce: benchpress_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.wp-list-table tbody').empty(); // Clear table rows in the UI
                    } else {
                        alert(response.data.message); // Show error message if delete fails
                    }
                }
            });
        }
    });

    // Download snapshots as CSV
    $('#benchpress-download-snapshots-btn').on('click', function() {
        $('#benchpress-download-snapshots-btn').on('click', function() {
            $.ajax({
                url: benchpress_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'benchpress_download_snapshots',
                    _ajax_nonce: benchpress_ajax.nonce
                },
                xhrFields: {
                    responseType: 'blob' // Expect binary data as the response
                },
                success: function(response, status, xhr) {
                    // Create a downloadable link with the CSV blob
                    var blob = new Blob([response], { type: 'text/csv' });
                    var downloadUrl = URL.createObjectURL(blob);
    
                    // Create a temporary link and click it to trigger download
                    var link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = `${benchpress_ajax.site_name}-benchpress-${benchpress_ajax.datetime}.csv`;
                    document.body.appendChild(link);
                    link.click();
    
                    // Clean up
                    document.body.removeChild(link);
                    URL.revokeObjectURL(downloadUrl);
                },
                error: function() {
                    alert('Failed to download snapshots. Please try again.');
                }
            });
        });
    });

    // Snapshot button click handler
    $('#benchpress-snapshot-btn').on('click', function() {
        $.ajax({
            url: benchpress_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'benchpress_snapshot',
                _ajax_nonce: benchpress_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                }
            }
        });
    });

    // Open modal and populate with snapshot data
    $(document).on('click', '.view-data-btn', function() {
        var snapshotData = $(this).data('snapshot');

        if (typeof snapshotData === 'string') {
            snapshotData = JSON.parse(snapshotData);
        }

        var formattedData = '<ul>';
        snapshotData.forEach(function(benchmark) {
            formattedData += '<li><strong>' + benchmark.name + ':</strong> ' +
                             benchmark.execution_time + ' seconds<br><em>' + 
                             benchmark.description + '</em></li>';
        });
        formattedData += '</ul>';

        $('#snapshotModalData').html(formattedData);
        $('#snapshotModal').css('display', 'block');
    });

    // Close the modal
    $(document).on('click', '.snapshot-modal-close', function() {
        $('#snapshotModal').hide();
    });

    // Close modal when clicking outside the modal content
    $(window).on('click', function(event) {
        if ($(event.target).is('#snapshotModal')) {
            $('#snapshotModal').hide();
        }
    });

    // Delete snapshot button click handler
    $(document).on('click', '.delete-snapshot-btn', function() {
        if (confirm('Are you sure you want to delete this snapshot?')) {
            var snapshotId = $(this).data('id');
            var row = $(this).closest('tr');

            $.ajax({
                url: benchpress_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'benchpress_delete_snapshot',
                    _ajax_nonce: benchpress_ajax.nonce,
                    snapshot_id: snapshotId
                },
                success: function(response) {
                    if (response.success) {
                        row.remove(); // Remove the row from the table
                    } else {
                        alert(response.data.message); // Error alert if deletion fails
                    }
                }
            });
        }
    });

});
