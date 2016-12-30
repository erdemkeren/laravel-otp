<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

interface AccessCode extends TokenInformation
{
    /**
     * Convert the access code to string.
     *
     * @return string
     */
    public function __toString();
}
