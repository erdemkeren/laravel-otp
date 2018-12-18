<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

/**
 * Class PasswordGeneratorManager.
 */
final class PasswordGeneratorManager implements PasswordGeneratorManagerInterface
{
    /**
     * The password generator registry.
     *
     * @var array
     */
    private static $generators = [];

    /**
     * Registers the given password generator with the given name.
     *
     * @param string                                     $name
     * @param callable|PasswordGeneratorInterface|string $generator
     *
     * @throws \ReflectionException
     */
    public function register(string $name, $generator): void
    {
        if (\is_string($generator)) {
            $generator = $this->createGeneratorFromString($generator);
        }

        if (! \is_callable($generator) && ! $generator instanceof PasswordGeneratorInterface) {
            $msg = 'The generators should either be callable or an instance of '.PasswordGeneratorInterface::class;

            throw new \UnexpectedValueException($msg);
        }

        static::$generators[$name] = \is_callable($generator)
            ? $generator
            : function (int $length) use ($generator): string {
                return $generator->generate($length);
            };
    }

    /**
     * Get the previously registered generator by the given name.
     *
     * @param null|string $generatorName
     *
     * @return callable
     */
    public function get(?string $generatorName = null): callable
    {
        if (! \in_array($generatorName, array_keys(static::$generators), true)) {
            throw new \UnexpectedValueException(
                'The '.$generatorName.' password generator is not registered.'
            );
        }

        return static::$generators[$generatorName];
    }

    /**
     * Create a new password generator instance using the given
     * fully qualified password generator class name.
     *
     * @param string $className
     *
     * @throws \ReflectionException
     *
     * @return PasswordGeneratorInterface
     */
    private function createGeneratorFromString(string $className): PasswordGeneratorInterface
    {
        if (! class_exists($className)) {
            throw new \RuntimeException(
                "The generator [{$className}] could not be found."
            );
        }

        $generatorReflection = new \ReflectionClass($className);
        if (! $generatorReflection->isInstantiable()) {
            throw new \RuntimeException(
                "The generator [{$className}] is not instantiable."
            );
        }

        return new $className();
    }
}
