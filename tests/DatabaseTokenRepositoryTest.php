<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Test;

use Carbon\Carbon;
use Erdemkeren\Otp\Repositories\DatabaseTokenRepository;
use Erdemkeren\Otp\OtpToken;
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
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callable) => $callable());

        $token = new OtpToken([
            'plain_text' => ':plain_text:',
            'cipher_text' => ':cipher_text:',
            'authenticable_id' => ':authenticable_id:',
            'expiry_time' => 300,
        ]);

        $this->assertTrue($this->repository->persist($token));
    }
}
