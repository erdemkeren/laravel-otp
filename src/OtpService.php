<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class OtpService.
 */
class OtpService
{
    /**
     * The password generator manager.
     *
     * @var PasswordGeneratorManagerInterface
     */
    private $manager;

    /**
     * The encryptor implementation.
     *
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * The password length.
     *
     * @var int
     */
    private $passwordLength;

    /**
     * The default otp password generator.
     *
     * @var string
     */
    private $defaultGenerator;

    /**
     * The password generator.
     *
     * @var callable
     */
    private $passwordGenerator;

    /**
     * The name of the token class being used
     * by the otp service.
     *
     * @var string
     */
    private $tokenClass;

    /**
     * OtpService constructor.
     *
     * @param PasswordGeneratorManagerInterface $manager
     * @param EncryptorInterface                $encryptor
     * @param string                            $defaultGenerator
     * @param int                               $passwordLength
     * @param string                            $tokenClass
     */
    public function __construct(
        PasswordGeneratorManagerInterface $manager,
        EncryptorInterface $encryptor,
        string $defaultGenerator,
        int $passwordLength,
        string $tokenClass
    ) {
        $this->manager = $manager;
        $this->encryptor = $encryptor;
        $this->passwordLength = $passwordLength;
        $this->defaultGenerator = $defaultGenerator;

        if (! class_exists($tokenClass)) {
            throw new \RuntimeException(
                "The token implementation [{$tokenClass}] could not be found."
            );
        }

        $generatorReflection = new \ReflectionClass($tokenClass);
        if (! $generatorReflection->isInstantiable()) {
            throw new \RuntimeException(
                "The token implementation [{$tokenClass}] is not instantiable."
            );
        }

        if (! is_subclass_of($tokenClass, TokenInterface::class)) {
            throw new \TypeError(
                'The token class should be an instance of '.TokenInterface::class
            );
        }

        $this->tokenClass = $tokenClass;
    }

    /**
     * Check the otp of the authenticable
     * with the given cipher text.
     *
     * @param mixed  $authenticableId
     * @param string $token
     *
     * @return bool
     */
    public function check($authenticableId, string $token): bool
    {
        $token = $this->retrieveByCipherText($authenticableId, $token);

        return (bool) $token && ! $token->expired();
    }

    /**
     * Set the active password generator of the otp service.
     *
     * @param string $name
     */
    public function setPasswordGenerator(string $name): void
    {
        $this->passwordGenerator = $this->manager->get($name);
    }

    /**
     * Create a new otp token.
     *
     * @param Authenticatable|mixed $authenticatableId
     * @param int                   $length
     *
     * @return Token
     */
    public function create($authenticatableId, ?int $length = null): TokenInterface
    {
        $plainText = $this->getPasswordGenerator()($length ?: $this->passwordLength);
        $cipherText = $this->encryptor->encrypt($plainText);

        if ($authenticatableId instanceof Authenticatable) {
            $authenticatableId = $authenticatableId->getAuthIdentifier();
        }

        return $this->tokenClass::create($authenticatableId, $cipherText, $plainText);
    }

    /**
     * Retrieve the token of the authenticable
     * by the given plain text.
     *
     * @param mixed  $authenticableId
     * @param string $plainText
     *
     * @return null|TokenInterface
     */
    public function retrieveByPlainText($authenticableId, string $plainText): ?TokenInterface
    {
        return $this->retrieveByCipherText($authenticableId, $this->encryptor->encrypt($plainText));
    }

    /**
     * Retrieve the token of the authenticable
     * by the given cipher text.
     *
     * @param mixed  $authenticableId
     * @param string $cipherText
     *
     * @return null|TokenInterface
     */
    public function retrieveByCipherText($authenticableId, string $cipherText): ?TokenInterface
    {
        if ($authenticableId instanceof Authenticatable) {
            $authenticableId = $authenticableId->getAuthIdentifier();
        }

        return $this->tokenClass::retrieveByAttributes([
            'authenticable_id' => $authenticableId,
            'cipher_text'      => $cipherText,
        ]);
    }

    /**
     * Add a new password generator implementation.
     *
     * @param string                                     $name
     * @param callable|PasswordGeneratorInterface|string $generator
     */
    public function addPasswordGenerator(string $name, $generator): void
    {
        $this->manager->register($name, $generator);
    }

    /**
     * Get the active password generator.
     *
     * @return callable
     */
    private function getPasswordGenerator(): callable
    {
        return $this->passwordGenerator ?: $this->passwordGenerator = $this->manager->get($this->defaultGenerator);
    }
}
