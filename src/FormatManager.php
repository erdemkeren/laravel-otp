<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Erdemkeren\Otp\Contracts\FormatContract;
use Erdemkeren\Otp\Exceptions\UnknownOtpFormat;
use Erdemkeren\Otp\Contracts\FormatManagerContract;

class FormatManager implements FormatManagerContract
{
    private static array $formats;

    public function __construct(private string $defaultFormat)
    {
    }

    public function get(string $name): FormatContract
    {
        $name = $name === 'default' ? $this->defaultFormat : $name;
        if (! array_key_exists($name, static::$formats)) {
            throw UnknownOtpFormat::createForName($name);
        }

        return static::$formats[$name];
    }

    public function register(FormatContract $format): void
    {
        static::$formats[$format->name()] = $format;
    }
}
