<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Exceptions;

use LogicException;

class AuthenticationException extends LogicException
{
    public static function create(): self
    {
        throw new static(
            'The otp middleware requires authentication via laravel guards.'
        );
    }
}
