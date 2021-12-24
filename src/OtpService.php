<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Closure;
use Erdemkeren\Otp\Contracts\EncryptorContract;
use Erdemkeren\Otp\Contracts\FormatContract;
use Erdemkeren\Otp\Contracts\FormatManagerContract;
use Erdemkeren\Otp\Contracts\TokenRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;

class OtpService
{
    public function __construct(
        private FormatManagerContract $manager,
        private EncryptorContract $encryptor,
        private TokenRepositoryContract $repository,
    ) {
        //
    }

    public function retrieveByPlainText(string $plainText): ?OtpToken
    {
        return $this->repository->retrieveByCipherText($this->encryptor->encrypt($plainText));
    }

    public function retrieveByCipherText(string $cipherText): ?OtpToken
    {
        return $this->repository->retrieveByCipherText($cipherText);
    }

    public function create(int|string $authenticableId, string $format = 'default'): OtpToken
    {
        $format = $this->getFormat($format);

        return tap(new OtpToken([
            'format' => $format->name(),
            'plain_text' => $plainText = $format->generator()(),
            'cipher_text' => $this->encryptor->encrypt($plainText),
            'expiry_time' => 300,
            'authenticable_id' => $authenticableId,
        ]), $this->getPersistor());
    }

    public function save(OtpToken $token): bool
    {
        return $this->repository->persist($token);
    }

    public function extend(OtpToken $token, int $secs): OtpToken
    {
        return tap($token->extend($secs), $this->getPersistor());
    }

    public function refresh(OtpToken $token): OtpToken
    {
        return tap($token->refresh(), $this->getPersistor());
    }

    public function invalidate(OtpToken $token): OtpToken
    {
        return tap($token->invalidate(), $this->getPersistor());
    }

    public function addFormat(FormatContract $format): void
    {
        $this->manager->register($format);
    }

    public function sendOtpNotification(object $notifiable, OtpToken $token): void
    {
        $notifiable->notify($this->getFormat($token->format())->createNotification($token));
    }

    public function sendNewOtp(Authenticatable $user): void
    {
        $this->sendOtpNotification($user, $this->create($user->getAuthIdentifier()));
    }

    private function getPersistor(): Closure
    {
        return fn(OtpToken $token): bool => $this->repository->persist($token);
    }

    private function getFormat(string $format): FormatContract
    {
        return $this->manager->get($format);
    }
}
