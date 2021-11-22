<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Exceptions;

use RuntimeException;

/**
 * Class GeneratorInstantiationException.
 */
class GeneratorInstantiationException extends RuntimeException
{
    public static function createForMissingGenerator(string $className): self
    {
        return new static(sprintf(
            'The generator [%s] could not be found.',
            $className,
        ));
    }

    public static function createForNotInstantiableGenerator(string $className): self
    {
        return new static(sprintf(
            'The generator [%s] is not instantiable.',
            $className,
        ));
    }
}
