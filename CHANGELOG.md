# Changelog

All notable changes to `erdemkeren/laravel-otp` will be documented in this file.

## Laravel OTP 1.0.0

Renames the package as laravel-otp. Was temporary-access before.

## 3.0.0 - 2018-11-08

- OtpController and Otp middleware are introduced
- Token generators are now password generators
- Token creation is now being handled by Encryptor implementations
- Repository pattern is not used anymore. Token persistence is a part of token itself

### v2 to v3 service method mapping:

- retrieve -> retrieveByCipherText
- retrieveUsingPlainText -> retrieveByPlainText
- check -> check
- checkUsingPlainText -> none. Token should be retrieved and then checked like `Otp::retrieveByPlainText()->expired()`
- checkAndProlong -> none. Token should be retrieved and then extended like `Otp::retrieveByPlainText()->extend(10)`
- checkUsingPlainTextAndProlong -> none. Above usages applies as well
- generate -> create
- update -> none. Use token methods to modify token state (`Token::extend()`, `Token::invalidate()` etc.)
- makeTokenFromPlainText -> no need anymore
- makeTokenFromEncryptedText -> no need anymore
- retrieveByAttributes -> no need anymore. But moved to `Token::retrieveByAttributes()
- delete -> will be implemented later. Not really needed since `Token::invalidate()` does the job
- deleteExpired -> will be implemented later

## 2.1.0 - 2018-02-06

- Adds token generator options:
    - String
    - Numeric
    - Numeric No 0
  which is configurable from config file.
- Moves the token & generators and interfaces to a common directory and namespace.

## 2.0.0 - 2017-01-04

- The code and token discrimination removed from the package. Plain text and encrypted text used instead.
- Totally separated token is now a part of access token.
- Interface names were changed after 1.0.0 but never released. This version includes new interface names.
- Most of the public service method signatures were changed due to removal of 'code'.

## 1.0.0 - 2016-12-30

- First release of the package.
