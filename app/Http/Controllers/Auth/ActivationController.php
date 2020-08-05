<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Emails\UserActivationEmail;
use Illuminate\Validation\ValidationException;
// use Events\MessageSentToUserPhone;

class ActivationController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function showActivationForm(Request $request)
    {
        $user = $this->validateUserEmail($request);
        $resendUrl = route('account.resend.activation.code', $user->id);

        if (!$request->session()->has('status')) {
            $e = $request->e;
            $user = User::whereEmail(base64url_decode($e))->firstOrFail();
            $phone = $user->phone;

            Mail::to($user)->send(new UserActivationEmail($user));
            session()->flash('status', trans('auth::activation.send_activation_code_to_email_successed'));
        }

        if ($user->phone && $request->type == 'message') {
            $resendUrl = route('account.resend.to.phone.activation.code', $user->id);
        }

        return view('auth.activation', compact('user', 'resendUrl'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function sendActivationCodeForm(Request $request)
    {
        $e = $request->e;
        $phone = User::whereEmail(base64url_decode($e))->first()->phone;

        return view('auth.activation-send-activation-code-form', compact('e', 'phone'));
    }

    /**
     * Activate user from link.
     *
     * @return \Illuminate\Http\RedirectResponse|static
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function activateUserFromLinks(Request $request)
    {
        $data = $this->validate($request, [
            'act' => 'required',
            'e' => 'required'
        ]);

        $user = $this->validateUserEmail($request);

        if ($this->attemptActivation($user, base64url_decode($data['act']))) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedActivationResponse($request);
    }

    /**
     * Activate user.
     *
     * @return \Illuminate\Http\RedirectResponse|static
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function activate(Request $request)
    {
        $data = $this->validate($request, ['act_code' => 'required']);

        $user = $this->validateUserEmail($request);

        if ($this->attemptActivation($user, $data['act_code'])) {
            return $this->sendLoginResponse($request);
        }
        
        return $this->sendFailedActivationResponse($request);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function success(Request $request)
    {
        return view('auth.activation-success');
    }

    /**
     * @param  \Modules\Auth\Models\User  $user
     * @param  string  $activationCode
     * @return boolean
     */
    protected function attemptActivation($user, $activationCode)
    {
        if ($user->getActivationCode() != $activationCode) {
            return false;
        }

        $user->markEmailAsVerified();

        Auth::guard()->login($user);

        return true;
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return redirect(route('account.activation.success'));
    }

    /**
     * Send failed activation.
     *
     * @return static
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedActivationResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'act_code' => [trans('auth::activation.activation_code_wrong')],
        ]);
    }

    /**
     * @return User
     */
    protected function validateUserEmail(Request $request)
    {
        $user = User::whereEmail(base64url_decode($request->e))->first();

        if (! $user) {
            abort(redirect()->route('login'));
        }

        if ($user->isActivated()) {
            abort(redirect('/'));
        }

        return $user;
    }

    /**
     * Send activation code to user.
     *
     * @param  string  $e
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendActivationCode($e)
    {
        $user = User::whereEmail(base64url_decode($e))->firstOrFail();

        Mail::to($user)->send(new UserActivationEmail($user));

        session()->flash('status', trans('auth::activation.send_activation_code_to_email_successed'));

        return redirect(route('account.activation', ['e' => $e]));
    }

    /**
     * Send activation code to user phone.
     *
     * @param  string  $e
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendActivationCodeToUserPhone($e)
    {
        $user = User::whereEmail(base64url_decode($e))->firstOrFail();

        $message = trans('auth::sms_messages.otp_message', [
            'code' => $user->activation_code,
        ]);
        $type = "otp";

        $phones = create_array_for_phone($user->phone);
        // $response = event(new MessageSentToUserPhone($user, $message, $type, $phones['country_id']));

        session()->flash('status', trans('auth::activation.send_activation_code_to_phone_successed'));

        return redirect(route('account.activation', [
            'e' => $e,
            'type' => 'message',
        ]));
    }

    /**
     * Resend user's account with activation code.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(User $user)
    {
        Mail::to($user)->send(new UserActivationEmail($user));
        session()->flash('status', trans('auth::activation.send_activation_code_to_email_successed'));

        return response()->json([
            'message' => trans('auth::activation.send_activation_code_to_email_successed'),
            'success' => true,
        ], 200);
    }

    /**
     * Resend user's account with activation code to user phone.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendActivationCodeToUserPhone(User $user)
    {
        $message = trans('auth::sms_messages.otp_message', [
            'code' => $user->activation_code,
        ]);
        $type = "otp";

        $phones = create_array_for_phone($user->phone);
        // event(new MessageSentToUserPhone($user, $message, $type, $phones['country_id']));
        session()->flash('status', trans('auth::activation.send_activation_code_to_phone_successed'));

        return response()->json([
            'message' => trans('auth::activation.send_activation_code_to_phone_successed'),
            'success' => true,
        ], 200);
    }
}