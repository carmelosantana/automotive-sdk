jQuery(document).ready(function ($) {
    // Function to fetch and populate headers
    function getFileHeaders() {
        var selectedFiles = $('#csv_file').val(); // Get the selected file(s)

        // Make an AJAX request to fetch headers for the selected file(s)
        $.post(metaAjax.ajaxUrl, {
            action: 'get_file_headers',  // The action defined in the PHP
            files: selectedFiles,  // Send the selected file keys
        }, function (response) {
            if (response.success) {
                // Populate each meta dropdown with the CSV headers
                $('.meta-dropdown').each(function () {
                    var select = $(this);
                    var currentValue = select.val(); // Get the current value
                    var hasValue = !!currentValue; // Check if the field has a value

                    select.empty(); // Clear existing options
                    select.append('<option value="">Select a CSV column</option>'); // Default option

                    // Add headers to the dropdown
                    $.each(response.data.headers, function (index, header) {
                        var option = $('<option></option>')
                            .attr('value', header)
                            .text(header);

                        if (header === currentValue) {
                            option.attr('selected', 'selected'); // Keep the option selected
                        }

                        select.append(option);
                    });

                    // If the current value was not found in the new headers, disable the field
                    if (hasValue && !select.find('option[value="' + currentValue + '"]').length) {
                        select.append('<option value="' + currentValue + '" disabled="disabled" selected="selected">' + currentValue + ' (Unavailable)</option>');
                    }
                });
            } else {
                console.log('Error loading file headers');
            }
        });
    }

    // Handle change event on the file multi-select box using .on()
    $('#csv_file').on('change', getFileHeaders);

    // On page load, fetch headers for the selected file(s)
    getFileHeaders();
});