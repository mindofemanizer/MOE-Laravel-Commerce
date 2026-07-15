<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode: single_store | multi_store
    |--------------------------------------------------------------------------
    |
    | single_store — Toko online tunggal (tanpa model Store)
    | multi_store  — Marketplace multi penjual
    |
    */

    'mode' => env('COMMERCE_MODE', 'multi_store'),

    /*
    |--------------------------------------------------------------------------
    | Model Bindings
    |--------------------------------------------------------------------------
    */

    'models' => [

        'user' => App\Models\User::class,

        'inventory' => App\Models\Inventory::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */

    'tables' => [

        'stores' => 'commerce_stores',

        'products' => 'commerce_products',

        'categories' => 'commerce_categories',

        'sub_categories' => 'commerce_sub_categories',

        'carts' => 'commerce_carts',

        'cart_items' => 'commerce_cart_items',

        'orders' => 'commerce_orders',

        'order_items' => 'commerce_order_items',

        'reviews' => 'commerce_reviews',

        'wishlists' => 'commerce_wishlists',

        'product_images' => 'commerce_product_images',

        'product_variants' => 'commerce_product_variants',

        'invoices' => 'commerce_invoices',

    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Fee
    |--------------------------------------------------------------------------
    */

    'default_fee_rate' => env('COMMERCE_FEE_RATE', '10'),

    /*
    |--------------------------------------------------------------------------
    | Order Number Format
    |--------------------------------------------------------------------------
    */

    'order_number_format' => 'ORD-{Ymd}-{RAND:6}',

    /*
    |--------------------------------------------------------------------------
    | Checkout Settings
    |--------------------------------------------------------------------------
    */

    'checkout' => [

        'split_by_store' => true,

        'allow_guest_checkout' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */

    'invoice' => [

        'due_days' => env('INVOICE_DUE_DAYS', 14),

        'auto_generate' => env('INVOICE_AUTO_GENERATE', true),

        'prefix' => [
            'invoice' => 'INV',
            'credit_note' => 'CN',
            'debit_note' => 'DN',
            'proforma' => 'PRO',
            'tax' => 'TAX',
        ],

    ],

];
