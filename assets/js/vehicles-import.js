jQuery(document).ready(function ($) {
    const progressWrapper = $('.progress-wrapper');
    const importProgress = $('#import-progress');

    // Handle click events for the import links
    $(document).on('click', '.start-import-link', function (event) {
        const importButton = $(this);
        const file = importButton.data('file');
        const nonce = importButton.data('nonce');

        // Disable the link and show the progress bar
        importButton.addClass('disabled').blur();
        progressWrapper.show();

        // Start the import process
        $.get(vehiclesImport.ajaxUrl, { action: 'start_vehicle_import', file: file, nonce: nonce })
            .done(function (data) {
                if (data.success) {
                    const totalRows = data.data.total;
                    processBatch(file, nonce, totalRows, 0);
                }
            });
    });

    function processBatch(file, nonce, totalRows, processedRows) {
        const batchSize = 10;
        $.post(vehiclesImport.ajaxUrl, {
            action: 'process_vehicle_import_batch',
            file: file,
            offset: processedRows,
            limit: batchSize,
            nonce: nonce
        })
            .done(function (data) {
                if (data.success) {
                    processedRows += batchSize;
                    updateProgressBar(processedRows, totalRows);
                    fetchVehicleCount();    // From PageImport.php

                    if (processedRows < totalRows) {
                        processBatch(file, nonce, totalRows, processedRows);
                    } else {
                        updateProgressAndShowAlert();
                    }
                }
            });
    }

    function updateProgressAndShowAlert() {
        // Update the progress to 100%
        document.getElementById('import-progress').style.width = '100%';

        // Use a small timeout to ensure the DOM updates before the alert is shown
        setTimeout(function () {
            alert('Import completed successfully!');
        }, 400); // Delay of 100 milliseconds

        $('.start-import-link').removeClass('disabled');

    }

    function updateProgressBar(current, total) {
        const progress = Math.min((current / total) * 100, 100);
        importProgress.css('width', progress + '%').text(Math.round(progress) + '%');
    }
});