<?php

namespace App\Service;



use Twilio\Rest\Client;

class TwilioService
{
    private $sid;
    private $token;
    private $from;

    public function __construct(string $sid, string $token, string $from)
    {
        $this->sid = $sid;
        $this->token = $token;
        $this->from = $from;
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            $client = new Client($this->sid, $this->token);
            $client->messages->create(
                $to, 
                [
                    'from' => $this->from,
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            // Log the error or handle it appropriately
            return false;
        }
    }
}
