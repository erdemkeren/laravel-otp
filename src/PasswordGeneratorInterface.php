<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

/**
 * Interface PasswordGeneratorInterface.
 */
interface PasswordGeneratorInterface
{
    /**
     * Generate a new password.
     *
     * @param int $length
     *
     * @return string
     */
    public function generate(int $length);
}
