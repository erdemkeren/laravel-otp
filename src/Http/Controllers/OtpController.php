<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Validator;
use Illuminate\Http\RedirectResponse;
use Erdemkeren\TemporaryAccess\TokenInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Erdemkeren\TemporaryAccess\TemporaryAccessFacade as TemporaryAccess;

/**
 * Class OtpController.
 */
class OtpController extends Controller
{
    /**
     * * Show the form for the otp submission.
     *
     * @param Request $request
     *
     * @return RedirectResponse|View
     */
    public function create(Request $request)
    {
        if (! session('otp_requested', false)) {
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

            redirect()->back()->withErrors($validator);
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
     * @return Validator
     */
    private function getOtpSubmissionRequestValidator(Request $request): Validator
    {
        return ValidatorFacade::make($request->all(), [
            'password' => 'required|string',
        ]);
    }

    /**
     * Retrieve a token by the given user and password.
     *
     * @param Authenticatable $user
     * @param string          $password
     *
     * @return mixed
     */
    private function retrieveOtpTokenByPlainText(Authenticatable $user, string $password): ?TokenInterface
    {
        return TemporaryAccess::retrieveByPlainText($user, $password);
    }
}
