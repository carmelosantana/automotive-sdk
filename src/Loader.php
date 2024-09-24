<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

class Loader
{
    public function __construct()
    {
        $this->addFilters();
        $this->setupAdmin();
        $this->setupBlocks();
        $this->setupImportProfile();
        $this->setupRender();
        $this->setupVehicle();
        $this->setupApi();
    }

    public function addFilters()
    {
        add_filter('upload_mimes', [$this, 'allowUploadMimes']);
    }

    public function allowUploadMimes($mimes)
    {
        $mimes['csv'] = 'text/csv';
        return $mimes;
    }

    public function setupAdmin()
    {
        new Admin\PageDashboard();
        new Admin\PageImport();
        new Admin\PageExport();
        new Admin\PageOptions();
    }

    public function setupApi()
    {
        new Api\Vehicles\VehicleApi();
    }

    public function setupBlocks()
    {
        new Blocks\Register();
    }

    public function setupImportProfile()
    {
        new ImportProfile\PostType();
        new ImportProfile\Meta();
    }

    public function setupRender()
    {
        new Render();
    }

    public function setupVehicle()
    {
        new Vehicle\PostType();
        new Vehicle\Data();
        new Vehicle\Meta();
        new Vehicle\Search();
    }
}
