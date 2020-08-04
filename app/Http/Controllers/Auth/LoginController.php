<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\CustomAuth\CustomLoginController;

class LoginController extends CustomLoginController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @return string|\Illuminate\Http\Request
     */
    public function redirectTo()
    {
        if (request()->has('redirect')) {
            return request('redirect');
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}
