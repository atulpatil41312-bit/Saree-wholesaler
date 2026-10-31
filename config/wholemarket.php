<?php

return [
    'billing' => [
        'provider' => env('WHOLEMARKET_BILLING_PROVIDER', 'manual'),
        'sandbox_enabled' => (bool) env('WHOLEMARKET_BILLING_SANDBOX_ENABLED', false),
    ],

    'sponsorship' => [
        'active_slots' => (int) env('WHOLEMARKET_PRO_RETAILER_SLOTS', 20),
        'invitation_days' => 7,
        'allowed_terms' => [30, 60, 90],
        'grace_days' => 7,
        'reminder_days' => [7, 3, 1],
    ],

    'plans' => [
        'basic' => [
            'name' => 'Basic',
            'shop_type' => 'wholesaler',
            'limits' => [
                'templates' => ['classic', 'vastra-emerald', 'royal-noir', 'wholesaler-templates', 'ivory-weaves'],
                'product_limit' => 30,
                'category_limit' => 5,
                'product_image_limit' => 2,
                'staff_limit' => 0,
                'sponsored_retailer_limit' => 0,
            ],
        ],
        'normal' => [
            'name' => 'Normal',
            'shop_type' => 'wholesaler',
            'limits' => [
                'templates' => 'all',
                'product_limit' => 200,
                'category_limit' => 15,
                'product_image_limit' => 4,
                'staff_limit' => 0,
                'sponsored_retailer_limit' => 0,
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'shop_type' => 'wholesaler',
            'limits' => [
                'templates' => 'all',
                'product_limit' => 1000,
                'category_limit' => 50,
                'product_image_limit' => 6,
                'staff_limit' => 2,
                'sponsored_retailer_limit' => (int) env('WHOLEMARKET_PRO_RETAILER_SLOTS', 20),
            ],
        ],
        'retailer-basic' => [
            'name' => 'Retailer Basic',
            'shop_type' => 'retailer',
            'limits' => [
                'templates' => ['classic', 'wholesaler-templates', 'royal-drape', 'silk-atelier', 'heritage-weaves', 'ivory-weaves'],
                'product_limit' => 20,
                'category_limit' => 5,
                'product_image_limit' => 2,
                'staff_limit' => 0,
                'sponsored_retailer_limit' => 0,
            ],
        ],
    ],
];
