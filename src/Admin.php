<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

class Admin
{
    public function __construct()
    {
        new Admin\PageImport();
        new Admin\PageImportTools();

        add_filter('upload_mimes', [$this, 'allowUploadMimes']);
    }

    public function allowUploadMimes($mimes)
    {
        $mimes['csv'] = 'text/csv';
        $mimes['tsv'] = 'text/tab-separated-values';
        $mimes['json'] = 'application/json';
        $mimes['xml'] = 'application/xml';

        return $mimes;
    }
}
