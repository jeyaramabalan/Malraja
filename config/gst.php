<?php

return [
    /*
    | Minimum invoice value (₹) for e-way bill eligibility (Part A prep).
    */
    'eway_min_value' => 50000,

    /*
    | Fallback when company GSTIN is not in settings / config.
    */
    'company_gstin' => env('GST_COMPANY_GSTIN', '33CZMPK0759B1ZJ'),

    'company_name' => env('GST_COMPANY_NAME', 'Malraja Traders'),

    'company_address' => env('GST_COMPANY_ADDRESS', ''),

    'company_phone' => env('GST_COMPANY_PHONE', ''),

    /*
    | Line amount field is GST-inclusive (matches POS / bill totals).
    */
    'amount_includes_gst' => true,

    'doc_types' => [
        'delivery' => 'Delivery order',
        'retail' => 'Retail sale',
        'purchase' => 'Purchase',
    ],

    'einvoice_doc_types' => ['delivery', 'retail'],
];
