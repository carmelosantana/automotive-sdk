<?php

declare(strict_types=1);

namespace WpAutos\VehiclesSdk;

class Loader
{
    public function __construct()
    {
        new Admin();
        new Render();
        new Vehicle();
    }
}
