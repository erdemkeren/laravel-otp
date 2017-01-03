# Temporary Access

[![Latest Version on Packagist](https://img.shields.io/packagist/v/erdemkeren/temporary-access.svg?style=flat-square)](https://packagist.org/packages/erdemkeren/temporary-access)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/erdemkeren/temporary-access/master.svg?style=flat-square)](https://travis-ci.org/erdemkeren/temporary-access)
[![StyleCI](https://styleci.io/repos/77648231/shield?branch=master)](https://styleci.io/repos/77648231)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/2caf0c8e-2f92-4b09-851a-873989dbe0ee.svg?style=flat-square)](https://insight.sensiolabs.com/projects/2caf0c8e-2f92-4b09-851a-873989dbe0ee)
[![Quality Score](https://img.shields.io/scrutinizer/g/erdemkeren/temporary-access.svg?style=flat-square)](https://scrutinizer-ci.com/g/erdemkeren/temporary-access)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/erdemkeren/temporary-access/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/erdemkeren/temporary-access/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/erdemkeren/temporary-access.svg?style=flat-square)](https://packagist.org/packages/erdemkeren/temporary-access)

This package allows you to create temporary access tokens for your users in your Laravel applications.

Example Usage:

```php
public function TemporaryAccessController extends Controller
{
    /**
     * The temporary access service.
     *
     * @var TemporaryAccessService
     */
    private $service;
    
    public function __construct(TemporaryAccessService $service)
    {
        $this->service = $service;
    }
    
    public function show(Request $request)
    {
        $token = $request->input('token');
        
        if(! $this->service->check(auth()->user(), $token)) {
            // Whoops! Do something.
        }
        
        // All is well!
    }
    
    /*
     * You can also prolong the expire date of the access token.
     */
    
    public function showAndProlong(Request $request)
    {
        $token = $request->input('token');
        
        if(! $this->service->checkAndProlong(auth()->user(), $token)) {
            // Whoops! Do something.
        }
        
        // Continue using the application.
    }
    
    public function store()
    {
        $token = $this->service->generate($user = auth()->user());
        // Maybe send an sms with the $token->plain().
    }
    
    public function validate(Request $request)
    {
        $code = $request->input('token');
        
        if(! $token = $this->service->retrieveUsingPlainText(auth()->user(), $token)) {
            // Whoops! Do something.
        }
        
        return ['token' => $token->encrypted()];
    }
}
```

## Contents

- [Installation](#installation)
	- [Setting up the temporary-access](#setting-up-the-temporary-access)
- [Usage](#usage)
	- [Available methods](#available-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Credits](#credits)


## Installation

```
$ composer require erdemkeren/temporary-access;
```

### Setting up the temporary-access

Register the package in your `config/app.php` file:

```php
Erdemkeren\TemporaryAccess\TemporaryAccessServiceProvider::class,
```

Publish the components:

```
$ php artisan vendor:publish
```

Apply the migrations (Will create a table called `temporary_access_tokens` if you didn't change the migration.):

```
$ php artisan migrate
```

(Optional) Change the table name in the configuration `config/temporary_access.php`:

```php
<?php

// Defaults.
return [
    'table'   => 'temporary_access_tokens',
    'expires' => 15 // in minutes.
];

```

## Usage

The package has a `TemporaryAccessService`, which is (probably) the only class you need to interact with. There is no facade yet, but planning to add it in the future.

To let you decide which version of the token (plain text or encrypted) most of the service methods has two different signatures. Like:
```php
$service->retrieveUsingPlainText(AuthenticatableContract $authenticatable, $plainText);
```

and

```php
$service->retrieve(AuthenticatableContract $authenticatable, $encryptedText);
```

When you need to create a new temporary access token; you can call the `generate` method of the TemporaryAccessService with the user who will own the access token.

```php
$service = app()->make(Erdemkeren\TemporaryAccess\TemporaryAccessService::class);

$token = $service->generate($user = \App\User::find(1));
```

When you need to validate an access token, you can call the `check` method of the TemporaryAccessService.

```php
$service->check($user, $token);   // true
$service->checkUsingPlainText($user, "8JBGJA"); // true
```

If you want to extend the expire date of the token after validation; there is a method for that!: `checkAndProlong` 

```php
(string) $accessToken->expiresAt(); // "2016-12-29 22:25:00"
$token = $service->checkAndProlong($user, $token);
(string) $accessToken->expiresAt(); // "2016-12-29 22:25:30" (Executed after 30 seconds.)
```

The same applies for plain text checks:

```php
(string) $token->expiresAt(); // "2016-12-29 22:25:00"
$token = $service->checkUsingPlainTextAndProlong($user, "8JBGJA");
(string) $token->expiresAt(); // "2016-12-29 22:25:30" (Executed after 30 seconds.)
```

If you have a special case which you can't use the retrieve methods, you can use `retrieveByAttributes` method:

```php
$service->retrieveByAttributes([
    'token' => $encryptedText,
]);

// or maybe

$service->retrieveByAttributes([
    'token' => (string) $service->makeTokenFromPlainText("8JBGJA"),
]);
```

You can call the `delete` method to delete an access token:

```php
$service->delete($token);

// or

$service->delete((string) $service->makeTokenFromPlainText("8JBGJA"));
```

If you need to clear all expired token history from your database, you can use the `deleteExpired` method:

```php
$service->deleteExpired();
```


### Available methods

The available methods of the `Erdemkeren\TemporaryAccess\TemporaryAccessService`:

```php
/**
 * Retrieve an access token from the storage by the actual token.
 *
 * @param AuthenticatableContract $authenticatable The authenticatable who owns the token.
 * @param string|TokenInterface   $encryptedText   The token of the authenticatable.
 *
 * @return null|AccessTokenInterface
 */
public function retrieve(AuthenticatableContract $authenticatable, $encryptedText);

/**
 * Retrieve an access token from the storage by the plain token.
 *
 * @param AuthenticatableContract $authenticatable The authenticatable who owns the token.
 * @param string|TokenInterface   $plainText       The token of the authenticatable.
 *
 * @return null|AccessTokenInterface
 */
public function retrieveUsingPlainText(AuthenticatableContract $authenticatable, $plainText);

/**
 * Determine if an access token exists and is valid.
 *
 * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
 * @param  string|TokenInterface   $encryptedText   The encrypted token of the authenticatable.
 *
 * @return bool
 */
public function check(AuthenticatableContract $authenticatable, $encryptedText);

/**
 * Determine if an access token exists and is valid.
 *
 * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
 * @param  string|TokenInterface   $plainText       The plain token of the authenticatable.
 *
 * @return bool
 */
public function checkUsingPlainText(AuthenticatableContract $authenticatable, $plainText);

/**
 * Determine if an access token record exists and prolong the expire date if so.
 * If no prolong time given, we will reset the original expire time.
 *
 * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
 * @param  string|TokenInterface   $encryptedText   The token of the authenticatable.
 * @param  int|null                $prolong         The prolong time in minutes.
 *
 * @return bool|AccessTokenInterface
 */
public function checkAndProlong(AuthenticatableContract $authenticatable, $encryptedText, $prolong = null);

/**
 * Determine if an access token record exists and prolong the expire date if so.
 * If no prolong time given, we will reset the original expire time.
 *
 * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
 * @param  string|TokenInterface   $plainText       The token of the authenticatable.
 * @param  int|null                $prolong         The prolong time in minutes.
 *
 * @return bool|AccessTokenInterface
 */
public function checkUsingPlainTextAndProlong(AuthenticatableContract $authenticatable, $plainText, $prolong = null);

/**
 * Generate a new access token in the storage and get the token.
 *
 * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
 * @param  Carbon|null             $expiresAt       The optional expire date of the access token.
 *
 * @return AccessTokenInterface
 */
public function generate(AuthenticatableContract $authenticatable, Carbon $expiresAt = null);

/**
 * Update an access token in the storage.
 *
 * @param  AccessTokenInterface $accessToken The access token to be updated.
 *
 * @return bool
 */
public function update(AccessTokenInterface $accessToken);

/**
 * Revive an token from the given plain text.
 *
 * @param  string $plainText The plain text to be converted back to token instance.
 *
 * @return TokenInterface
 */
public function makeTokenFromPlainText($plainText);

/**
 * Revive an token from the given plain text.
 *
 * @param  string $encryptedText The encrypted token to be converted back to token instance.
 *
 * @return TokenInterface
 */
public function makeTokenFromEncryptedText($encryptedText);

/**
 * Retrieve the first resource by the given attributes.
 *
 * @param  array $queryParams The key - value pairs to match.
 * @param  array $attributes  The attributes to be returned from the storage.
 *
 * @return AccessTokenInterface|null
 */
public function retrieveByAttributes(array $queryParams, array $attributes = ['*']);

/**
 * Delete the given access token from the storage.
 *
 * @param  AccessTokenInterface|string $accessToken The access token or the encrypted text to be deleted.
 *
 * @return bool
 */
public function delete($accessToken);

/**
 * Delete the expired access tokens from the storage.
 *
 * @return void
 */
public function deleteExpired();
```

The available methods of the `Erdemkeren\TemporaryAccess\GenericAccessToken`:

```php
/**
 * Get the unique identifier of the authenticatable who owns the access token.
 *
 * @return string
 */
public function authenticatableId();

/**
 * Get the token.
 *
 * @return TokenInterface
 */
public function token();

/**
 * Get the access token as plain text.
 *
 * @return string
 * @throws LogicException
 */
public function plain();

/**
 * Get the access token encrypted.
 *
 * @return string
 */
public function encrypted();

/**
 * Get the created at timestamp of the access token.
 *
 * @return \Carbon\Carbon
 */
public function createdAt();

/**
 * Get the expires at timestamp of the access token.
 *
 * @return \Carbon\Carbon
 */
public function expiresAt();

/**
 * Get a new instance of the access token with a longer expire date.
 *
 * @param  int $prolong The prolong time in seconds.
 *
 * @return GenericAccessToken
 */
public function prolong($prolong);
```

There are more public methods you can use. If you want to learn more, please see the source code.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Credits

- Hilmi Erdem KEREN
