<?php

declare(strict_types=1);

use WpAutos\AutomotiveSdk\Vehicle\Data as VehicleData;

/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<p <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	// get current vehicle by post id
	$vehicle = new VehicleData();
	$vehicle = $vehicle->getVehicleById(get_the_ID());

	// Honda > Odyssey > Elite > VIN: 5FNRL6H97SB005995
	$default_attributes = [
		'pre' => '',
		'post' => ' > ',
	];
	$vehicle_hierarchy = [
		'make' => [
			'attribute' => 'make',
		],
		'model' => [
			'attribute' => 'model',
		],
		'trim' => [
			'attribute' => 'trim',
		],
		'vin' => [
			'attribute' => 'vin',
			'pre' => 'VIN: ',
			'post' => '',
		],
	];

	foreach ($vehicle_hierarchy as $key => $value) {
		$attribute = $value['attribute'];
		$pre = $value['pre'] ?? $default_attributes['pre'];
		$post = $value['post'] ?? $default_attributes['post'];

		if (isset($vehicle[$attribute]) and !empty($vehicle[$attribute])) {
			$vehicle_hierarchy[$key] = $pre . $vehicle[$attribute] . $post;
		} else {
			$vehicle_hierarchy[$key] = '';
		}
	}

	$out = implode($vehicle_hierarchy);

	$out = rtrim($out, ' >');

	echo trim($out);
	?>
</p>