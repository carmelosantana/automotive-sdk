<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

use WpAutos\AutomotiveSdk\Import\Mapping;

/**
 * Handles the loading and processing of file data including headers and templates.
 */
class File
{
    /**
     * @var Files Instance of Files for file operations
     */
    private Files $Files;

    /**
     * @var array|null Data from the file
     */
    private ?array $file_data = null;

    /**
     * @var array|null Header data from the file
     */
    private ?array $file_header = null;

    /**
     * @var string|null Path of the loaded file
     */
    private ?string $file_path = null;

    /**
     * @var string|null Hash of the file header
     */
    private ?string $file_header_hash = null;

    /**
     * @var array|null Template data associated with the file
     */
    private ?array $template = null;

    /**
     * File constructor.
     *
     * @param string $file The file path or key to load
     */
    public function __construct()
    {
        $this->Files = new Files();
    }

    /**
     * Generates a hash for the given header array.
     *
     * @param array $header The header array to hash
     * @return string The md5 hash of the serialized header
     */
    public function generateHeaderHash(array $header): string
    {
        return md5(serialize($header));
    }

    /**
     * Gets the file data.
     *
     * @return array|null The data from the file
     */
    public function getData(): ?array
    {
        return $this->file_data;
    }

    /**
     * Gets the file header.
     *
     * @return array|null The header from the file
     */
    public function getHeader(): ?array
    {
        return $this->file_header;
    }

    /**
     * Gets the file header hash.
     *
     * @return string|null The hash of the file header
     */
    public function getHeaderHash(): ?string
    {
        return $this->file_header_hash;
    }

    /**
     * Gets the template associated with the file.
     *
     * @return array|null The template data
     */
    public function getTemplate(): ?array
    {
        return $this->template;
    }

    /**
     * Gets the file path.
     *
     * @return string|null The path of the loaded file
     */
    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    /**
     * Gets the file name.
     *
     * @return string|null The name of the file
     */
    public function getFileName(): ?string
    {
        return $this->file_path ? basename($this->file_path) : null;
    }

    /**
     * Gets the file size.
     *
     * @return string|null The formatted size of the file
     */
    public function getFileSize(): ?int
    {
        return $this->file_path ? filesize($this->file_path) : null;
    }

    /**
     * Gets the file modification date.
     *
     * @return string|null The formatted modification date of the file
     */
    public function getFileModificationDate(): ?int
    {
        return $this->file_path ? filemtime($this->file_path) : null;
    }

    /**
     * Gets the total number of rows in the file.
     *
     * @return int|null The total rows in the file
     */
    public function getTotalRows(): ?int
    {
        // Subtract 1 to account for the header row
        return $this->file_data ? count($this->file_data) - 1 : null;
    }

    /**
     * Gets the total number of columns in the file header.
     *
     * @return int|null The total columns in the file header
     */
    public function getTotalColumns(): ?int
    {
        return $this->file_header ? count($this->file_header) : null;
    }

    /**
     * Gets the full file information including data, header, header hash, template, file path, file size, modification date, total rows, and total columns.
     *
     * @return array The full file information
     */
    public function get(): array
    {
        return [
            'data' => $this->getData(),
            'header' => $this->getHeader(),
            'header_hash' => $this->getHeaderHash(),
            'template' => $this->getTemplate(),
            'file_path' => $this->getFilePath(),
            'file_name' => $this->getFileName(),
            'file_size' => $this->getFileSize(),
            'file_modification_date' => $this->getFileModificationDate(),
            'pathinfo' => pathinfo($this->getFilePath()),
            'total_rows' => $this->getTotalRows(),
            'total_columns' => $this->getTotalColumns(),
        ];
    }

    public function isLoaded(): bool
    {
        return !empty($this->file_data);
    }

    /**
     * Loads file data from either a key or a file path.
     *
     * @param string $key The key or file path to load
     */
    public function load(string $key): void
    {
        if ($this->Files->exists($key)) {
            $this->loadFile($key);
        } else {
            $this->loadFileFromPath($key);
        }
    }

    /**
     * Loads file data from a key.
     *
     * @param string $key The key to load the file from
     */
    public function loadFile(string $key): void
    {
        $file_path = $this->Files->getPath($key);
        if ($file_path) {
            $this->loadFileFromPath($file_path);
            $this->loadTemplate($this->file_header_hash);
        }
    }

    /**
     * Loads file data from a file path.
     *
     * @param string $file_path The file path to load
     */
    public function loadFileFromPath(string $file_path): void
    {
        $this->file_path = $file_path;
        $file_data = $this->Files->getData($file_path);
        $this->file_data = $file_data;
        $this->file_header = $file_data[0] ?? [];
        $this->file_header_hash = $this->generateHeaderHash($this->file_header);
    }

    /**
     * Loads the template data based on a key.
     *
     * @param string $key The key to retrieve the template
     */
    public function loadTemplate(string $key): void
    {
        $this->template = (new Mapping())->get($key);
    }
}
