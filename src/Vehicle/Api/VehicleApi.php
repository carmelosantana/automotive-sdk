<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle\Api;

class VehicleApi
{
    public function __construct()
    {
        new VehicleGet();
        new VehicleGetFields();
        new VehicleGetList();
        new VehiclePost();
        new VehiclePut();
        new VehicleDelete();
    }
}
