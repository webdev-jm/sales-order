<?php

return [

    'enable_parts' => false,

    'custom_limits' => [
        '1200023' => 23 // WATSON
    ],

    'separate_products' => [
        'groups' => [
            'cristalino' => [
                'stock_codes' => [
                    'CSP10001', 'CSP10002', 'CSP10003'
                ],
                'accounts' => []
            ],
            'twin-pack' => [
                'stock_codes' => [
                    'KS01046', 'KS01047'
                ],
                'accounts' => []
            ],
            'mdc-custom' => [
                'stock_codes' => [
                    'KS01058',
                    'KS01059',
                    'KS01060',
                    'KS01061',
                    'DF01015',
                    'DF01016',
                    'DF01017',
                    'DF01018',
                    'DF01019',
                ],
                'accounts' => [
                    '1200008' // MERCURY
                ]
            ],
        ]
    ],

    'custom_account_products' => [
        '1200008' => [ // MERCURY
            'KS01058',
            'KS01059',
            'KS01060',
            'KS01061',
            'DF01015',
            'DF01016',
            'DF01017',
            'DF01018',
            'DF01019',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Product IDs
    |--------------------------------------------------------------------------
    |
    | Product IDs exposed through the external sales-order API endpoints.
    | Update these when the product catalogue changes rather than editing
    | controller code.
    |
    */
    'api_product_ids' => [228, 229, 230],

    /*
    |--------------------------------------------------------------------------
    | XML Generation Toggle
    |--------------------------------------------------------------------------
    |
    | Set XML_GENERATION_ENABLED=false in .env to prevent XML files from being
    | generated and uploaded to FTP when a sales order is finalized. Useful for
    | local development and staging environments. Defaults to true.
    |
    */
    'xml_generation_enabled' => env('XML_GENERATION_ENABLED', true),

];
