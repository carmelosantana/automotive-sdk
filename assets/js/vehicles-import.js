jQuery(document).ready(function ($) {
    const importButton = $('#start-import');

    if (importButton.length) {
        let totalRows = 0;
        let processedRows = 0;
        const batchSize = 10;
        const fileKey = importButton.data('file');

        importButton.on('click', function () {
            const file = importButton.data('file');

            $.get(vehiclesImport.ajaxUrl, { action: 'start_vehicle_import', file: file })
                .done(function (data) {
                    if (data.success) {
                        totalRows = data.data.total;
                        processBatch();
                    }
                });
        });

        function processBatch(file) {
            $.post(vehiclesImport.ajaxUrl, {
                action: 'process_vehicle_import_batch',
                offset: processedRows,
                limit: batchSize,
                file: fileKey
            })
                .done(function (data) {
                    if (data.success) {
                        processedRows += batchSize;
                        updateProgressBar(processedRows, totalRows);

                        if (processedRows < totalRows) {
                            processBatch(file);
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