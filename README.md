# CyberSource Laravel Package

A Laravel package for integrating with the CyberSource payment gateway, with a focus on Visa card processing.

## Installation

You can install the package via composer:

```bash
composer require asciisd/cybersource-laravel
```

After installing the package, publish the configuration file and migrations:

```bash
php artisan cybersource:install
```

This will publish the configuration file to `config/cybersource.php` and the migrations to your migrations directory.

Run the migrations:

```bash
php artisan migrate
```

## Configuration

Set your CyberSource API credentials in your `.env` file:

```
CYBERSOURCE_MERCHANT_ID=your-merchant-id
CYBERSOURCE_API_KEY_ID=your-api-key-id
CYBERSOURCE_SECRET_KEY=your-secret-key
CYBERSOURCE_ENVIRONMENT=apitest.cybersource.com  # Use api.cybersource.com for production
```

## Usage

### Process a Payment with Visa Card

```php
use Asciisd\CyberSource\Facades\CyberSource;

// For authorization only (no capture)
$transaction = CyberSource::authorize([
    'reference' => 'order-123',
    'amount' => '100.00',
    'currency' => 'USD',
    'card' => [
        'number' => '4111111111111111', // Visa card number
        'expiration_month' => '12',
        'expiration_year' => '2025',
        'security_code' => '123'
    ],
    'billing' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => '1 Market St',
        'city' => 'San Francisco',
        'state' => 'CA',
        'postal_code' => '94105',
        'country' => 'US',
        'email' => 'customer@example.com',
        'phone' => '4158880000'
    ]
]);

// Check if the transaction was successful
if ($transaction->isSuccessful()) {
    // Get the transaction ID
    $transactionId = $transaction->getId();
    
    // Get the transaction status
    $status = $transaction->getStatus();
    
    // Store the transaction ID for future operations
    // ...
}

// For authorization with immediate capture (sale)
$transaction = CyberSource::charge([
    'reference' => 'order-123',
    'amount' => '100.00',
    'currency' => 'USD',
    'card' => [
        'number' => '4111111111111111', // Visa card number
        'expiration_month' => '12',
        'expiration_year' => '2025',
        'security_code' => '123'
    ],
    'billing' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => '1 Market St',
        'city' => 'San Francisco',
        'state' => 'CA',
        'postal_code' => '94105',
        'country' => 'US',
        'email' => 'customer@example.com',
        'phone' => '4158880000'
    ]
]);
```

### Capture a Previously Authorized Payment

```php
$transaction = CyberSource::capture('transaction-id', 100.00, [
    'reference' => 'capture-123',
    'currency' => 'USD'
]);
```

### Void a Transaction

```php
$transaction = CyberSource::void('transaction-id', [
    'reference' => 'void-123'
]);
```

### Refund a Transaction

```php
$transaction = CyberSource::refund('transaction-id', 100.00, [
    'reference' => 'refund-123',
    'currency' => 'USD'
]);
```

### Retrieve a Transaction

```php
$transaction = CyberSource::retrieveTransaction('transaction-id');
```

## Error Handling

The package throws `Asciisd\CyberSource\Exceptions\CyberSourceException` when an error occurs. You can catch this exception to handle errors:

```php
use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Facades\CyberSource;

try {
    $transaction = CyberSource::charge([
        // Payment details...
    ]);
    
    // Process successful transaction
    
} catch (CyberSourceException $e) {
    // Handle the error
    $errorMessage = $e->getMessage();
    $errorCode = $e->getCode();
    $errorData = $e->getErrorData();
    
    // Log the error or display a message to the user
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
