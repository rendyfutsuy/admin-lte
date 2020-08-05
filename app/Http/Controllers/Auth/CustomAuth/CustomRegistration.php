<?php

namespace App\Http\Controllers\Auth\CustomAuth;

use App\User;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\ValidationException;

abstract class CustomRegistration extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CustomRegistration
    |--------------------------------------------------------------------------
    |
    | need Illuminate\Foundation\Auth\RegistersUsers to work properly.
    | Serves as base to execute Registration for Users.
    |
    */

    use RegistersUsers;

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_id' => ['sometimes', 'nullable'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('level', User::REGISTERED);
                }),
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        if (request()->has('user_id')) {
            return $this->updateExistedUser($data);
        }

        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'level' => User::REGISTERED,
            'activation_code' => $this->generateActivationCode(Config::get('auth.code_generate.length', 0)),
            'banned_at' => null,
            'email_verified_at' => null,
        ]);
    }

    protected function generateActivationCode(int $digit = 6): int
    {
        return rand((int)str_repeat('1', $digit), (int)str_repeat('9', $digit));

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function updateExistedUser(array $data)
    {
        $user = User::findOrFail(request()->get('user_id'));

        if ($user->level != User::GUEST) {
            $this->sendFailedLoginResponse();
        }

        $user->update([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'level' => User::REGISTERED,
            'activation_code' => $this->generateActivationCode(Config::get('auth.code_generate.length', 0)),
            'banned_at' => null,
            'email_verified_at' => null,
        ]);

        return $user;
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
            'email' => ['failed' => __('ada kesalahan saat and mendaftar user.')],
        ]);
    }
}
