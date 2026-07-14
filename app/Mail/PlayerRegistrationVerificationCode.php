<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlayerRegistrationVerificationCode extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $verificationCode,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '會員註冊驗證碼',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.player.verification-code',
            with: [
                'verificationCode' => $this->verificationCode,
            ],
        );
    }
}
