<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

interface EncryptorInterface
{
    public function encrypt(string $plainText): string;
}
