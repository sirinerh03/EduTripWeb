<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $twilio;
    private $from;

    public function __construct(string $sid, string $token, string $from)
    {
        $this->twilio = new Client($sid, $token);
        $this->from = $from;
    }

    public function sendSms(string $to, string $message): void
    {
        $this->twilio->messages->create(
            $to,
            [
                'from' => $this->from,
                'body' => $message
            ]
        );
    }
}