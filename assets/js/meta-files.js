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
                var headersByFile = response.data.headers_by_file;  // Fetched headers grouped by file base name

                // Populate each meta dropdown with the CSV headers grouped by file
                $('.meta-dropdown').each(function () {
                    var select = $(this);
                    var currentValue = select.val(); // Get the current value of the dropdown
                    var hasValue = !!currentValue; // Check if the field has a value

                    // Keep the current value in the dropdown
                    if (hasValue && select.find('option[value="' + currentValue + '"]').length === 0) {
                        select.append('<option value="' + currentValue + '" disabled="disabled" selected="selected">' + currentValue + ' (Unavailable)</option>');
                    }

                    // Clear the dropdown but retain the unavailable value
                    select.empty(); // Clear existing options except for the unavailable one
                    select.append('<option value="">Select a CSV column</option>'); // Default option

                    // Add headers grouped by file base name
                    $.each(headersByFile, function (fileName, headers) {
                        var optgroup = $('<optgroup></optgroup>').attr('label', fileName);
                        $.each(headers, function (index, header) {
                            var option = $('<option></option>')
                                .attr('value', header)
                                .text(header);

                            // If this header was previously selected, mark it as selected
                            if (header === currentValue) {
                                option.attr('selected', 'selected');
                            }

                            optgroup.append(option);
                        });
                        select.append(optgroup); // Add the group to the dropdown
                    });

                    // Re-enable previously unavailable values if they are now available
                    if (hasValue && select.find('option[value="' + currentValue + '"]').length === 0) {
                        select.append('<option value="' + currentValue + '" disabled="disabled" selected="selected">' + currentValue + ' (Unavailable)</option>');
                    }
                });
            } else {
                console.log('Error loading file headers');
            }
        });
    }

    // Bind to the change event on the file multi-select box using .on()
    $('#csv_file').on('change', getFileHeaders);

    // On page load, fetch headers for the selected file(s)
    getFileHeaders();
});