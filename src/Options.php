<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

class Options
{
    public static function get(): array
    {
        return [
            'dealer' => [
                'section_title' => __('Dealer Settings', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'dealer_name',
                        'type' => 'text',
                        'label' => __('Dealer Name', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_address',
                        'type' => 'textarea',
                        'label' => __('Dealer Address', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_city',
                        'type' => 'text',
                        'label' => __('Dealer City', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_state',
                        'type' => 'text',
                        'label' => __('Dealer State', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_zip',
                        'type' => 'text',
                        'label' => __('Dealer Zip', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_phone',
                        'type' => 'text',
                        'label' => __('Dealer Phone', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_email',
                        'type' => 'text',
                        'description' => __('Email address for dealer contact form.', 'automotive-sdk'),
                        'label' => __('Dealer Email', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_description',
                        'type' => 'textarea',
                        'label' => __('Dealer Description', 'automotive-sdk'),
                    ],
                ],
            ],
            'legal' => [
                'section_title' => __('Disclaimers for new and used vehicles.', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'disclaimer_default',
                        'type' => 'textarea',
                        'label' => __('Default disclaimer on any non vehicle specific page.', 'automotive-sdk'),
                    ],                    
                    [
                        'name' => 'disclaimer_new',
                        'type' => 'textarea',
                        'label' => __('Disclaimer for new vehicles.', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'disclaimer_used',
                        'type' => 'textarea',
                        'label' => __('Disclaimer for used vehicles.', 'automotive-sdk'),
                    ],
                ],
            ],
            'license' => [
                'section_title' => __('License Settings', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'license_key',
                        'type' => 'text',
                        'label' => __('License Key', 'automotive-sdk'),
                    ],
                ],
            ],
        ];
    }
}
