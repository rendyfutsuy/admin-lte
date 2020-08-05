<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\CustomAuth\CustomLogin;

class LoginController extends CustomLogin
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
        $this->setUser(request());
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
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
