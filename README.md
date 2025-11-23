# coroq/session

Session-backed typed objects for PHP 8.0+.

## Requirements

- PHP 8.0+

## Installation

```bash
composer require coroq/session
```

## What it does

Manage session data as typed objects. You get IDE completion, type checking, and refactoring support for session data.

## Quick Start

```php
use Coroq\Session\Session;

class UserSession extends Session {
    public string $name = '';
    public bool $isLoggedIn = false;
}

$user = new UserSession('user');  // Loads values from $_SESSION['user']
$user->name = 'John';
$user->isLoggedIn = true;
// Saved to $_SESSION['user'] = ['name' => 'John', 'isLoggedIn' => true]
```

## Concept

Your class properties are stored in `$_SESSION` under the key you pass to the constructor.

```php
$user = new UserSession('user');
// Internally: $_SESSION['user'] = ['name' => 'John', 'isLoggedIn' => true]

$cart = new CartSession('cart');
// Internally: $_SESSION['cart'] = ['items' => [...]]
```

The same class can be used with different keys:

```php
$currentUser = new UserSession('current_user');
$editingUser = new UserSession('editing_user');
```

## Examples

### Shopping Cart

```php
class CartItem {
    public function __construct(
        public string $productId,
        public int $quantity = 1,
        public int $price = 0,
    ) {}
}

class CartSession extends Session {
    /** @var CartItem[] */
    public array $items = [];
    public int $totalPrice = 0;
}

$cart = new CartSession('cart');
$cart->items[] = new CartItem('prod_1', 2, 1000);
$cart->items[] = new CartItem('prod_2', 1, 500);
$cart->totalPrice = 2500;

// Stored as:
// $_SESSION['cart'] = [
//     'items' => [CartItem(...), CartItem(...)],
//     'totalPrice' => 2500,
// ]
```

### Multiple Session Keys

```php
class FormSession extends Session {
    public array $data = [];
    public array $errors = [];
}

$contactForm = new FormSession('form_contact');
$loginForm = new FormSession('form_login');
```

## What kind of data can be stored in session

PHP serializes (encodes as string) session data when storing it. This is general PHP behavior, not specific to this library.

**Safe to store:**
- Scalars (string, int, bool, float)
- Arrays
- Simple objects that only hold data ("Data Transfer Object")

**Avoid storing:**
- Objects with resources (file handles, database connections)
- Objects with dependencies (services, repositories)
- Objects with complex internal state

## Handling type mismatch

When property types change between deployments, stored session data may no longer match the expected types. In this case, all properties are reset to defaults. Override `onTypeError()` to customize:

```php
class UserSession extends Session {
    public int $version = 2;
    public string $name = '';

    protected function onTypeError(array $storedValues, \TypeError $error): void {
        // Log for debugging
        error_log('Session type mismatch: ' . $error->getMessage());

        // Reset to defaults
        parent::onTypeError($storedValues, $error);
    }
}
```

## Migration from v1.x

v2.0 is a complete redesign using typed properties. The previous implementation is available as `ArraySession` for backward compatibility.

```php
// Before (v1.x)
use Coroq\Session\Session;

$session = new Session('user');
$session->set(['name' => 'John']);
$name = $session->getIn('name');

// After (v2.x) - change import to ArraySession
use Coroq\Session\ArraySession;

$session = new ArraySession('user');
$session->set(['name' => 'John']);
$name = $session->getIn('name');
```

ArraySession is deprecated. For new code, use `Session` instead.
