<?php

namespace App\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserActivationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var \App\User */
    protected $user;

    public $subject = "Aktivasi Akun HolaHalo";

    /**
     * Create a new message instance.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@holahalo.com')
            ->view('auth.emails.activation_code')
            ->with([
                'userName' => $this->user->name,
                'activationCode' => $this->user->getActivationCode(),
                'encodedEmail' => base64url_encode($this->user->email),
                'secondUrl' => route('account.activation.from.links', [
                        'act' => base64url_encode($this->user->getActivationCode()),
                        'e' => base64url_encode($this->user->email)
                    ])
            ]);
    }
}
