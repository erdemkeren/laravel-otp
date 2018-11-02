<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class TemporaryAccessService.
 */
final class TemporaryAccessService
{
    /**
     * The password generator manager.
     *
     * @var PasswordGeneratorManager
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
     * The default temporary access password generator.
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
     * TemporaryAccessService constructor.
     *
     * @param PasswordGeneratorManager $manager
     * @param EncryptorInterface       $encryptor
     * @param string                   $defaultGenerator
     * @param int                      $passwordLength
     */
    public function __construct(
        PasswordGeneratorManager $manager,
        EncryptorInterface $encryptor,
        string $defaultGenerator,
        int $passwordLength
    ) {
        $this->manager = $manager;
        $this->encryptor = $encryptor;
        $this->passwordLength = $passwordLength;
        $this->defaultGenerator = $defaultGenerator;
    }

    /**
     * Check the temporary access of the authenticable
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
     * Set the active password generator of the temporary access service.
     *
     * @param string $name
     */
    public function setPasswordGenerator(string $name): void
    {
        $this->passwordGenerator = $this->manager->get($name);
    }

    /**
     * Create a new temporary access token.
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

        return Token::create($authenticatableId, $cipherText, $plainText);
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

        return Token::retrieveByAttributes([
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
