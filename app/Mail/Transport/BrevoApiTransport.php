<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail through Brevo's HTTP API instead of SMTP.
 *
 * Render blocks outbound traffic on ports 25, 465 and 587 for free web services,
 * so a Gmail SMTP connection from the deployed app times out and no student is
 * ever told their password. This talks to Brevo over 443, which is not blocked.
 *
 * Written against the API directly rather than pulling in Brevo's SDK: the app
 * sends one kind of message, and the SDK is a large dependency for one POST.
 */
class BrevoApiTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if ($this->apiKey === '') {
            throw new TransportException('No Brevo API key. Set BREVO_API_KEY.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? null;
        if ($from === null) {
            throw new TransportException('Brevo needs a sender address. Set MAIL_FROM_ADDRESS.');
        }

        $payload = array_filter([
            'sender'      => $this->address($from),
            'to'          => $this->addresses($email->getTo()),
            'cc'          => $this->addresses($email->getCc()),
            'bcc'         => $this->addresses($email->getBcc()),
            'replyTo'     => ($reply = $email->getReplyTo()[0] ?? null) ? $this->address($reply) : null,
            'subject'     => $email->getSubject(),
            'htmlContent' => $email->getHtmlBody(),
            'textContent' => $email->getTextBody(),
            'attachment'  => $this->attachments($email),
        ]);

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'accept'       => 'application/json',
            'content-type' => 'application/json',
        ])->timeout(30)->post(self::ENDPOINT, $payload);

        if ($response->failed()) {
            // Brevo answers a rejection with a JSON body naming the reason — an
            // unverified sender, a spent daily quota. Worth more than the status.
            throw new TransportException(
                'Brevo rejected the message (HTTP ' . $response->status() . '): ' . $response->body()
            );
        }
    }

    /** @param Address[] $addresses */
    private function addresses(array $addresses): ?array
    {
        if ($addresses === []) {
            return null;
        }

        return array_map(fn (Address $address) => $this->address($address), $addresses);
    }

    private function address(Address $address): array
    {
        return array_filter([
            'email' => $address->getAddress(),
            'name'  => $address->getName() ?: null,
        ]);
    }

    private function attachments(Email $email): ?array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'name'    => $attachment->getFilename() ?: 'attachment',
                'content' => base64_encode($attachment->getBody()),
            ];
        }

        return $attachments === [] ? null : $attachments;
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
