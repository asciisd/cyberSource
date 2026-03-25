---
name: cybersource-payments
description: Process payments, authorizations, captures, voids, and refunds using the CyberSource Laravel package.
---

# CyberSource Payments

## When to use this skill

Use this skill when working with CyberSource payment processing in a Laravel application — creating payment flows, handling authorizations, captures, voids, refunds, or integrating the `CyberSource` facade into controllers and services.

## Package overview

`asciisd/cybersource-laravel` wraps the official CyberSource REST SDK (`cybersource/rest-client-php`) behind a Laravel facade. All operations return an `Asciisd\CyberSource\Models\Transaction` DTO and throw `CyberSourceException` on failure.

## Setup

```bash
composer require asciisd/cybersource-laravel
php artisan cybersource:install   # publishes config + migration
php artisan migrate
```

Required `.env` keys:

```
CYBERSOURCE_MERCHANT_ID=your-merchant-id
CYBERSOURCE_API_KEY_ID=your-api-key-id
CYBERSOURCE_SECRET_KEY=your-secret-key
CYBERSOURCE_ENVIRONMENT=apitest.cybersource.com
```

Use `api.cybersource.com` for production.

## Charging a card (authorize + capture)

```php
use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Facades\CyberSource;

try {
    $transaction = CyberSource::charge([
        'reference' => 'order-' . $order->id,
        'amount'    => '100.00',
        'currency'  => 'USD',
        'card'      => [
            'number'           => '4111111111111111',
            'expiration_month' => '12',
            'expiration_year'  => '2025',
            'security_code'    => '123',
        ],
        'billing'   => [
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'address'     => '1 Market St',
            'city'        => 'San Francisco',
            'state'       => 'CA',
            'postal_code' => '94105',
            'country'     => 'US',
            'email'       => 'john@example.com',
            'phone'       => '4158880000',
        ],
    ]);

    if ($transaction->isSuccessful()) {
        $transactionId = $transaction->getId();
        $status = $transaction->getStatus(); // e.g. "AUTHORIZED"
    }
} catch (CyberSourceException $e) {
    Log::error('CyberSource error', [
        'message'    => $e->getMessage(),
        'code'       => $e->getCode(),
        'error_data' => $e->getErrorData(),
    ]);
}
```

## Authorize only (no capture)

Use `authorize` when you want to hold funds and capture later:

```php
$transaction = CyberSource::authorize([
    'reference' => 'order-' . $order->id,
    'amount'    => '250.00',
    'currency'  => 'USD',
    'card'      => [ /* ... */ ],
    'billing'   => [ /* ... */ ],
]);

// Store $transaction->getId() for later capture
```

## Capture a previous authorization

```php
$transaction = CyberSource::capture($transactionId, 250.00, [
    'reference' => 'capture-' . $order->id,
    'currency'  => 'USD',
]);
```

The second argument is the capture amount. Pass `null` to capture the full authorization amount.

## Void a transaction

```php
$transaction = CyberSource::void($transactionId, [
    'reference' => 'void-' . $order->id,
]);
```

## Refund a transaction

```php
$transaction = CyberSource::refund($transactionId, 50.00, [
    'reference' => 'refund-' . $order->id,
    'currency'  => 'USD',
]);
```

The second argument is the refund amount (supports partial refunds). Pass `null` for a full refund.

## Retrieve transaction details

```php
$transaction = CyberSource::retrieveTransaction($transactionId);

$status   = $transaction->getStatus();
$amount   = $transaction->getAmount();
$currency = $transaction->getCurrency();
$raw      = $transaction->getRawResponse();
```

## Transaction DTO methods

The `Transaction` object is a plain DTO (not Eloquent). Available methods:

| Method | Returns |
|--------|---------|
| `getId()` | CyberSource transaction ID |
| `getStatus()` | Status string (e.g. `AUTHORIZED`, `PENDING`, `DECLINED`) |
| `isSuccessful()` | `true` if status is `AUTHORIZED`, `PENDING`, `TRANSMITTED`, `COMPLETED`, or `SETTLED` |
| `isAuthorized()` | `true` if `AUTHORIZED` |
| `isPending()` | `true` if `PENDING` |
| `isDeclined()` | `true` if `DECLINED` |
| `getAmount()` | Total amount |
| `getCurrency()` | ISO 4217 currency code |
| `getReference()` | Client reference code |
| `getResponseCode()` | Processor response code |
| `getApprovalCode()` | Processor approval code |
| `getReconciliationId()` | Reconciliation ID |
| `getCaptureUrl()` | HATEOAS capture link |
| `getVoidUrl()` | HATEOAS void link |
| `getRefundUrl()` | HATEOAS refund link |
| `getRawResponse()` | Full raw API response array |
| `getAttribute($key, $default)` | Access any response key |
| `toArray()` | All transaction data as array |

## Options array shape reference

```php
[
    'reference' => string,       // Client reference code (auto-generated if omitted)
    'amount'    => string,       // e.g. '100.00'
    'currency'  => string,       // ISO 4217, defaults to 'USD'
    'card'      => [
        'number'           => string,
        'expiration_month' => string,  // '01'-'12'
        'expiration_year'  => string,  // e.g. '2026'
        'security_code'    => ?string, // CVV, optional
    ],
    'billing'   => [
        'first_name'  => string,
        'last_name'   => string,
        'address'     => string,
        'city'        => string,
        'state'       => string,
        'postal_code' => string,
        'country'     => string,  // ISO 3166-1 alpha-2
        'email'       => string,
        'phone'       => string,
    ],
]
```

## Error handling

All facade methods throw `CyberSourceException` on API or network errors:

```php
use Asciisd\CyberSource\Exceptions\CyberSourceException;

try {
    $transaction = CyberSource::charge([ /* ... */ ]);
} catch (CyberSourceException $e) {
    $e->getMessage();   // Human-readable error
    $e->getCode();      // HTTP status or SDK error code
    $e->getErrorData(); // Additional error data array (may be null)
}
```

## Important notes

- **No automatic database persistence.** The package provides a migration for `cybersource_transactions` but never writes to it. Your application must persist transaction records.
- **No routes or controllers.** The package only provides the facade and service classes. Define your own routes and controllers.
- **Always use Form Request validation** for payment inputs before passing to the facade.
- **Never log full card numbers.** Only store the last four digits for reference.
- **Sandbox vs production** is controlled entirely by `CYBERSOURCE_ENVIRONMENT`.
- **Debug logging** can be enabled with `CYBERSOURCE_DEBUG=true`; logs go to `storage/logs/cybersource_debug.log`.

## Controller example pattern

```php
use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Facades\CyberSource;

class PaymentController extends Controller
{
    public function store(PaymentRequest $request)
    {
        try {
            $transaction = CyberSource::charge([
                'reference' => 'order-' . $order->id,
                'amount'    => $request->validated('amount'),
                'currency'  => 'USD',
                'card'      => [
                    'number'           => $request->validated('card_number'),
                    'expiration_month' => $request->validated('expiration_month'),
                    'expiration_year'  => $request->validated('expiration_year'),
                    'security_code'    => $request->validated('cvv'),
                ],
                'billing'   => [
                    'first_name'  => $request->validated('first_name'),
                    'last_name'   => $request->validated('last_name'),
                    'address'     => $request->validated('address'),
                    'city'        => $request->validated('city'),
                    'state'       => $request->validated('state'),
                    'postal_code' => $request->validated('postal_code'),
                    'country'     => $request->validated('country'),
                    'email'       => $request->validated('email'),
                    'phone'       => $request->validated('phone'),
                ],
            ]);

            if ($transaction->isSuccessful()) {
                // Persist the transaction to your database
                // Redirect or return success response
            }

            // Handle declined transaction
        } catch (CyberSourceException $e) {
            Log::error('Payment failed', ['error' => $e->getMessage()]);
            // Return error response
        }
    }
}
```
