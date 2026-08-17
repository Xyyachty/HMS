<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The welcome a student gets once their HMS account exists.
 *
 * Carries the sign-in password in the body, so it is only ever built after the account
 * has actually been created — a student who was never created must not be told they
 * were. The plain password is passed in rather than read off the user, because the
 * column holds a bcrypt hash and this is the one moment the readable value exists.
 */
class StudentWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $loginEmail,
        public string $plainPassword,
        public string $loginUrl,
        public ?string $className = null,
        public ?string $studentNumber = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your HMS account is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-welcome',
        );
    }
}
