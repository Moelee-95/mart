<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PhoneVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PhpParser\Builder\Class_;



Route::post('test', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'API connection successful',
        'received_data' => $request->all(),
        'timestamp' => now(),
        'server_status' => 'running'
    ]);
});

// 


// Phone verification routes & Public routes (no authentication required)
Route::post('verify/send-otp', [PhoneVerificationController::class, 'sendOtp']);
Route::post('verify/verify-otp', [PhoneVerificationController::class, 'verifyOtp']);
Route::post('/customers/check-exists', [CustomerController::class, 'checkCustomerExists']);
Route::post('/customers/register', [CustomerController::class, 'store']);
Route::post('/customers/login', [CustomerController::class, 'login']);
Route::post('/customers/forgot_pin', [CustomerController::class, 'forgot_pin']); 

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {

    // Customer routes
    Route::get('/customer/profile', [CustomerController::class, 'profile']);
    Route::put('/customer/profile', [CustomerController::class, 'updateProfile']);
    Route::post('/customers/logout', [CustomerController::class, 'logout']);
    Route::post('/customers/change_pin', [CustomerController::class, 'changePin' ]);

    // Nested Address routes - Professional approach
    Route::apiResource('customers.addresses', AddressController::class)->shallow();

    //     1. Get All Addresses for Customer
    // GET /api/customers/{customer_id}/addresses
    // 2. Create Address for Customer
    // POST /api/customers/{customer_id}/addresses
    // 3. Get Specific Address (Shallow)
    // GET /api/addresses/{address_id}
    // 4. Update Address (Shallow)
    // PUT /api/addresses/{address_id}
    // 5. Delete Address (Shallow)
    // DELETE /api/addresses/{address_id}
    // 6. Set Default Address
    // POST /api/addresses/{address_id}/set-default
    // Additional route for setting default address

    Route::post('addresses/{address}/set-default', [AddressController::class, 'setDefault']);

    // Other customer routes (if needed for admin)
    Route::apiResource('customers', CustomerController::class)->except(['store']);
});