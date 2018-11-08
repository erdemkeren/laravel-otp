# Temporary Access

[![Latest Version on Packagist](https://img.shields.io/packagist/v/erdemkeren/temporary-access.svg?style=flat-square)](https://packagist.org/packages/erdemkeren/temporary-access)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/erdemkeren/temporary-access/master.svg?style=flat-square)](https://travis-ci.org/erdemkeren/temporary-access)
[![StyleCI](https://styleci.io/repos/77648231/shield?branch=master)](https://styleci.io/repos/77648231)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/2caf0c8e-2f92-4b09-851a-873989dbe0ee.svg?style=flat-square)](https://insight.sensiolabs.com/projects/2caf0c8e-2f92-4b09-851a-873989dbe0ee)
[![Quality Score](https://img.shields.io/scrutinizer/g/erdemkeren/temporary-access.svg?style=flat-square)](https://scrutinizer-ci.com/g/erdemkeren/temporary-access)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/erdemkeren/temporary-access/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/erdemkeren/temporary-access/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/erdemkeren/temporary-access.svg?style=flat-square)](https://packagist.org/packages/erdemkeren/temporary-access)

This package allows you to secure your resources with one time password access (otp).

Example Usage:

```php
Route::get('secret', function (): string {
    return 'The secret of immortality';
})->middleware('auth', 'otp-access');
```

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
	- [Basic Usage](#basic-usage)
	- [Advanced Usage](#advanced-usage)
	- [Deeper Knowledge](#deeper-knowledge)
- [Changelog](#changelog)
- [Testing](#testing)
- [Credits](#credits)


## Installation

1- Add the package to your dependencies.

```
$ composer require erdemkeren/temporary-access;
```

2- Register the package in your `config/app.php` file:

_only if you are using Laravel <=5.4 or your auto package discovery off._

```php
Erdemkeren\TemporaryAccess\TemporaryAccessServiceProvider::class,
```

3- Publish the components:

_Publishes a migration, two views and a configuration file._

```
$ php artisan vendor:publish
```

4- Apply the migrations:

_Will create a table called `temporary_access_tokens` to store generated token information._

```
$ php artisan migrate
```

5- Register the routes:

_These routes are required if you are planning to use `otp-access` middleware._

In your RouteServiceProvider, append the following line inside the `map` method:

```php
// App\RouteServiceProvider@map:
\Erdemkeren\TemporaryAccess\OtpRoutes::register();
```

6- Register the route middleware:

_Register the otp-access route middleware inside your `App\Http\Kernel`._

```php
/**
 * The application's route middleware.
 *
 * These middleware may be assigned to groups or used individually.
 *
 * @var array
 */
protected $routeMiddleware = [
    // [...]
    'otp-access' => \Erdemkeren\TemporaryAccess\Http\Middleware\OtpAccess::class,
];
```
## Configuration

This package comes with a set of handy configuration options:

**password_generator**: The password generator option allows you to decide which generator implementation to be used to generate new passwords.

Available built-in options: string, numeric and numeric-no-0.
default: string

**table**: The name of the table to be used to store the temporary access tokens.

default: temporary_access_tokens

**expiry_time**: The expiry time of the tokens in minutes.

default: 15

**default_channels**: The default notification channels of the token notification.

## Usage

### Basic Usage

After configuring your instance of the package,
you can use the built-in `otp-access` middleware alias to secure your endpoints:

```php
Route::get('secret', function (Request $request): string {
    $request->otpToken()->refresh();

    return 'The secret of immortality';
})->middleware('auth', 'otp-access');
```

This middleware will redirect any unauthenticated request to the `otp/create` endpoint
which we have registered in the installation process:

- A password will be generated using the configured password generator.
- The authenticated user will be notified about the password via the configured notification channel.
- The user will see a form to submit their password.
- You can change the appearance of the view under your `resources/views/otp` directory, modifying `create.blade.php` file.
- After a successful authentication; the user will be redirected back to the original route they requested at the first step.
- The redirected request will also include the `otpToken()` instance being used by the user.

### Advanced Usage

#### Adding the notification channel method:

If you are not using the `mail` channel, or your notification channel is expecting a method different than `mail` or `sms`, you can register your own method like:

```php
// AppServiceProvider::register():
TokenNotification::macro('AcmeSms', function () {
    // $this is TokenNotification class.
    return $this->notification->code;
});
```
_Don't forget to change your configuration file as well._

#### Using your own password generator:

To add your own password generator implemetation, you can call `addPasswordGenerator` method on `TemporaryAccess` service like:

```php
// AppServiceProvider::register():
app('temporary-access')->addPasswordGenerator('acme', function (int $length): string {
    return 'your_implementation';
});
```

If you need more power, you can also create your own password generator class too:

```php
<?php namespace App\Acme\PasswordGenerators;

use Erdemkeren\TemporaryAccess\PasswordGeneratorInterface;

class AcmePasswordGenerator implements PasswordGeneratorInterface
{
    /**
     * Generate an acme password with the given length.
     *
     * @param  int    $length
     * @return string
     */
    public function generate(int $length): string
    {
        return 'your implementation';
    }
}
```

You can register you password generator like the previous one:

```php
// AppServiceProvider::register():
TemporaryAccess::addPasswordGenerator('acme', AcmePasswordGenerator::class);
```

_Don't forget to change your configuration file as well._

#### Determining the otp channel per notifiable

The `Notification` class checks `otpChannels` existence inside the `notifiable` being notified.
If so, this method is being called to determine which notification channel is going to be used to notify the notifiable.

### Deeper Knowledge:

The public API consists of two main components: `TemporaryAccessService` and the `Token` which generally is being returned by the service.

#### TemporaryAccess Service:

If you are planning to create your own API or the basic functionality is not enough four you, you can use the TemporaryAccess Service API:

##### Chencking the validity of a given token:

```php
$isTokenValid = TemporaryAccess::check($authenticableId, $token);
```

##### Setting the password generator:

```php
TemporaryAccess::setPasswordGenerator('string');
```

##### Creating a new token for a given user:

```php
$token = TemporaryAccess::create(auth()->user(), $length = 6);
// See what can be done with tokens below.
```

##### Retrieveing an existing token from the storage by the given plain password:

```php
$token = TemporaryAccess::retrieveByPlainText(auth()->id(), $otpPassword);
// See what can be done with tokens below.
```

##### Retrieveing an existing token from the storage by the given cipher text (token):

```php
$token = TemporaryAccess::retrieveByCipherText(auth()->id(), $otpPassword);
// See what can be done with tokens below.
```
##### Changing the behavior of the Service

The package comes with a `ServiceProvider` which registers the TemporaryAccess
service to your application's container.

The TemporaryAccess orchestrates the method calls made to the 3 interface implementations below.

- PasswordGeneratorManagerInterface
- EncryptorInterface and
- TokenInterface

You can write your service provider and register the `TemporaryAccessService`
with your version of the dependencies.

_Note: Because the token class is being used with static calls,
you have to send the fully qualified name of your TokenInterface implementation._

#### Token API:

##### Getting the attributes of the token:

```php
public function authenticableId();
public function cipherText(): string;
public function plainText(): ?string; // If you have just created the token, plain text will be accessable. If you retrieved it; it won't.
public function createdAt(): Carbon;
public function updatedAt(): Carbon;
public function expiryTime(): int;
public function expiresAt(): Carbon;
public function timeLeft(): int;
public function expired(): bool;
```

##### Invalidate a token:

```php
public function revoke(): void;
public function invalidate(): void;
```

**e.g.**

```php
public function show(Request $request, $id) {
    if($request->input('revoke_session', false)) {
        $request->otpToken()->revoke();
    }

    return view('heaven');
}
```

##### Extend or refresh the token expiry time:

```php
// Extend the usage time of the token for the given seconds:
public function extend(?int $seconds = null): bool;
// Make the token function like it has just been created:
public function refresh(): bool;
```

**e.g.**

```php
$token = TemporaryAccess::retrieveByCipherText(
    auth()->id(),
    $request->input('otp_token')
);

if(! $token->expired()) {
 $token->refresh();
}
```

##### Create a new token:

```php
public static function create(
    $authenticableId,
    string $cipherText,
    ?string $plainText = null
): TokenInterface;
```

**e.g.**

```php
$token = Token::create(1, 'foo', 'plain foo');
```

#####  Retrieve a token from the storage by the given attributes:

_Make sure that the attributes you provided will return a unique token._

```php
public static function retrieveByAttributes(array $attributes): ?TokenInterface;
```

**e.g.**

```php
$token = Token::retrieveByAttributes([
    'authenticable_id' => 1,
    'cipher_text'      => 'foo',
]);
```

##### Convert the token to a notification:

```php
public function toNotification(): Notification;
```

**e.g.**

```php
$user->notify($token->toNotification());
```

# Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Credits

- Hilmi Erdem KEREN
- Berkay Güre
