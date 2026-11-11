<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Support\StudentWelcomeMailer;

/**
 * Tells a student their account was set to active or inactive by their faculty.
 */
class AccountStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $status,
        public string $changedBy,
        public string $loginUrl,
    ) {
    }

    /**
     * Mails each user after the response is sent, so a whole block's worth of
     * emails does not hold up the page. Never throws; failures are logged.
     *
     * @param iterable<User> $users
     */
    public static function sendTo(iterable $users, string $status, string $changedBy): void
    {
        $loginUrl = rtrim((string) config('app.url'), '/') . route('login', [], false);
        $recipients = collect($users)
            ->filter(fn (User $u) => StudentWelcomeMailer::isDeliverable($u->email))
            ->map(fn (User $u) => [$u->email, trim((string) ($u->first_name ?: $u->name)) ?: 'there'])
            ->values()
            ->all();

        if (!$recipients) {
            return;
        }

        dispatch(function () use ($recipients, $status, $changedBy, $loginUrl) {
            foreach ($recipients as [$email, $name]) {
                try {
                    Mail::to($email)->send(new self($name, $status, $changedBy, $loginUrl));
                } catch (\Throwable $e) {
                    Log::error('Account status email failed for ' . $email . ': ' . $e->getMessage());
                }
            }
        })->afterResponse();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->status === 'active'
                ? 'Your HMS account is active'
                : 'Your HMS account was set to inactive',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-status',
        );
    }
}
