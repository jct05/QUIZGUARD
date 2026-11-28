<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromList = $email->getFrom();
        $fromAddr = !empty($fromList) ? $fromList[0]->getAddress() : (string) config('mail.from.address');
        $fromName = !empty($fromList) && $fromList[0]->getName() !== ''
            ? $fromList[0]->getName()
            : (string) config('mail.from.name', 'DWCC QuizGuard');

        $payload = [
            'sender' => [
                'email' => $fromAddr,
                'name'  => $fromName,
            ],
            'to' => array_map(
                fn($addr) => array_filter([
                    'email' => $addr->getAddress(),
                    'name'  => $addr->getName() ?: null,
                ]),
                $email->getTo()
            ),
            'subject' => (string) ($email->getSubject() ?? ''),
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = array_map(fn($a) => ['email' => $a->getAddress()], $cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = array_map(fn($a) => ['email' => $a->getAddress()], $bcc);
        }

        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        if (is_string($html) && $html !== '') {
            $payload['htmlContent'] = $html;
        }
        if (is_string($text) && $text !== '') {
            $payload['textContent'] = $text;
        }
        if (!isset($payload['htmlContent']) && !isset($payload['textContent'])) {
            $payload['textContent'] = ' ';
        }

        $attachments = $email->getAttachments();
        if (!empty($attachments)) {
            $payload['attachment'] = array_map(fn($att) => [
                'name'    => $att->getFilename() ?: 'attachment',
                'content' => base64_encode($att->getBody()),
            ], $attachments);
        }

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'accept'       => 'application/json',
            'content-type' => 'application/json',
        ])->timeout(15)->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Brevo API error (' . $response->status() . '): ' . $response->body()
            );
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
