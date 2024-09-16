<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

/**
 * Class Files
 * Handles operations related to file management including checking existence, retrieving data, and refreshing file lists.
 */
class Files
{
    /**
     * @var array List of all files with their md5 hash as keys and paths as values.
     */
    private array $files;

    /**
     * Files constructor.
     * Initializes the file list.
     */
    public function __construct()
    {
        $this->files = $this->getAll();
    }

    /**
     * Checks if a file exists in the file list.
     *
     * @param string $file The file key to check.
     * @return bool True if the file exists, false otherwise.
     */
    public function exists(string $file): bool
    {
        return array_key_exists($file, $this->files);
    }

    /**
     * Retrieves all files from cache or refreshes the list if not available.
     *
     * @return array The list of all files.
     */
    public function getAll(bool $refresh = false): array
    {
        if ($refresh) {
            delete_transient('vehicle_sdk_import_library');
            $files = false;
        } else {
            $files = get_transient('vehicle_sdk_import_library');
        }

        if ($files === false) {
            $files = $this->refresh();
            set_transient('vehicle_sdk_import_library', $files, 300);
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
    public function getData(string $file_path, string $delimiter = ',', int $page = 0, int $limit = 10, bool $header = true): array|false
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $file_handle = fopen($file_path, 'r');
        $file_data = [];

        if ($page > 0) {
            $page = max(1, $page);
            $limit = max(1, $limit);
            $current_row = 0;  // Keep track of the current row
            $start_row = ($page - 1) * $limit;  // The first row of the page

            if ($header) {
                // Skip the header row
                $file_header = fgetcsv($file_handle, 0, $delimiter);
                if ($file_header !== false) {
                    $file_data[] = $file_header;
                }
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

    /**
     * Retrieves the file path for a given file key.
     *
     * @param string $file The file key to retrieve the path for.
     * @return string|false The file path or false if it does not exist.
     */
    public function getPath(string $file): string|false
    {
        $files = $this->refresh();
        $file_path = $files[$file] ?? '';

        if (!file_exists($file_path)) {
            return false;
        }

        return $file_path;
    }

    /**
     * Retrieves information about the file's path.
     *
     * @param string $file The file key to retrieve information for.
     * @return array|false The path information or false if the file does not exist.
     */
    public function getPathInfo(string $file): array|false
    {
        $files = $this->refresh();
        $file_path = $files[$file] ?? '';
        if (!file_exists($file_path)) {
            echo '✘ File does not exist.';
            return false;
        }

        return pathinfo($file_path);
    }

    /**
     * Refreshes the file list by scanning the uploads directory for CSV, TSV, and JSON files.
     *
     * @return array The updated list of files.
     */
    public function refresh(): array
    {
        $files = [];
        $dirs = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ABSPATH . 'wp-content/uploads'));
        foreach ($dirs as $dir) {
            if ($dir->isDir()) {
                continue;
            }

            if (in_array($dir->getExtension(), ['csv', 'tsv', 'json'])) {
                // Store file in array with full path via md5 as key
                $files[md5($dir->getPathname())] = $dir->getPathname();
            }
        }

        return $files;
    }
}
