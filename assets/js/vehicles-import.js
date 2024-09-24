jQuery(document).ready(function ($) {
    const progressWrapper = $('.progress-wrapper');
    const importProgress = $('#progress-bar');

    // Handle click events for the import links
    $(document).on('click', '.start-import-link', function (event) {
        const importButton = $(this);
        const file = importButton.data('file');
        const nonce = importButton.data('nonce');
        const profile = $('#import_profile').val(); // Get the selected profile

        // Disable the link and show the progress bar
        importButton.addClass('disabled').blur();
        progressWrapper.show();

        // Start the import process
        $.get(vehiclesImport.ajaxUrl, { action: 'start_vehicle_import', file: file, nonce: nonce, profile: profile })
            .done(function (data) {
                if (data.success) {
                    const totalRows = data.data.total;
                    processBatch(file, nonce, totalRows, 0, profile); // Pass the profile
                }
            });
    });

    function processBatch(file, nonce, totalRows, processedRows, profile) {
        const batchSize = 10;
        $.post(vehiclesImport.ajaxUrl, {
            action: 'process_vehicle_import_batch',
            file: file,
            offset: processedRows,
            limit: batchSize,
            nonce: nonce,
            profile: profile // Pass the profile
        })
            .done(function (data) {
                if (data.success) {
                    processedRows += batchSize;
                    updateProgressBar(processedRows, totalRows);
                    fetchVehicleCount();

                    if (processedRows < totalRows) {
                        processBatch(file, nonce, totalRows, processedRows, profile);
                    } else {
                        updateProgressAndShowAlert();
                    }
                }
            });
    }

    function updateProgressAndShowAlert() {
        // Update the progress to 100%
        document.getElementById('progress-bar').style.width = '100%';

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