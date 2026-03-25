## CyberSource Laravel

This package (`asciisd/cybersource-laravel`) provides a Laravel integration for the CyberSource payment gateway REST API. It wraps the official `cybersource/rest-client-php` SDK behind a clean facade with authorize, charge, capture, void, refund, and retrieve operations.

### Key Classes

- **Facade:** `Asciisd\CyberSource\Facades\CyberSource` (accessor `cybersource`).
- **Gateway:** `Asciisd\CyberSource\CyberSource` — main service class; all public methods return a `Transaction` DTO.
- **Transaction DTO:** `Asciisd\CyberSource\Models\Transaction` — wraps normalized API response data (not an Eloquent model).
- **Payment:** `Asciisd\CyberSource\Models\Payment` — converts an options array to a `CreatePaymentRequest` SDK object.
- **Exception:** `Asciisd\CyberSource\Exceptions\CyberSourceException` — thrown on all API failures; has `getErrorData()`.
- **API Client:** `Asciisd\CyberSource\Http\CyberSourceApiClient` — internal wrapper around the CyberSource REST SDK.

### Configuration

Config lives at `config/cybersource.php`. Required `.env` variables:

@verbatim
<code-snippet name="Required .env variables" lang="env">
CYBERSOURCE_MERCHANT_ID=your-merchant-id
CYBERSOURCE_API_KEY_ID=your-api-key-id
CYBERSOURCE_SECRET_KEY=your-secret-key
CYBERSOURCE_ENVIRONMENT=apitest.cybersource.com
</code-snippet>
@endverbatim

- Use `apitest.cybersource.com` for sandbox and `api.cybersource.com` for production.
- Auth type defaults to `http_signature`; also supports `jwt` via `CYBERSOURCE_AUTH_TYPE`.
- Default currency is `USD` via `CYBERSOURCE_CURRENCY`.

### Installation

@verbatim
<code-snippet name="Install commands" lang="bash">
composer require asciisd/cybersource-laravel
php artisan cybersource:install
php artisan migrate
</code-snippet>
@endverbatim

### Facade API

All methods return `Asciisd\CyberSource\Models\Transaction` and throw `CyberSourceException` on failure.

| Method | Signature |
|--------|-----------|
| `authorize` | `CyberSource::authorize(array $options): Transaction` |
| `charge` | `CyberSource::charge(array $options): Transaction` |
| `capture` | `CyberSource::capture(string $transactionId, ?float $amount, array $options): Transaction` |
| `void` | `CyberSource::void(string $transactionId, array $options): Transaction` |
| `refund` | `CyberSource::refund(string $transactionId, ?float $amount, array $options): Transaction` |
| `retrieveTransaction` | `CyberSource::retrieveTransaction(string $transactionId): Transaction` |

### Payment Options Array Shape

The `authorize` and `charge` methods accept an options array with the following structure:

@verbatim
<code-snippet name="Options array shape" lang="php">
$options = [
    'reference' => 'order-123',        // Client reference code
    'amount'    => '100.00',            // Total amount as string
    'currency'  => 'USD',              // ISO 4217 currency code
    'card'      => [
        'number'           => '4111111111111111',
        'expiration_month' => '12',
        'expiration_year'  => '2025',
        'security_code'    => '123',   // Optional CVV
    ],
    'billing'   => [
        'first_name'  => 'John',
        'last_name'   => 'Doe',
        'address'     => '1 Market St',
        'city'        => 'San Francisco',
        'state'       => 'CA',
        'postal_code' => '94105',
        'country'     => 'US',
        'email'       => 'customer@example.com',
        'phone'       => '4158880000',
    ],
];
</code-snippet>
@endverbatim

### Transaction DTO

`Transaction` is a plain DTO (not Eloquent). Key methods:

- `getId()`, `getStatus()`, `getAmount()`, `getCurrency()`, `getReference()`
- `isSuccessful()` — true when status is `AUTHORIZED`, `PENDING`, `TRANSMITTED`, `COMPLETED`, or `SETTLED`
- `isAuthorized()`, `isPending()`, `isDeclined()`
- `getResponseCode()`, `getApprovalCode()`, `getReconciliationId()`
- `getCaptureUrl()`, `getVoidUrl()`, `getRefundUrl()` — HATEOAS link helpers
- `getRawResponse()`, `getAttribute($key, $default)`, `toArray()`

### Important Conventions

- **No automatic persistence.** The package does not write to the database. The migration provides a `cybersource_transactions` table schema, but the host application must persist transaction data itself.
- **No routes or controllers.** The package ships example controllers and views under `examples/` and `resources/views/` but does not register any routes. The host application must define its own routes and controllers.
- **Always wrap calls in try/catch** for `CyberSourceException`.
- **Capture and refund options** accept `reference` and `currency` keys; `amount` can be passed as the second positional argument or inside the options array.
- Debug logging goes to `storage/logs/cybersource_debug.log` and `storage/logs/cybersource_error.log` when `CYBERSOURCE_DEBUG=true`.
