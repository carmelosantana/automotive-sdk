<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

class Loader
{
    public function __construct()
    {
        $this->setupAdmin();
        $this->setupBlocks();
        $this->setupImportProfile();
        $this->setupRender();
        $this->setupVehicle();
        $this->setupVehicleApi();
    }

    public function setupAdmin()
    {
        new Admin();
    }

    public function setupVehicleApi()
    {
        new Vehicle\Api\VehicleGet();
        new Vehicle\Api\VehicleGetFields();
        new Vehicle\Api\VehiclePost();
        new Vehicle\Api\VehiclePut();
        new Vehicle\Api\VehicleDelete();
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
