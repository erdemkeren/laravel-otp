<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use UnexpectedValueException;
use Erdemkeren\Otp\Contracts\FormatContract;
use Erdemkeren\Otp\Contracts\EncryptorContract;
use Erdemkeren\Otp\Contracts\FormatManagerContract;
use Erdemkeren\Otp\Contracts\TokenRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;

class OtpService
{
    public function __construct(
        private FormatManagerContract   $manager,
        private EncryptorContract       $encryptor,
        private TokenRepositoryContract $repository,
    ) {
        //
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
        ]), fn (OtpToken $otpToken) => $this->repository->persist($otpToken));
    }

    public function save(OtpToken $token): bool
    {
        return $this->repository->persist($token);
    }

    public function extend(OtpToken $token, int $secs): OtpToken
    {
        $extended = $token->extend($secs);

        $this->repository->persist($extended);

        return $extended;
    }

    public function refresh(OtpToken $token): OtpToken
    {
        $refreshed = $token->refresh();

        $this->repository->persist($refreshed);

        return $refreshed;
    }

    public function invalidate(OtpToken $token): OtpToken
    {
        $invalidated = $token->invalidate();

        $this->repository->persist($invalidated);

        return $invalidated;
    }

    public function addFormat(FormatContract $format): void
    {
        $this->manager->register($format);
    }

    public function sendOtpNotification(object $notifiable, OtpToken $token): void
    {
        $notifiable->notify(
            $this->getFormat($token->format())->createNotification($token)
        );
    }

    public function sendNewOtp(Authenticatable $user): void
    {
        $token = $this->create($user->getAuthIdentifier());

        if (! method_exists($user, 'notify')) {
            throw new UnexpectedValueException(
                'The otp owner should be an instance of notifiable or implement the notify method.'
            );
        }

        $this->sendOtpNotification($user, $token);
    }

    private function getFormat(string $format): FormatContract
    {
        return $this->manager->get($format);
    }
}
