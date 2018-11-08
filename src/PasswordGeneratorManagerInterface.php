<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

/**
 * Class PasswordGeneratorManager.
 */
interface PasswordGeneratorManagerInterface
{
    /**
     * Registers the given password generator with the given name.
     *
     * @param string                                     $name
     * @param callable|PasswordGeneratorInterface|string $generator
     */
    public function register(string $name, $generator): void;

    /**
     * Get the previously registered generator by the given name.
     *
     * @param null|string $generatorName
     *
     * @return callable
     */
    public function get(?string $generatorName = null): callable;
}
