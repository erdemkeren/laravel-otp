<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Exceptions;

use UnexpectedValueException;

class UnknownOtpFormat extends UnexpectedValueException
{
    public static function createForName(string $name): self
    {
        return new static(sprintf('Unknown otp format: %s.', $name));
    }
}
