<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Closure;
use Erdemkeren\Otp\Contracts\FormatContract;

class GenericFormat implements FormatContract
{
    public function __construct(
        private string $name,
        private Closure $generator,
        private Closure $notificationResolver
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function generator(): Closure
    {
        return $this->generator;
    }

    public function createNotification(OtpToken $token): object
    {
        return ($this->notificationResolver)($token);
    }
}
