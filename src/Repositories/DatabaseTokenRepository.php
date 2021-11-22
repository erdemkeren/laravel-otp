<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Repositories;

use Erdemkeren\Otp\Contracts\TokenRepositoryContract;
use Erdemkeren\Otp\OtpToken;
use Illuminate\Support\Facades\DB;

/**
 * Class DatabaseTokenRepository.
 */
class DatabaseTokenRepository implements TokenRepositoryContract
{
    /**
     * Save the given token in the storage.
     *
     * @param  OtpToken  $token
     * @return bool
     */
    public function persist(OtpToken $token): bool
    {
        return DB::transaction(
            fn (): bool => DB::table('otp_tokens')->updateOrInsert([
                'authenticable_id' => $token->authenticableId(),
                'cipher_text'      => $token->cipherText(),
            ], $token->withoutPlainText()->toArray())
        );
    }
}
