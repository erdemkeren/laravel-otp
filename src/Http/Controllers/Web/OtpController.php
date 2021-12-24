<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Http\Controllers\Web;

use Erdemkeren\Otp\OtpFacade;
use Erdemkeren\Otp\OtpToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

class OtpController
{
    /**
     * * Show the form for the otp submission.
     *
     * @return RedirectResponse|View
     */
    public function create(): RedirectResponse|View
    {
        if (! $this->otpHasBeenRequested()) {
            return redirect('/');
        }

        return view('otp.create');
    }

    /**
     * Store the otp in cookies and redirect user
     * to their original path.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $this->otpHasBeenRequested()) {
            return redirect('/');
        }

        $validator = $this->getOtpSubmissionRequestValidator($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        if (! $token = $this->retrieveOtpTokenByPlainText(
            $request->user(),
            $request->input('password')
        )) {
            $validator->getMessageBag()->add(
                'password',
                'The password is not valid.'
            );

            return redirect()->back()->withErrors($validator);
        }

        if ($token->expired()) {
            $validator->getMessageBag()->add(
                'password',
                'The password is expired.'
            );

            return redirect()->back()->withErrors($validator);
        }

        session()->forget('otp_requested');

        return redirect()
            ->to(session()->pull('otp_redirect_url'))
            ->withCookie(
                cookie()->make('otp_token', (string) $token, $token->expiryTime() / 60)
            );
    }

    /**
     * Validate the given otp submission request.
     *
     * @param Request $request
     *
     * @return ValidatorContract
     */
    private function getOtpSubmissionRequestValidator(Request $request): ValidatorContract
    {
        return ValidatorFacade::make($request->all(), [
            'password' => 'required|string',
        ]);
    }

    /**
     * Retrieve a token by the given user and password.
     *
     * @param Authenticatable $user
     * @param string          $plainText
     *
     * @return mixed
     */
    private function retrieveOtpTokenByPlainText(Authenticatable $user, string $plainText): ?OtpToken
    {
        return OtpFacade::retrieveByPlainText($plainText);
    }

    /**
     * Determine if an otp requested or not.
     *
     * @return mixed
     */
    private function otpHasBeenRequested()
    {
        return session('otp_requested', false);
    }
}
