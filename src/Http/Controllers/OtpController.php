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
use Illuminate\Support\Facades\Validator;
use Erdemkeren\TemporaryAccess\TokenInterface;
use Illuminate\Contracts\Auth\Authenticatable;
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
     * @return View
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $token = TemporaryAccess::create($user, 6);

        $user->notify($token->toNotification());

        return view('otp.create', $request->only(['redirect_path']));
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
        $validator = $this->validateOtpSubmissionRequest($request);

        if (! $token = $this->retrieveOtpTokenByPlainText($request)) {
            $validator->getMessageBag()->add(
                'password',
                'The password is not valid.'
            );

            redirect()->back()->withErrors($validator);
        }

        if ($token->expired()) {
            $validator->getMessageBag()->add(
                'password',
                'The password is expired.'
            );

            redirect()->back()->withErrors($validator);
        }

        return redirect()
            ->to($request->input('redirect_path'))
            ->withCookies(['otp_token' => (string) $token]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateOtpSubmissionRequest(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            redirect()->back()->withErrors($validator);
        }

        return $validator;
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
