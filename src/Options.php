<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk;

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
            'output' => [
                'section_title' => __('Output Settings', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'output_currency',
                        'type' => 'text',
                        'label' => __('Currency Symbol', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'output_currency_position',
                        'type' => 'select',
                        'label' => __('Currency Position', 'automotive-sdk'),
                        'options' => [
                            'before' => __('Before Price', 'automotive-sdk'),
                            'after' => __('After Price', 'automotive-sdk'),
                        ],
                    ],
                    [
                        'name' => 'juice_query_homepage',
                        'type' => 'post_select',
                        'label' => __('Juice Query Homepage', 'automotive-sdk'),
                        'description' => __('Select a page to display the Juice Query search form.', 'automotive-sdk'),
                        'post_type' => 'juiceq'
                    ],
                ],
            ],
            'cache' => [
                'section_title' => __('Cache Settings', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'cache_enabled',
                        'type' => 'checkbox',
                        'label' => __('Enable Cache', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'cache_duration_html',
                        'type' => 'number',
                        'label' => __('Cache Duration (in seconds)', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'cache_duration_api',
                        'type' => 'number',
                        'label' => __('Cache Duration for API (in seconds)', 'automotive-sdk'),
                    ],
                ],
            ],
            'license' => [
                'section_title' => __('License Settings', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'license_automotive_sdk',
                        'type' => 'text',
                        'label' => __('License Key', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'edd_license_activate',
                        'type' => 'submit',
                        'label' => __('Activate License', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'asdk_edd_nonce',
                        'type' => 'nonce',
                    ],
                ],
            ],
        ];
    }
}
