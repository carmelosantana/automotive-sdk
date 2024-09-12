<?php

declare(strict_types=1);

namespace CarmeloSantana\VinImporter\Import;

class Files
{
    public function getAll()
    {
        // save files to transient for 5 minutes
        $files = get_transient('vin_importer_files');
        if ($files === false) {
            $files = $this->refresh();
            set_transient('vin_importer_files', $files, 300);
        }
        return $files;
    }

    /**
     * Get paginated data from the CSV file.
     *
     * @param string $file_path Path to the file.
     * @param string $delimiter Delimiter used in the CSV file. Default is ','.
     * @param int $page The page number to retrieve (1-based).
     * @param int $limit Number of rows to retrieve per page.
     * @param bool $header Whether the first row is a header row.
     *
     * @return array|false Paginated data or false on failure.
     */
    public function getData(string $file_path, string $delimiter = ',', int $page = 0, int $limit = 10, $header = true)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $file_handle = fopen($file_path, 'r');

        $file_data = [];

        if ($page > 0) {
            // Ensure that the page and limit parameters are valid
            $page = max(1, $page);
            $limit = max(1, $limit);

            $current_row = 0;  // Keep track of the current row
            $start_row = ($page - 1) * $limit;  // The first row of the page

            if ($header) {
                // Skip the header row
                $file_header = fgetcsv($file_handle, 0, $delimiter);
                $file_data[] = $file_header;
            }

            // Skip rows before the start of the page
            while ($current_row < $start_row && fgetcsv($file_handle, 0, $delimiter) !== false) {
                $current_row++;
            }

            // Read only the rows for the current page
            while (($data = fgetcsv($file_handle, 0, $delimiter)) !== false) {
                if ($current_row >= $start_row && $current_row < $start_row + $limit) {
                    $file_data[] = $data;
                }
                $current_row++;

                // Stop reading if we've reached the end of the page
                if (count($file_data) >= $limit) {
                    break;
                }
            }
        } else {

            // Read all rows
            while (($data = fgetcsv($file_handle, 0, $delimiter)) !== false) {
                $file_data[] = $data;
            }
        }

        fclose($file_handle);

        return $file_data;
    }

    public function getPath($file)
    {
        $files = $this->refresh();
        $file_path = $files[$file];

        if (!file_exists($file_path)) {
            return false;
        }

        return $file_path;
    }

    public function getPathInfo($file)
    {
        $file = $_GET['file'] ?? null;
        $files = $this->refresh();

        $file_path = $files[$file];
        $file_info = pathinfo($file_path);

        if (!file_exists($file_path)) {
            echo '✘ File does not exist.';
            return false;
        }

        return $file_info;
    }

    public function refresh()
    {
        // check if we have any csv, tsv, or json files in the uploads directory, traverse all subdirectories
        $files = [];
        $dirs = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ABSPATH . 'wp-content/uploads'));
        foreach ($dirs as $dir) {
            if ($dir->isDir()) {
                continue;
            }

            if (in_array($dir->getExtension(), ['csv', 'tsv', 'json'])) {
                // store file in array with full path via md5 as key
                $files[md5($dir->getPathname())] = $dir->getPathname();
            }
        }

        return $files;
    }
}
