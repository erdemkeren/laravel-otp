<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Contracts;

/**
 * Interface GeneratorContract.
 */
interface GeneratorContract
{
    /**
     * Generate a new token.
     *
     * @return string
     */
    public function generate(): string;
}
