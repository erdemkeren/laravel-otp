<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use ReflectionClass;
use UnexpectedValueException;
use Erdemkeren\Otp\Contracts\GeneratorContract;
use Erdemkeren\Otp\Contracts\GeneratorManagerContract;
use Erdemkeren\Otp\Exceptions\UnregisteredGeneratorException;
use Erdemkeren\Otp\Exceptions\GeneratorInstantiationException;
use function is_string;
use function is_callable;

/**
 * Class GeneratorManager.
 */
class GeneratorManager implements GeneratorManagerContract
{
    /**
     * The password generator registry.
     *
     * @var array
     */
    private static array $generators;

    /**
     * Get the token generator by the given name.
     *
     * @param string $name
     *
     * @return callable
     * @throws UnregisteredGeneratorException
     */
    public function get(string $name): callable
    {
        if (!array_key_exists($name, static::$generators)) {
            throw UnregisteredGeneratorException::createForName($name);
        }

        return static::$generators[$name];
    }

    /**
     * Registers the given password generator with the given name.
     *
     * @param string $name
     * @param callable|string $generator
     */
    public function register(string $name, string|callable $generator): void
    {
        if (is_string($generator)) {
            $generator = $this->createGeneratorFromString($generator);
        }

        if (! is_callable($generator) && ! $generator instanceof GeneratorContract) {
            throw new UnexpectedValueException(sprintf(
                'The generators should either be callable or an instance of %s',
                GeneratorContract::class,
            ));
        }

        static::$generators[$name] = is_callable($generator)
            ? $generator
            : function () use ($generator): string {
                return $generator->generate();
            };
    }
    /**
     * Create a new password generator instance using the given
     * fully qualified password generator class name.
     *
     * @param string $className
     *
     * @return GeneratorContract
     */
    private function createGeneratorFromString(string $className): GeneratorContract
    {
        if (! class_exists($className)) {
            throw GeneratorInstantiationException::createForMissingGenerator($className);
        }

        $generatorReflection = new ReflectionClass($className);
        if (! $generatorReflection->isInstantiable()) {
            throw GeneratorInstantiationException::createForNotInstantiableGenerator($className);
        }

        return new $className();
    }
}
