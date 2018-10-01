<?php

namespace Erdemkeren\TemporaryAccess;

class Encryptor implements EncryptorInterface
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encrypt(string $plainText): string
    {
        return hash_hmac('sha256', $plainText, $this->key);
    }
}
