<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Contracts;

interface FormatManagerContract
{
    public function get(string $name): FormatContract;

    public function register(FormatContract $format): void;
}
