<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle;

class Fields
{
    /**
     * Get a structured array of meta fields in sections.
     * Used for registering and referencing meta fields for the vehicle post type.
     *
     * @return array
     */
    public static function getMetas()
    {
        return [
            'specifications' => [
                'description' => __('Details for this particular vehicle.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'vin',
                        'type' => 'text',
                        'label' => 'VIN'
                    ],
                    [
                        'name' => 'stock_number',
                        'type' => 'text',
                        'label' => 'Stock Number'
                    ],
                    [
                        'name' => 'mileage',
                        'type' => 'number',
                        'label' => 'Mileage'
                    ],
                    [
                        'name' => 'transmission',
                        'type' => 'text',
                        'label' => 'Transmission'
                    ],
                    [
                        'name' => 'engine',
                        'type' => 'text',
                        'label' => 'Engine'
                    ],
                    [
                        'name' => 'engine_cylinders',
                        'type' => 'text',
                        'label' => 'Engine Cylinders'
                    ],
                    [
                        'name' => 'engine_displacement',
                        'type' => 'text',
                        'label' => 'Engine Displacement'
                    ],
                    [
                        'name' => 'fuel_type',
                        'type' => 'text',
                        'label' => 'Fuel Type'
                    ],
                    [
                        'name' => 'drive_train',
                        'type' => 'text',
                        'label' => 'Drive Train'
                    ],
                    [
                        'name' => 'doors',
                        'type' => 'number',
                        'label' => 'Doors'
                    ],
                    [
                        'name' => 'exterior_color',
                        'type' => 'text',
                        'label' => 'Exterior Color'
                    ],
                    [
                        'name' => 'interior_color',
                        'type' => 'text',
                        'label' => 'Interior Color'
                    ],
                ],
            ],
            'price' => [
                'description' => __('Pricing, MSRP, financing.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'internet_price',
                        'type' => 'number',
                        'label' => 'Internet Price'
                    ],
                    [
                        'name' => 'invoice',
                        'type' => 'number',
                        'label' => 'Invoice'
                    ],
                    [
                        'name' => 'price',
                        'type' => 'number',
                        'label' => 'Price'
                    ],
                    [
                        'name' => 'sale_price',
                        'type' => 'number',
                        'label' => 'Sale Price'
                    ],
                    [
                        'name' => 'msrp',
                        'type' => 'number',
                        'label' => 'MSRP'
                    ],
                    [
                        'name' => 'lease_payment',
                        'type' => 'number',
                        'label' => 'Lease Payment'
                    ],
                ],
            ],
            'media' => [
                'description' => __('Images, videos, and other media.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'photo_urls',
                        'type' => 'textarea',
                        'label' => 'Photo URLs',
                        'data_type' => 'array'
                    ],
                    [
                        'name' => 'video_url',
                        'type' => 'text',
                        'label' => 'Video URL'
                    ],
                ],
            ],
            'rooftop' => [
                'description' => __('Dealership location details, URL, and contact information.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'dealer_name',
                        'type' => 'text',
                        'label' => 'Dealer Name'
                    ],
                    [
                        'name' => 'dealer_address',
                        'type' => 'text',
                        'label' => 'Dealer Address'
                    ],
                    [
                        'name' => 'dealer_city',
                        'type' => 'text',
                        'label' => 'Dealer City'
                    ],
                    [
                        'name' => 'dealer_state',
                        'type' => 'text',
                        'label' => 'Dealer State'
                    ],
                    [
                        'name' => 'dealer_zip',
                        'type' => 'text',
                        'label' => 'Dealer Zip'
                    ],
                    [
                        'name' => 'listing_url',
                        'type' => 'text',
                        'label' => 'Listing URL'
                    ],
                ],
            ],
            'warranty' => [
                'description' => __('Warranty information for the vehicle.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'certified',
                        'type' => 'text',
                        'label' => 'Certified'
                    ],
                    [
                        'name' => 'certification_warranty',
                        'type' => 'text',
                        'label' => 'Certification Warranty'
                    ],
                    [
                        'name' => 'warranty_month',
                        'type' => 'number',
                        'label' => 'Warranty Month'
                    ],
                    [
                        'name' => 'warranty_miles',
                        'type' => 'number',
                        'label' => 'Warranty Miles'
                    ],
                ],
            ],
            'additional_info' => [
                'description' => __('Additional information about the vehicle.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'internet_special',
                        'type' => 'text',
                        'label' => 'Internet Special'
                    ],
                    [
                        'name' => 'book_value',
                        'type' => 'number',
                        'label' => 'Book Value'
                    ],
                    [
                        'name' => 'description',
                        'type' => 'textarea',
                        'label' => 'Description'
                    ],
                    [
                        'name' => 'options',
                        'type' => 'textarea',
                        'label' => 'Options',
                        'data_type' => 'array'
                    ],
                    [
                        'name' => 'fuel_economy_city',
                        'type' => 'number',
                        'label' => 'Fuel Economy City'
                    ],
                    [
                        'name' => 'fuel_economy_highway',
                        'type' => 'number',
                        'label' => 'Fuel Economy Highway'
                    ],
                    [
                        'name' => 'vehicle_status',
                        'type' => 'text',
                        'label' => 'Vehicle Status'
                    ],
                    [
                        'name' => 'stock_type',
                        'type' => 'text',
                        'label' => 'Stock Type'
                    ],
                    [
                        'name' => 'vehicle_condition',
                        'type' => 'text',
                        'label' => 'Vehicle Condition'
                    ],
                    [
                        'name' => 'carfax_one_owner',
                        'type' => 'text',
                        'label' => 'Carfax One Owner'
                    ],
                    [
                        'name' => 'carfax_available',
                        'type' => 'text',
                        'label' => 'Carfax Available'
                    ],
                ],
            ],
        ];
    }

    public static function getMetasFlat(): array
    {
        $fields = self::getMetas();
        $flat_fields = [];
        foreach ($fields as $section) {
            $flat_fields = array_merge($flat_fields, $section['fields']);
        }
        return $flat_fields;
    }

    /**
     * Get a structured array of taxonomies.
     * Used for registering and referencing taxonomies for the vehicle post type.
     *
     * @return array
     */
    public static function getTaxonomies(): array
    {
        return [
            [
                'name' => 'year',
                'slug' => 'years',
                'label' => 'Year',
                'hierarchical' => false,
            ],
            [
                'name' => 'make',
                'slug' => 'makes',
                'label' => 'Make',
                'hierarchical' => false,
            ],
            [
                'name' => 'model',
                'slug' => 'models',
                'label' => 'Model',
                'hierarchical' => false,
            ],
            [
                'name' => 'trim',
                'slug' => 'trims',
                'label' => 'Trim',
                'hierarchical' => false,
            ],
            [
                'name' => 'body',
                'slug' => 'bodies',
                'label' => 'Body',
                'hierarchical' => false,
            ],
        ];
    }

    /**
     * Get the names of the taxonomies.
     *
     * @return array
     */
    public static function getTaxonomiesNames(): array
    {
        return array_map(function ($taxonomy) {
            return $taxonomy['name'];
        }, self::getTaxonomies());
    }
}
