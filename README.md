# Temporary Access

[![Latest Version on Packagist](https://img.shields.io/packagist/v/erdemkeren/temporary-access.svg?style=flat-square)](https://packagist.org/packages/erdemkeren/temporary-access)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/erdemkeren/temporary-access/master.svg?style=flat-square)](https://travis-ci.org/erdemkeren/temporary-access)
[![StyleCI](https://styleci.io/repos/77648231/shield?branch=master)](https://styleci.io/repos/77648231)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/projects/2caf0c8e-2f92-4b09-851a-873989dbe0ee.svg?style=flat-square)](https://insight.sensiolabs.com/projects/2caf0c8e-2f92-4b09-851a-873989dbe0ee)
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
        
        if(! $this->service->checkToken(auth()->user(), $token)) {
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
        
        if(! $this->service->checkTokenAndProlong(auth()->user(), $token)) {
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
        $code = $request->input('code');
        
        if(! $token = $this->service->retrieveByCode(auth()->user(), $code)) {
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

Before reading the usage, here are the base definitions:

1- The plain text string which will be used by the users is `code`. 
2- The codes are created with the encrypted texts: `tokens`.

To let you decide stick with the `code` or return the associated `token` after first validation, every retrieval service provided by the package has two signatures. Like:
```php
retrieveByCode(AuthenticatableContract $authenticatable, $plainText);
```

and

```php
retrieveByToken(AuthenticatableContract $authenticatable, $encryptedText);
```

When you need to create a new temporary access token; you can call the `generate` method of the TemporaryAccessService with the user who will own the access token.

```php
$service = app()->make(Erdemkeren\TemporaryAccess\TemporaryAccessService::class);

$token = $service->generate($user = \App\User::find(1));
```

When you need to validate an access token, you can call the `exists` method of the TemporaryAccessService.

```php
$service->exists($user, "8JBGJA"); // true
$service->exists($user, $token);   // true
```

If you want to extend the expire date of the token after validate; there is a method for that: `checkAndProlong` 

```php
(string) $token->expiresAt(); // "2016-12-29 22:25:00"
$token = $service->checkAndProlong($user, "8JBGJA");
(string) $token->expiresAt(); // "2016-12-29 22:25:30" (Executed after 30 seconds.)
```

If you don't want to share the plain access code between requests, you can use `retrieveByAttributes` method:

```php
$service->retrieveByAttributes([
    'authenticatable_id' => $user->getAuthIdentifier(),
    'token' => $token->encrypted(),
]);
```

You can call the `delete` method to delete an access token:
```php
$service->delete($token);
```

If you need to clear all expired token history from your database, you can use the `deleteExpired` method:

```php
$service->deleteExpired($token);
```


### Available methods

Please see `TemporaryAccessService` class for available methods.


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Credits

- Hilmi Erdem KEREN
