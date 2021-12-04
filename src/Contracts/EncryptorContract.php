<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Contracts;

interface EncryptorContract
{
    public function encrypt(string $plainText): string;
}
