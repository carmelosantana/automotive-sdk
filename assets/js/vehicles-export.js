jQuery(document).ready(function ($) {
    $('.export-json-btn, .export-csv-btn').on('click', function (e) {
        e.preventDefault();
        var exportType = $(this).data('type');
        var $button = $(this);

        // Disable the button and change cursor to indicate activity
        $button.prop('disabled', true);
        $('body').css('cursor', 'wait');

        exportVehicles(exportType, $button);
    });

    function exportVehicles(type, $button) {
        var data = {
            action: 'export_' + type,
            nonce: vehiclesExport.nonce
        };

        $.post(vehiclesExport.ajaxUrl, data, function (response) {
            if (response.success) {
                // Download the file
                var a = document.createElement('a');
                a.href = response.data.url;
                a.download = 'vehicles-export.' + type;
                a.click();

                // Reset cursor and enable button
                $('body').css('cursor', 'default');
                $button.prop('disabled', false);
            } else {
                alert('Export failed: ' + response.data.message);

                // Reset cursor and enable button
                $('body').css('cursor', 'default');
                $button.prop('disabled', false);
            }
        });
    }
});