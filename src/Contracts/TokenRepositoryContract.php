<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Contracts;

use Erdemkeren\Otp\OtpToken;

interface TokenRepositoryContract
{
    public function retrieveByCipherText(string $cipherText): ?OtpToken;

    public function persist(OtpToken $token): bool;
}
