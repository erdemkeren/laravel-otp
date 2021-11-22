<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Test;

use Carbon\Carbon;
use Erdemkeren\Otp\Contracts\EncryptorContract;
use Erdemkeren\Otp\OtpToken;
use Erdemkeren\Otp\OtpService;
use Erdemkeren\Otp\Contracts\GeneratorManagerContract;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Erdemkeren\Otp\Contracts\TokenRepositoryContract;

class OtpServiceTest extends TestCase
{
    private OtpService $tokenService;

    private MockObject|TokenRepositoryContract $repository;

    private MockObject|EncryptorContract $encryptor;

    private MockObject|GeneratorManagerContract $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(GeneratorManagerContract::class);
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
    public function itCreatesANewPersistedTokenWithTheGivenAuthenticableId(): void
    {
        $this->manager
            ->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn(fn (): string => ':token:');

        $this->encryptor
            ->expects($this->once())
            ->method('encrypt')
            ->with(':token:')
            ->willReturn('encrypted');

        $this->repository
            ->expects($this->once())
            ->method('persist')
            ->willReturn(true);

        $token = $this->tokenService->create(1);
        $this->assertInstanceOf(OtpToken::class, $token);
        $this->assertEquals(1, $token->authenticableId());
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
}
