<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Routes;

use Illuminate\Support\Facades\Route;
use Erdemkeren\Otp\Http\Controllers\Web\OtpController;

class WebRoutes
{
    public static function register(): void
    {
        Route::resource('otp', OtpController::class, [
            'only'       => ['create', 'store'],
            'prefix'     => 'otp',
        ])->middleware(['web', 'auth']);
    }
}
