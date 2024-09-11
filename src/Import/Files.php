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

    public function getData($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $file_handle = fopen($file_path, 'r');

        $file_data = [];

        while (($data = fgetcsv($file_handle)) !== false) {
            $file_data[] = $data;
        }

        // header row, md5 hash of header row
        $this->file_header = $file_data[0];
        $this->file_header_hash = md5(implode(',', $this->file_header));

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
