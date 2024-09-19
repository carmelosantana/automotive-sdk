<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Blocks;

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */

class Register
{
	public function __construct()
	{
		add_action('init', [$this, 'register']);
	}

	public function register()
	{
		register_block_type(__DIR__ . '/build/vehicle-breadcrumbs');
		register_block_type(__DIR__ . '/build/vehicle-overview');
	}
}
