<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CyberSource API Credentials
    |--------------------------------------------------------------------------
    |
    | Here you may configure your CyberSource API credentials. These are used
    | to authenticate with the CyberSource API. You can find these credentials
    | in your CyberSource Business Center account.
    |
    */
    'merchant_id' => env('CYBERSOURCE_MERCHANT_ID', ''),
    'api_key_id' => env('CYBERSOURCE_API_KEY_ID', ''),
    'secret_key' => env('CYBERSOURCE_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | CyberSource Environment
    |--------------------------------------------------------------------------
    |
    | This value determines which CyberSource environment you are connecting to.
    | Possible values are:
    | - apitest.cybersource.com (Sandbox/Test)
    | - api.cybersource.com (Production)
    |
    */
    'environment' => env('CYBERSOURCE_ENVIRONMENT', 'apitest.cybersource.com'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Type
    |--------------------------------------------------------------------------
    |
    | The authentication type to use for API requests.
    | Supported values: 'http_signature', 'jwt'
    |
    */
    'auth_type' => env('CYBERSOURCE_AUTH_TYPE', 'http_signature'),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When debug mode is enabled, detailed error messages and request/response
    | information will be logged.
    |
    */
    'debug' => env('CYBERSOURCE_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for CyberSource.
    |
    */
    'log_file' => storage_path('logs/cybersource_debug.log'),
    'error_log_file' => storage_path('logs/cybersource_error.log'),
    'log_filename' => 'cybersource',
    'log_size' => '1048576', // 1MB

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency that will be used for payments when no
    | currency is specified.
    |
    */
    'currency' => env('CYBERSOURCE_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the database settings for storing transaction
    | information.
    |
    */
    'database' => [
        'enabled' => env('CYBERSOURCE_DB_ENABLED', true),
        'table' => 'cybersource_transactions',
    ],
];
