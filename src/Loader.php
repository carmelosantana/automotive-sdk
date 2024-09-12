<?php

declare(strict_types=1);

namespace WpAutos\Vehicles;

use WpAutos\Vehicles\Vehicle;

const VERSION = '0.1.0';

class Loader
{
    public function __construct()
    {
        new Import\Admin();
        new Vehicle();
    }
}
