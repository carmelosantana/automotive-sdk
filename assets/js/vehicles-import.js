jQuery(document).ready(function ($) {
    const importButton = $('#start-import');

    if (importButton.length) {
        let totalRows = 0;
        let processedRows = 0;
        const batchSize = 10;

        importButton.on('click', function () {
            const file = importButton.data('file');
            const nonce = importButton.data('nonce'); // Pass nonce
            $.get(vehiclesImport.ajaxUrl, { action: 'start_vehicle_import', file: file, nonce: nonce })
                .done(function (data) {
                    if (data.success) {
                        totalRows = data.data.total;
                        processBatch();
                    }
                });
        });

        function processBatch() {
            $.post(vehiclesImport.ajaxUrl, {
                action: 'process_vehicle_import_batch',
                file: importButton.data('file'),
                offset: processedRows,
                limit: batchSize,
                nonce: importButton.data('nonce') // Pass nonce
            })
                .done(function (data) {
                    if (data.success) {
                        processedRows += batchSize;
                        updateProgressBar(processedRows, totalRows);
                        fetchVehicleCount();

                        if (processedRows < totalRows) {
                            processBatch();
                        } else {
                            alert('Import completed');
                        }
                    }
                });
        }

        function updateProgressBar(current, total) {
            const progress = Math.min((current / total) * 100, 100);
            $('#import-progress').css('width', progress + '%').text(Math.round(progress) + '%');
        }
    }
});