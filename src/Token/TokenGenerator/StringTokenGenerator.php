<?php

namespace Erdemkeren\TemporaryAccess\Token\TokenGenerator;

final class StringTokenGenerator extends AbstractTokenGenerator implements TokenGeneratorInterface
{
    protected function getPlainText($length)
    {
        return str_random($length);
    }
}
