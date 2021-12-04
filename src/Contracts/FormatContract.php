<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Contracts;

use Closure;
use Erdemkeren\Otp\OtpToken;

interface FormatContract
{
    public function name(): string;

    public function generator(): Closure;

    public function createNotification(OtpToken $token): object;
}
