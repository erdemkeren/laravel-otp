<?php

namespace Erdemkeren\TemporaryAccess\Token\TokenGenerator;

use Exception;

class NumericNo0TokenGenerator extends NumericTokenGenerator implements TokenGeneratorInterface
{
    protected function getPlainText($length)
    {
        return (string) str_replace(0, $this->getRandomDigitWithNo0(), (string) parent::getPlainText($length));
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
