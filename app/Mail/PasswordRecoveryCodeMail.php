<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordRecoveryCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $code,
        public int $expiresInMinutes
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Artfy password recovery code');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-recovery-code',
            with: [
                'userName' => $this->userName,
                'code' => $this->code,
                'expiresInMinutes' => $this->expiresInMinutes,
            ],
        );
    }
}
