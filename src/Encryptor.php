<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Erdemkeren\Otp\Contracts\EncryptorContract;

class Encryptor implements EncryptorContract
{
    public function __construct(
        private string $key
    ) {
    }

    public function encrypt(string $plainText): string
    {
        return hash_hmac('sha256', $plainText, $this->key);
    }
}
