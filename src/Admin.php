<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk;

class Admin
{
    public function __construct()
    {
        // Load first to setup admin menu
        new Admin\PageDashboard();
        new Admin\PageImport();
        new Admin\PageExport();
        new Admin\PageMarketplace();
        new Admin\PageOptions();

        // Filters
        add_filter('upload_mimes', [$this, 'allowUploadMimes']);
    }

    public function allowUploadMimes($mimes)
    {
        $mimes['csv'] = 'text/csv';
        return $mimes;
    }
}
