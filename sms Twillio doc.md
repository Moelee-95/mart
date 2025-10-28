# Step 1: Install Twilio SDK
composer require twilio/sdk
# Step 2: Add Twilio Config to .env
envTWILIO_SID=ACea4827a6afb2dfbc0c1946fc6dbc6bd0
TWILIO_TOKEN=6c5349d0e93213770f993bda2aa88bd2
TWILIO_VERIFY_SID=VA0a8e268429fe3e58d8e6b4bcf7f79496
# Step 3: Create Twilio Service Class
php artisan make:class Services/TwilioService
app/Services/TwilioService.php:
php<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;

# class TwilioService
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
# Step 4: Add Twilio Config to config/services.php
php// config/services.php
return [
    // ... other services
    
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'verify_sid' => env('TWILIO_VERIFY_SID'),
    ],
];

# Twillio 

Account SID
ACea4827a6afb2dfbc0c1946fc6dbc6bd0

Auth Token
6c5349d0e93213770f993bda2aa88bd2
