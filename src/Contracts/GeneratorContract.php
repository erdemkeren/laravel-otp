<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Contracts;

interface GeneratorContract
{
    public function generate(): string;
}
