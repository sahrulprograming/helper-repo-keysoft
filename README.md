# Keysoft Helper Library Microservice

Shared core library for the Keysoft microservice ecosystem.

This package centralizes reusable components and enforces operational policies across all non-core services.

---

# ✨ Features

- Shared Eloquent Models
- Shared Enums
- Shared Traits
- Shared Infrastructure Utilities
- Centralized Command Guard (Artisan protection layer)
- Laravel Auto-Discovery Support

---

# 📦 Installation

Install via Composer:

```bash
composer require keysoft/helper-library-microservice
````

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
