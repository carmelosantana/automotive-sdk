<?php

declare(strict_types=1);

use WpAutos\AutomotiveSdk\Vehicle\Data as VehicleData;

/**
 * Output the 2 Column Details block.
 */

$vehicle = new VehicleData();
$vehicle = $vehicle->getVehicleById(get_the_ID());

// Full list of vehicle attributes
$vehicle_details = [
	'Exterior' => [
		'attribute' => 'exterior_color',
		'label' => 'Exterior: ',
		'icon' => 'fas fa-droplet',
	],
	'Interior' => [
		'attribute' => 'interior_color',
		'label' => 'Interior: ',
		'icon' => 'fas fa-fill',
	],
	'Mileage' => [
		'attribute' => 'mileage',
		'label' => 'Mileage: ',
		'suffix' => ' miles',
		'icon' => 'fas fa-gauge',
	],
	'Fuel Type' => [
		'attribute' => 'fuel_type',
		'label' => 'Fuel Type: ',
		'icon' => 'fas fa-gas-pump',
	],
	'Fuel Efficiency' => [
		'attribute' => 'fuel_efficiency',
		'label' => 'Fuel Efficiency: ',
		'icon' => 'fas fa-road-circle-check',
	],
	'Transmission' => [
		'attribute' => 'transmission',
		'label' => 'Transmission: ',
		'icon' => 'fas fa-cogs',
	],
	'Drivetrain' => [
		'attribute' => 'drivetrain',
		'label' => 'Drivetrain: ',
		'icon' => 'fas fa-gear',
	],
	'Engine' => [
		'attribute' => 'engine',
		'label' => 'Engine: ',
		'icon' => 'fas fa-oil-can',
	],
	'Location' => [
		'attribute' => 'location',
		'label' => 'Location: ',
		'icon' => 'fas fa-location-dot',
	],
	'Listed' => [
		'attribute' => 'days_listed',
		'label' => 'Listed: ',
		'suffix' => ' days ago',
		'icon' => 'fas fa-calendar-alt',
	],
	'VIN' => [
		'attribute' => 'vin',
		'label' => 'VIN: ',
		'icon' => 'fas fa-car',
	],
	'Stock Number' => [
		'attribute' => 'stock_number',
		'label' => 'Stock Number: ',
		'icon' => 'fas fa-hashtag',
	]
];

echo '<div class="wp-block-columns">';

$left_column = '';
$right_column = '';
$index = 0;

foreach ($vehicle_details as $key => $detail) {
	$attribute = $detail['attribute'];
	$label = $detail['label'] ?? '';
	$suffix = $detail['suffix'] ?? '';
	$icon = $detail['icon'] ?? '';

	// Check if the attribute exists and is not empty
	if (isset($vehicle[$attribute]) && !empty($vehicle[$attribute])) {
		$value = $label . esc_html($vehicle[$attribute]) . $suffix;

		$list_item = '<li><i class="' . esc_attr($icon) . '" aria-hidden="true"></i>' . $value . '</li>';

		// Alternate between left and right columns
		if ($index % 2 === 0) {
			$left_column .= $list_item;
		} else {
			$right_column .= $list_item;
		}

		$index++;
	}
}

if ($left_column) {
	echo '<div class="wp-block-column"><ul class="wp-block-list">' . $left_column . '</ul></div>';
}
if ($right_column) {
	echo '<div class="wp-block-column"><ul class="wp-block-list">' . $right_column . '</ul></div>';
}

echo '</div>';
