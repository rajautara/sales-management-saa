<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MyInvois (LHDN e-Invoice) Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for submitting e-Invoices to the Malaysian Inland Revenue
    | Board (LHDN) MyInvois system. Credentials are obtained from the MyInvois
    | Portal (client id/secret) plus a valid X.509 digital signing certificate.
    |
    */

    'enabled' => (bool) env('MYINVOIS_ENABLED', false),

    // 'sandbox' (preprod) or 'production'.
    'environment' => env('MYINVOIS_ENVIRONMENT', 'sandbox'),

    'urls' => [
        'sandbox' => [
            'identity' => 'https://preprod-api.myinvois.hasil.gov.my',
            'api' => 'https://preprod-api.myinvois.hasil.gov.my',
            'portal' => 'https://preprod.myinvois.hasil.gov.my',
        ],
        'production' => [
            'identity' => 'https://api.myinvois.hasil.gov.my',
            'api' => 'https://api.myinvois.hasil.gov.my',
            'portal' => 'https://myinvois.hasil.gov.my',
        ],
    ],

    'client_id' => env('MYINVOIS_CLIENT_ID'),
    'client_secret' => env('MYINVOIS_CLIENT_SECRET'),

    // Path to the X.509 signing certificate (.p12/.pfx) and its password.
    'cert_path' => env('MYINVOIS_CERT_PATH'),
    'cert_password' => env('MYINVOIS_CERT_PASSWORD'),

    // HTTP timeout (seconds) for API calls.
    'timeout' => (int) env('MYINVOIS_TIMEOUT', 30),

    // Default codes used when a product/customer has none configured.
    'defaults' => [
        'classification_code' => '022', // Others
        'uom_code' => 'UNT',            // Unit
        'tax_type' => '06',             // Not Applicable
        'country_code' => 'MYS',
    ],

];
