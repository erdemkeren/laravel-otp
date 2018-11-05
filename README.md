# Temporary Access

This package allows you to secure your resources with one time password access (otp).

Example Usage:

```php
$route->get('secret', function (): string {
    return 'The secret of immortality';
})->middleware('otp-access');
```

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
	- [Available methods](#available-methods)
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

After configuring your instance of the package, you can use the built-in `otp-access` middleware alias to secure your endpoints:

```php
$route->get('secret', function (): string {
    return 'The secret of immortality';
})->middleware('otp-access');
```

This middleware will redirect any unauthenticated request to `otp/create` endpoint which we have registered in the installation process. After a successful authentication; the user will be redirected back to the original route. You can change the appearence of the view under your `resources/views/otp` directory, inside `create.blade.php` file.

## Advanced Usage

### Adding the notification channel method:

If you are not using the `mail` channel, or your notification channel is expecting a method different than `mail` or `sms`, you can register your own method like:

```php
// AppServiceProvider::register():
TokenNotification::macro('AcmeSms', function () {
    return $this->notification->code;
});
```
_Don't forget to change your configuration file as well._

### Using your own password generator:

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
        $request->otpToken->revoke();
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
