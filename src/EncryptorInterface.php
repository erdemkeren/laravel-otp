<?php

namespace Erdemkeren\TemporaryAccess;

interface EncryptorInterface
{
    public function encrypt(string $plainText): string;
}