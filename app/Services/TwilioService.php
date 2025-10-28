<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;

class TwilioService
{
    protected $client;
    protected $verifySid;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->verifySid = config('services.twilio.verify_sid');
    }

    public function sendOtp($phoneNumber)
    {
        try {
            $verification = $this->client->verify->v2
                ->services($this->verifySid)
                ->verifications
                ->create($phoneNumber, "sms");

            return [
                'success' => true,
                'sid' => $verification->sid,
                'status' => $verification->status
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function verifyOtp($phoneNumber, $code)
    {
        try {
            $verificationCheck = $this->client->verify->v2
                ->services($this->verifySid)
                ->verificationChecks
                ->create([
                    'to' => $phoneNumber,
                    'code' => $code
                ]);

            return [
                'success' => $verificationCheck->status === 'approved',
                'status' => $verificationCheck->status
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}