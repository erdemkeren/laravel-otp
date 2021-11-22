<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Exceptions;

use RuntimeException;

/**
 * Class UndefinedTokenGeneratorException.
 */
class UnregisteredGeneratorException extends RuntimeException
{
    public static function createForName(string $name): self
    {
        return new static(sprintf('The generator [%s] is not registered.', $name));
    }
}
