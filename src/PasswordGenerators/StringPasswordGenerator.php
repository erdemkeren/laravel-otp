<?php

namespace Erdemkeren\TemporaryAccess\PasswordGenerators;

use Erdemkeren\TemporaryAccess\PasswordGeneratorInterface;

class StringPasswordGenerator implements PasswordGeneratorInterface
{
    public function generate(int $length): string
    {
        return str_random($length);
    }
}
