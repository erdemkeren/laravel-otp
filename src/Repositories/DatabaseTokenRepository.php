<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Repositories;

use Erdemkeren\Otp\Contracts\TokenRepositoryContract;
use Erdemkeren\Otp\OtpToken;
use Illuminate\Support\Facades\DB;

class DatabaseTokenRepository implements TokenRepositoryContract
{
    public function retrieveByCipherText(string $cipherText): ?OtpToken
    {
        $result = DB::table('otp_tokens')->where('cipher_text', $cipherText)->first();

        return $result ? $this->createOtpToken($result) : null;
    }

    public function persist(OtpToken $token): bool
    {
        return DB::transaction(
            fn (): bool => DB::table('otp_tokens')->updateOrInsert([
                'authenticable_id' => $token->authenticableId(),
                'cipher_text'      => $token->cipherText(),
            ], $token->withoutPlainText()->toArray())
        );
    }

    private function createOtpToken(object $result): OtpToken
    {
        return new OtpToken([
            'authenticable_id' => $result->authenticable_id,
            'cipher_text' => $result->cipher_text,
            'format' => $result->format,
            'expiry_time' => $result->expiry_time,
            'created_at' => $result->created_at,
            'updated_at' => $result->updated_at,
        ]);
    }
}
