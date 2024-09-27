jQuery(document).ready(function ($) {
    var universalMapping = {};
    var headersByFile = {};

    // Function to fetch universal mapping via AJAX
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
                headersByFile = response.data.headers_by_file;  // Fetched headers grouped by file base name

                // Populate dropdowns for meta and taxonomy fields
                populateDropdowns('.meta-dropdown');
                populateDropdowns('.taxonomy-dropdown');
            } else {
                console.log('Error loading file headers');
            }
        });
    }

    // Function to populate dropdowns with headers
    function populateDropdowns(selector) {
        $(selector).each(function () {
            var select = $(this);
            var mappingKey = select.attr('name').replace(/csv_(meta|taxonomy)_mapping\[/, '').replace(/\]\[csv\]/, '');  // Get the mapping key
            var currentValue = select.val(); // Get the current value of the dropdown

            // Preserve the selected value before clearing the options
            var hasValue = !!currentValue; // Check if the field has a value (user-selected)

            // Clear existing options except for the previously selected value
            select.find('option:not([disabled="disabled"])').remove();
            select.append('<option value="">Select a CSV column</option>'); // Default option

            // Add headers grouped by file base name
            $.each(headersByFile, function (fileName, headers) {
                var optgroup = $('<optgroup></optgroup>').attr('label', fileName);

                $.each(headers, function (index, header) {
                    var option = $('<option></option>')
                        .attr('value', header)
                        .text(header);

                    // Check if the current value matches the header
                    if (hasValue && currentValue === header) {
                        option.attr('selected', 'selected');
                    }

                    // Preselect if the field has no current value AND the header matches the universal mapping
                    if (!hasValue && universalMapping[mappingKey] && universalMapping[mappingKey].includes(header)) {
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
    }

    // Check if there is a 'file' parameter in the URL
    var urlParams = new URLSearchParams(window.location.search);
    var fileParam = urlParams.get('file');

    // If there is a 'file' parameter, pre-select that file and populate fields
    if (fileParam) {
        $('#csv_file').val([fileParam]);  // Pre-select the file in the dropdown
        getUniversalMapping(function () {
            getFileHeaders([fileParam]);  // Fetch and populate headers for the selected file after mapping is loaded
        });
    }

    // Bind to the change event on the file multi-select box
    $('#csv_file').on('change', function () {
        getUniversalMapping(getFileHeaders);  // Fetch mapping first, then file headers when a new file is selected
    });
});