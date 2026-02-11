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
                    'KS99079',
                    'KS99080',
                    'KS99081',
                    'KS99082',
                    'KS99078',
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
    ]
];
