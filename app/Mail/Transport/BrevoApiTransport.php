<?php

namespace App\Mail\Transport;

use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestCcItem;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestBccItem;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestReplyTo;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestAttachmentItem;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
    protected Brevo $client;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        
        $this->client = new Brevo($apiKey);
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        $requestData = [];
        
        // Set sender
        $from = $email->getFrom()[0] ?? null;
        if ($from) {
            $requestData['sender'] = new SendTransacEmailRequestSender([
                'email' => $from->getAddress(),
                'name' => $from->getName() ?: null,
            ]);
        }
        
        // Set recipients
        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = new SendTransacEmailRequestToItem([
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ]);
        }
        $requestData['to'] = $to;
        
        // Set CC
        if ($email->getCc()) {
            $cc = [];
            foreach ($email->getCc() as $address) {
                $cc[] = new SendTransacEmailRequestCcItem([
                    'email' => $address->getAddress(),
                    'name' => $address->getName() ?: null,
                ]);
            }
            $requestData['cc'] = $cc;
        }
        
        // Set BCC
        if ($email->getBcc()) {
            $bcc = [];
            foreach ($email->getBcc() as $address) {
                $bcc[] = new SendTransacEmailRequestBccItem([
                    'email' => $address->getAddress(),
                    'name' => $address->getName() ?: null,
                ]);
            }
            $requestData['bcc'] = $bcc;
        }
        
        // Set subject
        $requestData['subject'] = $email->getSubject();
        
        // Set content
        if ($email->getHtmlBody()) {
            $requestData['htmlContent'] = $email->getHtmlBody();
        }
        
        if ($email->getTextBody()) {
            $requestData['textContent'] = $email->getTextBody();
        }
        
        // Set reply-to
        if ($email->getReplyTo()) {
            $replyTo = $email->getReplyTo()[0];
            $requestData['replyTo'] = new SendTransacEmailRequestReplyTo([
                'email' => $replyTo->getAddress(),
                'name' => $replyTo->getName() ?: null,
            ]);
        }
        
        // Handle attachments
        if ($email->getAttachments()) {
            $attachments = [];
            foreach ($email->getAttachments() as $attachment) {
                $attachments[] = new SendTransacEmailRequestAttachmentItem([
                    'content' => base64_encode($attachment->getBody()),
                    'name' => $attachment->getFilename() ?: $attachment->getName(),
                ]);
            }
            $requestData['attachment'] = $attachments;
        }
        
        $request = new SendTransacEmailRequest($requestData);
        
        // Send email
        try {
            $result = $this->client->transactionalEmails->sendTransacEmail($request);
            \Log::info('Brevo email sent successfully', [
                'message_id' => $result->messageId ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Brevo email failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }
}
