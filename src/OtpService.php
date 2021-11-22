<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Erdemkeren\Otp\Contracts\EncryptorContract;
use Erdemkeren\Otp\Contracts\GeneratorManagerContract;
use Erdemkeren\Otp\Contracts\TokenRepositoryContract;
use Erdemkeren\Otp\Exceptions\UnregisteredGeneratorException;

/**
 * Class OtpService.
 */
class OtpService
{
    public function __construct(
        private GeneratorManagerContract $manager,
        private EncryptorContract $encryptor,
        private TokenRepositoryContract $tokenRepository,
    ) {
        //
    }

    /**
     * Create a new token and get it.
     *
     * @param  int|string  $authenticableId
     * @param  string  $generator
     * @return OtpToken
     */
    public function create(int|string $authenticableId, string $generator = 'default'): OtpToken
    {
        $plainText = $this->getPasswordGenerator($generator)();
        $cipherText = $this->encryptor->encrypt($plainText);

        $token = new OtpToken([
            'plain_text' => $plainText,
            'cipher_text' => $cipherText,
            'expiry_time' => 300,
            'authenticable_id' => $authenticableId,
        ]);

        $this->tokenRepository->persist($token);

        return $token;
    }

    /**
     * Save the given token to the storage.
     *
     * @param  OtpToken  $token
     * @return bool
     */
    public function save(OtpToken $token): bool
    {
        return $this->tokenRepository->persist($token);
    }

    /**
     * Extend the given token and get the extended instance.
     *
     * @param  OtpToken  $token
     * @param  int  $secs
     * @return OtpToken
     */
    public function extend(OtpToken $token, int $secs): OtpToken
    {
        $extended = $token->extend($secs);

        $this->tokenRepository->persist($extended);

        return $extended;
    }

    /**
     * Refresh the given token and get the refreshed instance.
     *
     * @param  OtpToken  $token
     * @return OtpToken
     */
    public function refresh(OtpToken $token): OtpToken
    {
        $refreshed = $token->refresh();

        $this->tokenRepository->persist($refreshed);

        return $refreshed;
    }

    /**
     * Invalidate the given token and get the invalidated instance.
     *
     * @param  OtpToken  $token
     * @return OtpToken
     */
    public function invalidate(OtpToken $token): OtpToken
    {
        $invalidated = $token->invalidate();

        $this->tokenRepository->persist($invalidated);

        return $invalidated;
    }

    /**
     * Add a new password generator implementation.
     *
     * @param  string  $name
     * @param  string|callable  $generator
     * @return void
     */
    public function addPasswordGenerator(string $name, string|callable $generator): void
    {
        $this->manager->register($name, $generator);
    }

    /**
     * Get the token generator by the given name.
     *
     * @param  string  $name
     * @return callable
     *
     * @throws UnregisteredGeneratorException
     */
    private function getPasswordGenerator(string $generator): callable
    {
        return $this->manager->get($generator);
    }
}
