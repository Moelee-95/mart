<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    /**
     * Display a listing of addresses for a specific customer.
     * GET /customers/{customer}/addresses
     */
    public function index(Request $request, Customer $customer): JsonResponse
    {
        // Ensure customer can only access their own addresses
        if ($customer->id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $addresses = $customer->addresses()
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'addresses' => $addresses,
                'total' => $addresses->count()
            ]
        ]);
    }

    /**
     * Store a newly created address for a specific customer.
     * POST /customers/{customer}/addresses
     */
    public function store(Request $request, Customer $customer): JsonResponse
    {
        // Ensure customer can only create addresses for themselves
        if ($customer->id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validated = $this->validateAddressData($request);

        Log::debug('--------------------------------------------------------------------------------------------');
        Log::debug(json_encode($validated));
        Log::debug('--------------------------------------------------------------------------------------------');


        // Handle default address logic
        if ($validated['is_default'] ?? false) {
            $customer->addresses()->update(['is_default' => false]);
        }

        // If this is the first address, make it default
        if ($customer->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        $address = $customer->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => [
                'address' => $address
            ]
        ], 201);
    }

    /**
     * Display the specified address.
     * GET /addresses/{address} (shallow route)
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        // Check ownership
        if ($address->customer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found or unauthorized'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'address' => $address
            ]
        ]);
    }

    /**
     * Update the specified address.
     * PUT /addresses/{address} (shallow route)
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        // Check ownership
        if ($address->customer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found or unauthorized'
            ], 404);
        }

        $validated = $this->validateAddressData($request);

        // Handle default address logic
        if ($validated['is_default'] ?? false) {
            Address::where('customer_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => [
                'address' => $address->fresh()
            ]
        ]);
    }

    /**
     * Remove the specified address (soft delete).
     * DELETE /addresses/{address} (shallow route)
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        // Check ownership
        if ($address->customer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found or unauthorized'
            ], 404);
        }

        // Don't allow deleting if it's the only address
        $addressCount = Address::where('customer_id', $request->user()->id)
            ->where('is_active', true)
            ->count();

        if ($addressCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only address. Please add another address first.'
            ], 422);
        }

        // If deleting default address, set another as default
        if ($address->is_default) {
            $newDefault = Address::where('customer_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->where('is_active', true)
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        // Soft delete
        $address->update(['is_active' => false]);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    /**
     * Set an address as default.
     * POST /addresses/{address}/set-default (additional route)
     */
    public function setDefault(Request $request, Address $address): JsonResponse
    {
        // Check ownership
        if ($address->customer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found or unauthorized'
            ], 404);
        }

        // Remove default from all other addresses
        Address::where('customer_id', $request->user()->id)
            ->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => [
                'address' => $address->fresh()
            ]
        ]);
    }

    /**
     * Validate address data
     */
    private function validateAddressData(Request $request): array
    {
        return $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'area' => 'required|string|max:255',
            'type' => [
                'required',
                'string',
                Rule::in(['home', 'work', 'office', 'other'])
            ],
            'building_name' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:10',
            'apartment_number' => 'nullable|string|max:10',
            'nearby_landmark' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20|regex:/^[\+]?[\d\s\-\(\)]+$/',
            'additional_directions' => 'nullable|string|max:500',
            'label' => 'nullable|string|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean'
        ]);
    }
}