<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

class Loader
{
    public function __construct()
    {
        $this->setupAdmin();
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
        (new Vehicle\Api\VehicleGet())->register();
        (new Vehicle\Api\VehicleGetFields())->register();
        (new Vehicle\Api\VehiclePost())->register();
        (new Vehicle\Api\VehiclePut())->register();
        (new Vehicle\Api\VehicleDelete())->register();
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
