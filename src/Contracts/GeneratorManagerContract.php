<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Contracts;

use Erdemkeren\Otp\Exceptions\UnregisteredGeneratorException;

/**
 * Interface PasswordGeneratorManagerContract.
 */
interface GeneratorManagerContract
{
    /**
     * Get the token generator by the given name.
     *
     * @param string $name
     *
     * @return callable
     * @throws UnregisteredGeneratorException
     */
    public function get(string $name): callable;

    /**
     * Add a new GeneratorContract implementation.
     *
     * @param string $name
     * @param string|callable $generator
     *
     * @return void
     */
    public function register(string $name, string|callable $generator): void;
}
