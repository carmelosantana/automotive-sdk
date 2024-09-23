jQuery(document).ready(function ($) {
    var universalMapping = {};

    // Fetch the universal mapping via AJAX
    function getUniversalMapping(callback) {
        $.post(metaAjax.ajaxUrl, {
            action: 'get_universal_mapping'
        }, function (response) {
            if (response.success) {
                universalMapping = response.data;
                if (typeof callback === 'function') {
                    callback(); // Call the callback function when mapping is ready
                }
            } else {
                console.log('Error fetching universal mapping');
            }
        });
    }

    // Function to fetch and populate headers, and preselect matching fields
    function getFileHeaders(selectedFiles = null) {
        if (!selectedFiles) {
            selectedFiles = $('#csv_file').val(); // Get the selected file(s) from the dropdown
        }

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
                    var metaKey = select.attr('name').replace('csv_meta_mapping[', '').replace('][csv]', '');  // Get the meta key (e.g., 'vin', 'make', etc.)
                    var currentValue = select.val(); // Get the current value of the dropdown
                    var hasValue = !!currentValue; // Check if the field has a value

                    // Keep the current value in the dropdown
                    if (hasValue && select.find('option[value="' + currentValue + '"]').length === 0) {
                        select.append('<option value="' + currentValue + '" disabled="disabled" selected="selected">' + currentValue + ' (Unavailable)</option>');
                    }

                    select.empty(); // Clear existing options except for the unavailable one
                    select.append('<option value="">Select a CSV column</option>'); // Default option

                    // Add headers grouped by file base name
                    $.each(headersByFile, function (fileName, headers) {
                        var optgroup = $('<optgroup></optgroup>').attr('label', fileName);

                        $.each(headers, function (index, header) {
                            var option = $('<option></option>')
                                .attr('value', header)
                                .text(header);

                            // Preselect if this header matches the universal mapping for this meta key
                            if (universalMapping[metaKey] && universalMapping[metaKey].includes(header)) {
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

    // Check if there is a 'file' parameter in the URL
    var urlParams = new URLSearchParams(window.location.search);
    var fileParam = urlParams.get('file');

    // If there is a 'file' parameter, pre-select that file
    if (fileParam) {
        $('#csv_file').val([fileParam]);  // Pre-select the file in the dropdown
        getUniversalMapping(function () {
            getFileHeaders([fileParam]);  // Fetch and populate headers for the selected file after mapping is loaded
        });
    }

    // Bind to the change event on the file multi-select box using .on()
    $('#csv_file').on('change', function () {
        getUniversalMapping(getFileHeaders);  // Fetch mapping first, then file headers
    });

    // On page load, fetch headers for the selected file(s)
    if (!fileParam) {
        getUniversalMapping(getFileHeaders);  // Only fetch headers if no file param exists
    }
});