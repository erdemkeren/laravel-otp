<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Test;

use Erdemkeren\Otp\Contracts\FormatContract;
use Erdemkeren\Otp\Exceptions\UnknownOtpFormat;
use Erdemkeren\Otp\FormatManager;
use Erdemkeren\Otp\GenericFormat;
use Erdemkeren\Otp\OtpToken;
use Erdemkeren\Otp\Test\TestFormat\AcmeNotification;
use PHPUnit\Framework\TestCase;

class FormatManagerTest extends TestCase
{
    private FormatManager $manager;

    private FormatContract $format;

    protected function setUp(): void
    {
        parent::setUp();

        $this->format = new GenericFormat(
            ':acme:',
            fn (): string => ':otpToken:',
            fn (OtpToken $token): AcmeNotification => new AcmeNotification($token),
        );

        $this->manager = tap(
            new FormatManager(':acme:'),
            fn (FormatManager $m) => $m->register($this->format),
        );
    }

    /**
     * @test
     */
    public function itReturnsTheRequestedFormat(): void
    {
        $this->assertEquals($this->format, $this->manager->get(':acme:'));
    }

    /**
     * @test
     */
    public function itReturnsTheDefaultFormat(): void
    {
        $this->assertEquals(
            $this->format,
            $this->manager->get('default'),
        );
    }

    /**
     * @test
     */
    public function itCanRegisterCustomFormats(): void
    {
        $this->manager->register($customFormat = new GenericFormat(
            ':custom:',
            fn (): string => ':otpToken:',
            fn ($otp): AcmeNotification => new AcmeNotification($otp),
        ));

        $this->assertEquals($customFormat, $this->manager->get(':custom:'));
    }

    /**
     * @test
     */
    public function itThrowsExceptionIfTheRequestedFormatIsUnknown(): void
    {
        $this->expectException(UnknownOtpFormat::class);

        $this->manager->get(':unknown:');
    }
}
