<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

interface AccessCodeInterface extends TokenInformationInterface
{
    /**
     * Convert the access code to string.
     *
     * @return string
     */
    public function __toString();
}
