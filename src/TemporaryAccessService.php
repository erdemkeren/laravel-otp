<?php

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
     * @param PasswordGeneratorManager $manager
     * @param EncryptorInterface $encryptor
     * @param string $defaultGenerator
     * @param int $passwordLength
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
     * Set the active password generator of the temporary access service.
     *
     * @param string $name
     *
     * @return void
     */
    public function setPasswordGenerator(string $name): void
    {
        $this->passwordGenerator = $this->manager->get($name);
    }

    /**
     * Create a new temporary access token.
     *
     * @param  Authenticatable $authenticatable
     * @param  int             $length
     *
     * @return Token
     */
    public function create(Authenticatable $authenticatable, ?int $length = null): TokenInterface
    {
        $plainText = $this->getPasswordGenerator()($length ?: $this->passwordLength);

        $cipherText = $this->encryptor->encrypt($plainText);

        return Token::create($authenticatable->getAuthIdentifier(), $cipherText, $plainText);
    }

    public function findByPlainText(string $plainText): ?TokenInterface
    {
        return $this->findByCipherText($this->encryptor->encrypt($plainText));
    }

    public function findByCipherText(string $cipherText): ?TokenInterface
    {
        return Token::findByAttributes([
            'cipher_text' => $cipherText
        ]);
    }

    /**
     * Add a new password generator implementation.
     *
     * @param string                                     $name
     * @param callable|PasswordGeneratorInterface|string $generator
     *
     * @return void
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
    private function getPasswordGenerator(): callable {
        return $this->passwordGenerator ?: $this->passwordGenerator = $this->manager->get($this->defaultGenerator);
    }
}
