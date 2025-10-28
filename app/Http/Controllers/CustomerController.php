<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PhoneVerification;
use Illuminate\Http\Request;
use Exception;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers',
            'pin' => 'required|digits:4',
            'verification_token' => 'required|string'
        ]);

        $phoneVerification = PhoneVerification::where('token', $validated['verification_token'])
            ->where('expires_at', '>', now())
            ->where('phone', $validated['phone'])
            ->first();

        if (!$phoneVerification) {
            return response()->json(['success' => false, 'error' => 'Incorrect token or phone number match'], 400);
        }


        $customer = Customer::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'pin' => $validated['pin'],
        ]);
        $token = $customer->createToken('mobile-app')->plainTextToken;
        $phoneVerification->update(['registered' => true]);


        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'customer' => $customer,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function show(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $customer->id,
            'pin' => 'required|digits:4',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'customer' => $customer
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }

    public function checkCustomerExists(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string'
        ]);

        //find customer and if found return true
        $customer = Customer::where('phone', $validated['phone'])->first();
        if ($customer) {
            return response()->json([
                'success' => true,
                'message' => 'Customer exists',
                'phone' => $customer->phone
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Customer does not exist',
            ]);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|digits:4'
        ]);

        $customer = Customer::where('phone', $validated['phone'])->first();
        if ($customer) {
            if ($customer->pin == $validated['pin']) {
                $token = $customer->createToken('mobile-app')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'customer' => $customer,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect PIN'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect phone number'
            ]);
        }
    }

    public function forgot_pin(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|digits:4'
        ]);
        $customer = Customer::where('phone', $validated['phone'])->first();
        if ($customer) {
            if ($customer->pin == $validated['pin']) {
                $token = $customer->createToken('mobile-app')->plainTextToken;
                return response()->json([
                    'success' => true,
                    'message' => 'Pin reset successfully',
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect phone number'
            ]);
        }
    }

    public function updatePhone(Request $request)
    {
        $validated = $request->validate(
            [
                'phone' => 'required|string'
            ]
        );
    }

    public function changePin(Request $request)
    {
        $validated = $request->validate([
            'newPin' => ['required', 'digits:4'],
        ]);

        $user = $request->user();           // get the model (update() returns bool)
        $result = $user->update(['pin' => $validated['newPin']]);

        return response()->json([
            'success' => $result,
        ]);
    }
}


// Log::debug('----------------------------------------------------------------------');
// Log::debug($request['phone']);
// Log::debug('----------------------------------------------------------------------');
