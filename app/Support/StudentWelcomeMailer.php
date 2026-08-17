<?php

namespace App\Support;

use App\Mail\StudentWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Sends the welcome email for a newly created student account.
 *
 * Shared by the single Add Student form and the bulk upload so both say the same thing.
 * Every method here runs only after the account exists — a failed creation must never
 * produce an email telling someone their account is ready.
 */
class StudentWelcomeMailer
{
    /**
     * Domain the app appends when faculty type a bare username instead of an address.
     * It resolves nowhere, so anything on it is undeliverable and worth saying so
     * rather than handing to the mail server to bounce.
     */
    public const PLACEHOLDER_DOMAIN = '@hms.edu';

    /**
     * A password a student can read off a screen and type without misreading it.
     * No O/0 or I/l, and no symbols, which get mangled when copied out of an email.
     */
    public static function generatePassword(int $length = 12): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }

    /** Whether an address could actually receive mail. */
    public static function isDeliverable(?string $email): bool
    {
        $email = trim((string) $email);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return !Str::endsWith(Str::lower($email), self::PLACEHOLDER_DOMAIN);
    }

    /**
     * Sends the welcome to one student.
     *
     * Never throws: a mail server that is down or refusing must not roll back or
     * abort an import whose accounts were created successfully. The outcome is
     * returned so the caller can report it per student.
     *
     * @return array{sent: bool, reason: string} reason is '' when sent
     */
    public static function send(User $user, string $plainPassword, ?string $className = null, ?string $studentNumber = null): array
    {
        if (!self::isDeliverable($user->email)) {
            // Logged as well as returned. This path used to fail in silence, which
            // left nothing to look at when a student said no email arrived.
            Log::warning('Student welcome email skipped, address not deliverable: ' . var_export($user->email, true));

            return [
                'sent'   => false,
                'reason' => Str::endsWith(Str::lower((string) $user->email), self::PLACEHOLDER_DOMAIN)
                    ? 'no real email address on file — account created, welcome email not sent'
                    : 'invalid email address — account created, welcome email not sent',
            ];
        }

        $displayName = trim((string) ($user->first_name ?: $user->name)) ?: 'there';

        try {
            Mail::to($user->email)->send(new StudentWelcomeMail(
                studentName: $displayName,
                loginEmail: $user->email,
                plainPassword: $plainPassword,
                loginUrl: route('login'),
                className: $className,
                studentNumber: $studentNumber,
            ));

            Log::info('Student welcome email handed to the mail server for ' . $user->email . '.');

            return ['sent' => true, 'reason' => ''];
        } catch (\Throwable $e) {
            // Logged in full, summarised for the screen — an SMTP error runs to
            // several lines and none of it helps whoever is looking at the roster.
            Log::error('Student welcome email failed for ' . $user->email . ': ' . $e->getMessage());

            return [
                'sent'   => false,
                'reason' => 'account created, but the welcome email could not be sent',
            ];
        }
    }
}
