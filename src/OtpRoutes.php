<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Erdemkeren\Otp\Http\Controllers\OtpController;
use Illuminate\Support\Facades\Route;

class OtpRoutes
{
    /**
     * Binds the Passport routes into the controller.
     */
    public static function register(): void
    {
        Route::resource('otp', OtpController::class, [
            'only'       => ['create', 'store'],
            'prefix'     => 'otp',
        ])->middleware(['web', 'auth']);
    }
}
