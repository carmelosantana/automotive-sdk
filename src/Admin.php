<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

class Admin
{
    public function __construct()
    {
        // Load first to setup admin menu
        new Admin\PageDashboard();
        new Admin\PageImport();
        new Admin\PageImportTools();
        new Admin\PageExport();
        new Admin\PageOptions();

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
