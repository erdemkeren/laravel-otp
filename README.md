# Temporary Access

This package allows you to secure whatever you want with one time password access.

Example Usage:

```php
$route->get('important-information', function (): string {
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

```
$ composer require erdemkeren/temporary-access;
```

Publish the components:

```
$ php artisan vendor:publish
```

Apply the migrations (Will create a table called `temporary_access_tokens` if you didn't change the migration.):

```
$ php artisan migrate
```

For laravel >=5.5 that's all. new package will be recognized automatically. If you are using
Laravel <=5.4, than you have to register the service provider manually:

Register the package in your `config/app.php` file:

```php
Erdemkeren\TemporaryAccess\TemporaryAccessServiceProvider::class,
```

## Configuration

This package comes with a set of handy configuration options:

_token_generator_: The token generator option allows you to decide which generator
implementation to be used when generating new token.

Available built-in options: string, numeric and numeric-no-0.
default: string

_table_: The name of the table to be used to store the temporary access tokens.

default: temporary_access_tokens

_expiry_time_: The expiry time of the tokens in minutes.

default: 15

## Usage



## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Credits

- Hilmi Erdem KEREN
