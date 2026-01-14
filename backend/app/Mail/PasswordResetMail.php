<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $user;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->resetUrl = "http://localhost:3000/reset-password?token=" . $token . "&email=" . urlencode($user->email);
    }

    public function build()
    {
        return $this->subject('Restablecer tu contraseña - Farmacia Online')
                    ->view('emails.password-reset')
                    ->with([
                        'resetUrl' => $this->resetUrl,
                        'user' => $this->user,
                    ]);
    }
}