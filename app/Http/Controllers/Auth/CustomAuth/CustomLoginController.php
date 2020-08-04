<?php

namespace App\Http\Controllers\Auth\CustomAuth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class CustomLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CustomLoginController
    |--------------------------------------------------------------------------
    |
    | require to use `Illuminate\Foundation\Auth\AuthenticatesUsers` to work properly
    |
    */

    use AuthenticatesUsers;


    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);

            return;
        }

        if ($this->userNotVerified($request->login, $request->password)) {
            $user = User::where(function($user) use ($request) {
                $user->where('email', $request->login)
                    ->orWhere('username', $request->login);
            })->firstOrFail();

            $e = base64url_encode($user->email);

            // Mail::to($user)->send(new UserActivationEmail($user));

            return redirect(route('account.activation', [
                'e' => $e,
            ]));
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse();
    }

    /**
     * Get the failed login response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse()
    {
        throw ValidationException::withMessages([
            $this->username() => ['failed' => __('Email atau kata sandi yang Anda masukkan salah.')],
        ]);
    }

    /**
     * Check user verified status.
     *
     * @param  string  $login
     * @param  string  $password
     * @return bool
     */
    public function userNotVerified($login, $password)
    {
        $user = User::where(function($user) use ($login) {
            $user->where('email', $login)
                ->orWhere('username', $login);
        })->first();

        if (! $user) {
            return false; // user not found leave the response to sendFailedLoginResponse
        }

        if (! Hash::check($password, $user->password)) {
            abort(
                redirect(route('login'))->withErrors([
                    'failed' => __('Email atau kata sandi yang Anda masukkan salah.'),
                ])
            );
        }

        return $user->email_verified_at == null;
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        $login = request()->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $login]);

        return $field;
    }
}
