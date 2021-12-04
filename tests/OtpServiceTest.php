<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Test;

use Carbon\Carbon;
use Erdemkeren\Otp\Contracts\EncryptorContract;
use Erdemkeren\Otp\Contracts\FormatContract;
use Erdemkeren\Otp\Contracts\FormatManagerContract;
use Erdemkeren\Otp\Contracts\TokenRepositoryContract;
use Erdemkeren\Otp\GenericFormat;
use Erdemkeren\Otp\OtpService;
use Erdemkeren\Otp\OtpToken;
use Erdemkeren\Otp\Test\TestFormat\AcmeNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OtpServiceTest extends TestCase
{
    private OtpService $tokenService;

    private MockObject|TokenRepositoryContract $repository;

    private MockObject|EncryptorContract $encryptor;

    private MockObject|FormatManagerContract $manager;

    private MockObject|Notifiable $notifiable;

    private MockObject|FormatContract $format;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notifiable = $this->createMock(Notifiable::class);
        $this->manager = $this->createMock(FormatManagerContract::class);
        $this->encryptor = $this->createMock(EncryptorContract::class);
        $this->repository = $this->createMock(TokenRepositoryContract::class);
        $this->tokenService = new OtpService($this->manager, $this->encryptor, $this->repository);
    }

    /**
     * @test
     */
    public function itIsInstantiable(): void
    {
        $this->assertInstanceOf(OtpService::class, $this->tokenService);
    }

    /**
     * @test
     */
    public function itRetrievesTheOtpTokenByTheGivenCipherText(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('retrieveByCipherText')
            ->with(':cipher_text:')
            ->willReturn($otpToken = new OtpToken(['cipher_text' => ':cipher_text:']));

        $this->assertEquals(
            $otpToken,
            $this->tokenService->retrieveByCipherText(':cipher_text:'),
        );
    }

    /**
     * @test
     */
    public function itSupportsCustomFormats(): void
    {
        $format = new GenericFormat(
            ':acme:',
            fn(): string => ':acme:',
            fn(OtpToken $otp): AcmeNotification => new AcmeNotification($otp),
        );

        $this->manager
            ->expects($this->once())
            ->method('register')
            ->with($format);

        $this->tokenService->addFormat($format);
    }

    /**
     * @test
     */
    public function itCreatesANewPersistedTokenWithTheGivenAuthenticableId(): void
    {
        $this->manager
            ->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn(
                new GenericFormat(
                    ':acme:',
                    fn(): string => ':otpToken:',
                    fn(OtpToken $otp): AcmeNotification => new AcmeNotification($otp),
                ),
            );

        $this->encryptor
            ->expects($this->once())
            ->method('encrypt')
            ->with(':otpToken:')
            ->willReturn(':encrypted:');

        $this->repository
            ->expects($this->once())
            ->method('persist')
            ->willReturn(true);

        $token = $this->tokenService->create(1);
        $this->assertInstanceOf(OtpToken::class, $token);
        $this->assertEquals(1, $token->authenticableId());

        $this->assertEquals(':acme:', $token->format());
        $this->assertEquals(':otpToken:', $token->plainText());
        $this->assertEquals(':encrypted:', $token->cipherText());
    }

    /**
     * @test
     */
    public function itSavesTheGivenOtp(): void
    {
        $token = new OtpToken(['expiry_time' => 300]);

        $this->repository
            ->expects($this->once())
            ->method('persist')
            ->with($token)
            ->willReturn(true);

        $this->assertTrue($this->tokenService->save($token));
    }

    /**
     * @test
     */
    public function itExtendsTheValidityOfAToken(): void
    {
        $token = new OtpToken([
            'expiry_time' => 300,
        ]);

        $extendedToken = $this->tokenService->extend($token, 300);

        $this->assertEquals(600, $extendedToken->expiryTime());
    }

    /**
     * @test
     */
    public function itRefreshesTheExpiryTimeOfAToken(): void
    {
        Carbon::setTestNow('2021-11-20 19:20:00');

        $token = new OtpToken([
            'expiry_time' => 300,
        ]);

        Carbon::setTestNow('2021-11-20 19:25:00');

        $refreshedToken = $this->tokenService->refresh($token);

        $this->assertEquals('300', $refreshedToken->timeLeft());
        $this->assertEquals(
            300,
            $token->expiresAt()->diffInSeconds($refreshedToken->expiresAt())
        );
    }

    /**
     * @test
     */
    public function itInvalidatesTheToken(): void
    {
        $token = new OtpToken([
            'expiry_time' => 300,
        ]);

        $invalidatedToken = $this->tokenService->invalidate($token);

        $this->assertFalse($token->expired());
        $this->assertEquals(0, $invalidatedToken->expiryTime());
        $this->assertTrue($invalidatedToken->expired());
    }

    /**
     * @test
     */
    public function itSendsOtpNotifications(): void
    {
        $token = new OtpToken([
            'format' => ':acme:',
        ]);

        $format = new GenericFormat(
            ':acme:',
            fn(): string => ':otpToken:',
            fn(OtpToken $otpToken): AcmeNotification => new AcmeNotification($otpToken),
        );

        $this->manager
            ->expects($this->once())
            ->method('get')
            ->with(':acme:')
            ->willReturn($format);

        $this->notifiable
            ->expects($this->once())
            ->method('notify');

        $this->tokenService->sendOtpNotification($this->notifiable, $token);
    }

    /**
     * @test
     */
    public function itSendsNewOtp(): void
    {
        $format = new GenericFormat(
            'default',
            fn(): string => ':otpToken:',
            fn(OtpToken $otpToken): AcmeNotification => new AcmeNotification($otpToken),
        );

        $this->manager
            ->expects($this->exactly(2))
            ->method('get')
            ->with('default')
            ->willReturn($format);

        $this->notifiable
            ->expects($this->once())
            ->method('getAuthIdentifier')
            ->willReturn(':auth_id:');

        $this->notifiable
            ->expects($this->once())
            ->method('notify');

        $this->tokenService->sendNewOtp($this->notifiable);
    }
}

class Notifiable implements Authenticatable
{
    public function notify(mixed $instance): void
    {
    }

    public function getAuthIdentifierName()
    {
    }

    public function getAuthIdentifier()
    {
    }

    public function getAuthPassword()
    {
    }

    public function getRememberToken()
    {
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
    }
}
