# Keysoft Helper Library Microservice

Shared core library for the Keysoft microservice ecosystem.

This package centralizes reusable components and enforces operational policies across all non-core services.

---

# ✨ Features

- Shared Eloquent Models
- Shared Enums
- Shared Traits
- Shared Infrastructure Utilities
- JWT Service and Middleware
- General Cipher (AES-256-GCM)
- Centralized Command Guard (Artisan protection layer)
- Laravel Auto-Discovery Support

---

# 📦 Setup

```json
"repositories": [
     {
         "type": "vcs",
         "url": "https://github.com/KeysoftGit/laravel-library-microservice.git"
     }
 ]
````
Add manual on your composer.json.

---
# 📦 Registry Your Token Github

```bash
composer config --global github-oauth.github.com TOKEN_GITHUB
```

Replace TOKEN_GITHUB.

---
# 📦 Installation

Install via Composer:

```bash
composer require keysoft/helper-library-microservice
```
OR
```bash
composer require keysoft/helper-library-microservice:dev-main
```
Laravel will automatically discover the service provider.

---

# ⚙️ Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=keysoft-config
```

This will generate:

```
config/keysoft-lib-config.php
```

# JWT

## Overview

The package includes a JWT helper for:

- token generation
- token validation
- token refresh / rotation
- token revocation using blacklist
- permission lookup from the token payload

Main classes:

```php
Keysoft\HelperLibrary\Http\Jwt\Services\JwtService
Keysoft\HelperLibrary\Http\Middleware\JwtMiddleware
Keysoft\HelperLibrary\Http\Jwt\Exceptions\JwtException
```

---

## JWT Configuration

After publishing the config file, set these environment variables:

```env
JWT_SECRET=change-this-secret
JWT_ALGORITHM=HS256
JWT_TTL=3600
JWT_REFRESH_TTL=86400
```

Relevant config section:

```php
'jwt' => [
    'secret' => env('JWT_SECRET', 'your-secret'),
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
    'ttl' => env('JWT_TTL', 3600),
    'refresh_ttl' => env('JWT_REFRESH_TTL', 86400),
    'blacklist_enabled' => true,
    'prefix' => 'Bearer',
],
```

Notes:

- `ttl` is the token lifetime in seconds
- `refresh_ttl` is the maximum token age that can still be refreshed
- `blacklist_enabled` allows token revocation

---

## Generate Token

Use `JwtService` from your controller or service:

```php
use Keysoft\HelperLibrary\Http\Jwt\Services\JwtService;

$jwt = new JwtService();

$token = $jwt->generate([
    'sub' => $user->id,
    'email' => $user->email,
    'permissions' => ['user.read', 'user.write'],
], 'web-admin');
```

The service automatically adds:

- `iat`
- `exp`
- `device_id`

If `device_id` is not provided, the service generates one automatically.

---

## Validate Token

```php
use Keysoft\HelperLibrary\Http\Jwt\Exceptions\JwtException;
use Keysoft\HelperLibrary\Http\Jwt\Services\JwtService;

$jwt = new JwtService();

try {
    $payload = $jwt->validate($token);
} catch (JwtException $e) {
    return response()->json([
        'message' => $e->getMessage(),
    ], 401);
}
```

Possible validation messages:

- `Token expired`
- `Invalid token signature`
- `Invalid token`
- `Token revoked`

---

## Refresh Token

```php
use Keysoft\HelperLibrary\Http\Jwt\Services\JwtService;

$jwt = new JwtService();
$newToken = $jwt->refresh($token);
```

Refresh flow:

1. The current token is validated.
2. The token age is checked against `refresh_ttl`.
3. The old token is blacklisted.
4. A new token is generated with the same payload and `device_id`.

---

## Revoke Token

To revoke a token manually:

```php
use Keysoft\HelperLibrary\Http\Jwt\Services\JwtService;

$jwt = new JwtService();
$jwt->blacklist($token);
```

---

## Permissions

Store permissions in the payload:

```php
$token = $jwt->generate([
    'sub' => $user->id,
    'permissions' => ['orders.read', 'orders.create'],
]);
```

Check them later:

```php
$permissions = $jwt->getPermissions($token);
$canCreateOrder = $jwt->hasPermission($token, 'orders.create');
```

---

## Middleware Usage

The package provides `JwtMiddleware` to validate bearer tokens on incoming requests.

Example route usage:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Keysoft\HelperLibrary\Http\Middleware\JwtMiddleware;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('/profile', function (Request $request) {
        return response()->json([
            'payload' => $request->attributes->get('jwt_payload'),
            'permissions' => $request->attributes->get('jwt_permissions', []),
        ]);
    });
});
```

Expected header:

```http
Authorization: Bearer your-jwt-token
```

After successful validation, the middleware stores:

- `jwt_payload`
- `jwt_permissions`

inside the request attributes.

---

## Typical Login Flow

```php
use Illuminate\Http\Request;
use Keysoft\HelperLibrary\Http\Jwt\Exceptions\JwtException;
use Keysoft\HelperLibrary\Http\Jwt\Services\JwtService;

class AuthController
{
    public function login(Request $request)
    {
        $jwt = new JwtService();

        $token = $jwt->generate([
            'sub' => 1,
            'email' => 'user@example.com',
            'permissions' => ['profile.read'],
        ], 'mobile-app');

        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
        ]);
    }

    public function refresh(Request $request)
    {
        $jwt = new JwtService();

        try {
            $token = $jwt->refresh($request->bearerToken());

            return response()->json([
                'token' => $token,
                'type' => 'Bearer',
            ]);
        } catch (JwtException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}
```

---

# General Cipher

## Overview

The package includes `GeneralCipher` for symmetric encryption and decryption.

Main class:

```php
Keysoft\HelperLibrary\Support\GeneralCipher
```

Default behavior:

- encrypts using `AES-256-GCM`
- reads prefix, key, and cipher from environment variables
- keeps payload format stable for cross-language usage
- can still decrypt legacy `AES-256-CBC` payloads generated by older versions

---

## Environment Variables

Set these values in your service:

```env
GENERAL_CIPHER_PREFIX=enc1
GENERAL_CIPHER_KEY=base64:REPLACE_WITH_32_BYTE_BASE64_KEY
GENERAL_CIPHER_CIPHER=AES-256-GCM
```

Notes:

- `GENERAL_CIPHER_PREFIX` is used to detect encrypted payloads
- `GENERAL_CIPHER_KEY` must be `base64:...` or 64-character hex
- the key must decode to exactly 32 bytes
- `GENERAL_CIPHER_CIPHER` currently supports `AES-256-GCM` and `AES-256-CBC`

Relevant config section:

```php
'general_cipher' => [
    'prefix' => env('GENERAL_CIPHER_PREFIX', 'enc1'),
    'key' => env('GENERAL_CIPHER_KEY', env('APP_KEY')),
    'cipher' => env('GENERAL_CIPHER_CIPHER', 'AES-256-GCM'),
],
```

---

## Payload Format

New encrypted payloads use this format:

```text
enc1:<nonce_base64>:<ciphertext_base64>:<tag_base64>
```

For `AES-256-GCM`:

- part 1: payload prefix
- part 2: nonce / IV
- part 3: ciphertext
- part 4: authentication tag

This format is the same one used by the Go decrypt example.

---

## PHP Usage

```php
use Keysoft\HelperLibrary\Support\GeneralCipher;

$cipher = new GeneralCipher();

$encrypted = $cipher->encrypt('secret-value');
$plainText = $cipher->decrypt($encrypted);
```

If you want to override the defaults explicitly:

```php
use Keysoft\HelperLibrary\Support\GeneralCipher;

$cipher = new GeneralCipher(
    key: 'base64:REPLACE_WITH_32_BYTE_BASE64_KEY',
    prefix: 'enc1',
    cipher: 'AES-256-GCM',
);
```

Behavior:

- `encrypt()` returns the original value when input is `null`, empty, or already prefixed
- `decrypt()` returns the original value when input is `null`, empty, or not encrypted
- invalid payload, invalid key, invalid tag, or invalid MAC will throw `RuntimeException`

---

## Go Decrypt Example

Example file:

```text
examples/go/general_cipher_decrypt.go
```

The Go example reads the same environment variables:

```env
GENERAL_CIPHER_PREFIX=enc1
GENERAL_CIPHER_KEY=base64:REPLACE_WITH_32_BYTE_BASE64_KEY
GENERAL_CIPHER_CIPHER=AES-256-GCM
```

Run example:

```bash
go run ./examples/go/general_cipher_decrypt.go
```

Core call:

```go
plainText, err := DecryptGeneralCipher(payload, key, prefix, cipherName)
```

The Go implementation is compatible with payloads generated by `GeneralCipher` in PHP when using `AES-256-GCM`.

---

# 🛡 Command Guard

## Overview

The **Command Guard** feature prevents selected Artisan commands from being executed.

This is especially useful in microservice environments where:

* Database schema is managed by a Core service
* Satellite services must not run migrations
* Destructive commands must be restricted
* Operational control must be centralized

---

## How It Works

The library listens to Laravel's console lifecycle using:

```
Illuminate\Console\Events\CommandStarting
```

When a command starts:

1. The guard checks if the command matches configured rules
2. If matched:

    * Execution stops
    * A custom message is displayed
    * The process exits gracefully
    * No exception is thrown

This ensures safe and silent enforcement.

---

## 🔧 Default Configuration

```php
'command_guard' => [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Guard
    |--------------------------------------------------------------------------
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Blocked Commands (Prefix Match)
    |--------------------------------------------------------------------------
    | Any command starting with these prefixes will be blocked
    */
    'blocked_commands' => [
        'migrate',
        'db',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception List (Whitelist)
    |--------------------------------------------------------------------------
    | Commands allowed even if prefix matches
    */
    'except' => [
        // 'migrate:status',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Message
    |--------------------------------------------------------------------------
    */
    'message' => 'Command blocked by Keysoft Library.',
],
```

---

## 🚫 Blocked Commands (Default)

With default configuration enabled, the following commands will be blocked:

```
php artisan migrate
php artisan migrate:fresh
php artisan migrate:rollback
php artisan migrate:reset
php artisan db:wipe
php artisan db:seed
```

Output example:

```
Command blocked by Keysoft Library.
```

The command exits cleanly without throwing an exception.

---

## ✅ Allow Specific Commands (Whitelist)

To allow certain commands:

```php
'except' => [
    'migrate:status',
],
```

---

## 🔓 Disable Guard (For Core Service)

If a service is responsible for handling migrations:

```php
'command_guard' => [
    'enabled' => false,
],
```

---

# 🏗 Microservice Architecture Policy

This library enforces infrastructure rules across services.

### Core Service Responsibilities

* Database schema management
* Migration execution
* Structural changes

### Non-Core / Satellite Service Responsibilities

* Consume shared models
* Business logic execution
* API orchestration
* No schema modification

The Command Guard ensures system integrity by preventing unintended structural changes.

---

# 📁 Package Structure

```
src/
 ├── Models/
 ├── Enums/
 ├── Traits/
 ├── Providers/
 └── ...
```

---

# 🧩 Model Requirements

All shared models must follow this structure:

```php
class ExampleModel extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
}
```

---

# 🔐 Security Design

* No exception thrown (prevents stack traces in production)
* Silent command interception
* Config-driven policy control
* Service-level override support

---

# 🧩 Requirements

* PHP ^8.2
* Laravel 12.x
* illuminate/database ^12.0
* illuminate/support ^12.0
* illuminate/http ^12.0
* illuminate/routing ^12.0
* illuminate/session ^12.0
* illuminate/console ^12.0

---

# 🚀 Versioning

Follow semantic versioning:

```
MAJOR.MINOR.PATCH
```

---

# 📄 License

MIT

---

# 👨‍💻 Maintained By

Keysoft Engineering Team
