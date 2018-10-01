<?php

namespace Erdemkeren\TemporaryAccess\PasswordGenerators;

use Exception;
use Erdemkeren\TemporaryAccess\PasswordGeneratorInterface;

class NumericNo0PasswordGenerator extends NumericPasswordGenerator implements PasswordGeneratorInterface
{
    public function generate(int $length): string
    {
        return (string) str_replace(0, $this->getRandomDigitWithNo0(), (string) parent::generate($length));
    }

    private function getRandomDigitWithNo0()
    {
        try {
            $int = random_int(1, 9);
        } catch (Exception $e) {
            $int = rand(1, 9);
        }

        return $int;
    }
}
