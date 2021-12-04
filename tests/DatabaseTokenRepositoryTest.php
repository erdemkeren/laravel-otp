<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Test;

use Carbon\Carbon;
use Erdemkeren\Otp\OtpToken;
use Erdemkeren\Otp\Repositories\DatabaseTokenRepository;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class DatabaseTokenRepositoryTest extends TestCase
{
    private DatabaseTokenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DatabaseTokenRepository();
    }

    /**
     * @test
     */
    public function itRetrievesTheOtpTokenByTheGivenCipherText(): void
    {
        DB::shouldReceive('table')->with('otp_tokens')->once()->andReturnSelf();
        DB::shouldReceive('where')
            ->with('cipher_text', ':cipher_text:')
            ->once()
            ->andReturnSelf();
        DB::shouldReceive('first')
            ->andReturn((object) [
                'authenticable_id' => ':authenticable_id:',
                'cipher_text' => ':cipher_text:',
                'format' => ':format:',
                'expiry_time' => 30,
                'created_at' => '2022-01-01 00:00:00',
                'updated_at' => '2022-01-02 00:00:00',
            ]);

        $this->assertInstanceOf(
            OtpToken::class,
            $otpToken = $this->repository->retrieveByCipherText(':cipher_text:'),
        );

        $this->assertEquals(':authenticable_id:', $otpToken->authenticableId());
        $this->assertNull($otpToken->plainText());
        $this->assertEquals(':cipher_text:', $otpToken->cipherText());
        $this->assertEquals(':format:', $otpToken->format());
        $this->assertEquals(30, $otpToken->expiryTime());
        $this->assertEquals(
            '2022-01-01 00:00:00',
            $otpToken->createdAt()->format('Y-m-d H:i:s'),
        );
        $this->assertEquals(
            '2022-01-02 00:00:00',
            $otpToken->updatedAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @test
     */
    public function itPersistsTheGivenToken(): void
    {
        Carbon::setTestNow(new Carbon('2018-11-06 00:00:00'));

        DB::shouldReceive('table')->with('otp_tokens')->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')
            ->once()
            ->with([
                'authenticable_id' => ':authenticable_id:',
                'cipher_text'      => ':cipher_text:',
            ], [
                'authenticable_id' => ':authenticable_id:',
                'cipher_text'      => ':cipher_text:',
                'expiry_time'      => 300,
                'created_at'       => '2018-11-06 00:00:00',
                'updated_at'       => '2018-11-06 00:00:00',
            ])->andReturn(true);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callable) => $callable());

        $token = new OtpToken([
            'plain_text' => ':plain_text:',
            'cipher_text' => ':cipher_text:',
            'authenticable_id' => ':authenticable_id:',
            'expiry_time' => 300,
        ]);

        $this->assertTrue($this->repository->persist($token));
    }
}
