<?php

declare(strict_types=1);

namespace WpAutos\Vehicles;

class Vehicle
{
    public function __construct()
    {
        // Register Post Type
        new Vehicle\PostType();

        // Setup metas
        new Vehicle\Meta();

        // Setup search
        new Vehicle\Search();

        // Setup REST API
        new Api\VehiclesApi();
        new Api\VehiclesListApi();

        // Finally setup output rendering after everything is setup
        new Vehicle\Render();
    }
}
