<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Erdemkeren\TemporaryAccess\TemporaryAccessFacade as TemporaryAccess;

class OtpController extends Controller
{
    public function create(Request $request): View
    {
        return view('otp.create', $request->only(['redirect_path']));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $token = TemporaryAccess::retrieveByPlainText(
            $request->user(),
            $request->input('password')
        )) {
            $validator->getMessageBag()->add('password', 'Password wrong');

            redirect()->back()->withErrors();
        }

        return redirect()
            ->to($request->input('redirect_path'))
            ->withCookies(['otp_token' => (string) $token]);
    }
}
