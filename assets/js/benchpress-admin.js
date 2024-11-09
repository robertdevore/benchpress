jQuery(document).ready(function($) {
    // Initialize Select2 for multiple post selection.
    $('#benchpress_post_id').select2({
        placeholder: 'Select posts',
        allowClear: true,
        width: '100%',
    });

    // Show or hide fields based on query type.
    function toggleQueryFields() {
        if ($('select[name="benchpress_query_type"]').val() === 'multiple') {
            $('#single-post-fields').hide();
            $('#multiple-post-fields').show();
        } else {
            $('#single-post-fields').show();
            $('#multiple-post-fields').hide();
        }
    }

    // Initial check on page load.
    toggleQueryFields();

    // Event listener for query type change.
    $('select[name="benchpress_query_type"]').on('change', toggleQueryFields);
});
