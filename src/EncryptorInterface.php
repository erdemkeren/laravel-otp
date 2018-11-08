<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

interface EncryptorInterface
{
    public function encrypt(string $plainText): string;
}
