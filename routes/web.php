<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('account/activation', 'Auth\ActivationController@showActivationForm')
    ->name('account.activation');

Route::post('account/activation', 'Auth\ActivationController@activate')
    ->name('account.activation');

Route::get('account/activation-from-links', 'Auth\ActivationController@activateUserFromLinks')
    ->name('account.activation.from.links');

Route::get('account/send-activation-form', 'Auth\ActivationController@sendActivationCodeForm')
    ->name('account.send.activation.code.form');

Route::post('account/send/{e}', 'Auth\ActivationController@sendActivationCode')
    ->name('account.send.activation.code');

Route::get('account/resend/{user}', 'Auth\ActivationController@resend')
    ->name('account.resend.activation.code');

Route::post('account/send-to-phone/{e}', 'Auth\ActivationController@sendActivationCodeToUserPhone')
    ->name('account.send.to.phone.activation.code');

Route::get('account/resend-to-phone/{user}', 'Auth\ActivationController@resendActivationCodeToUserPhone')
    ->name('account.resend.to.phone.activation.code');

Route::post('validate/activation-code', 'Auth\ValidateCodeActivation')
    ->name('account.validate.activation.code');

Route::get('account/success', 'Auth\ActivationController@success')
    ->name('account.activation.success');


Route::get('/home', 'HomeController@index')->name('home');
