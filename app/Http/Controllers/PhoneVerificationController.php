<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PhoneVerification;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Exception;
use Log;

class PhoneVerificationController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string'
        ]);

        //we have few cases 
        //1 when customer is creating a new account
        // 2 when customer is updating his phone number
        // 3 when customer reset is pin

        if (Customer::where('phone', $validated['phone'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Phone Number is already exists'], 400);
        }

        // Temp 
        return response()->json(['success' => true, 'message' => 'Message sent', 'status' => 'pending']);
        // $result = $this->twilioService->sendOtp($validated['phone']);
        // if ($result['success']) {
        //     return response()->json([
        //         'success' => true,
        //         'message' => $result['message'],
        //         'status' => $result['status']
        //     ]);

        // }

        // return response()->json([
        //     'success' => false,
        //     'message' => $result['message'],
        // ], 400);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'otp_code' => 'required|string'
        ]);

        //we have few cases 
        //1 when customer is creating a new account
        // 2 when customer is updating his phone number
        // 3 when customer reset is pin
        //


        // $result = $this->twilioService->verifyOtp(
        //     $validated['phone'],
        //     $validated['otp_code']
        // );
        // $result['success']

        if (true) {
            $token = base64_encode(random_bytes(16));
            PhoneVerification::create([
                'phone' => $validated['phone'],
                'token' => $token,
                'expires_at' => now()->addMinutes(10)
            ]);




            return response()->json([
                'success' => true,
                'message' => 'Phone verified successfully',
                'verified' => true,
                'verification_token' => $token
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired code',
            'verified' => false
        ], 400);
    }
}
