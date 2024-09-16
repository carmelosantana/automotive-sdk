<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

class Loader
{
    public function __construct()
    {
        new Admin();
        new Render();
        new Vehicle();
    }
}
