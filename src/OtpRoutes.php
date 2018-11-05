<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Illuminate\Support\Facades\Route;
use Erdemkeren\TemporaryAccess\Http\Controllers\OtpController;

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
        ])->middleware('auth');
    }
}
