<?php

namespace Modules\Customer\Http\Controllers\Api\V1\Shipping;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Models\CustomerShipping;
use Modules\Customer\Http\Requests\Api\V1\Shipping\StoreShippingRequest;
use Modules\Customer\Http\Requests\Api\V1\Shipping\UpdateShippingRequest;
use Modules\Customer\Http\Resources\CustomerShippingResource;

class CustomerShippingController extends Controller
{
    /**
     * Display a listing of the customer's shipping addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        $addresses = CustomerShipping::forCustomer($customer->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Shipping addresses retrieved successfully',
            'data' => CustomerShippingResource::collection($addresses),
        ]);
    }

    /**
     * Store a newly created shipping address.
     */
    public function store(StoreShippingRequest $request): JsonResponse
    {
        $customer = $request->user();

        $data = $request->validated();
        $data['customer_id'] = $customer->id;

        // If this is the first address, make it default
        $hasAddresses = CustomerShipping::forCustomer($customer->id)->exists();
        if (!$hasAddresses) {
            $data['is_default'] = true;
        }

        $address = CustomerShipping::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipping address created successfully',
            'data' => new CustomerShippingResource($address),
        ], 201);
    }

    /**
     * Display the specified shipping address.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $customer = $request->user();

        $address = CustomerShipping::forCustomer($customer->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Shipping address retrieved successfully',
            'data' => new CustomerShippingResource($address),
        ]);
    }

    /**
     * Update the specified shipping address.
     */
    public function update(UpdateShippingRequest $request, string $uuid): JsonResponse
    {
        $customer = $request->user();

        $address = CustomerShipping::forCustomer($customer->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $address->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Shipping address updated successfully',
            'data' => new CustomerShippingResource($address->fresh()),
        ]);
    }

    /**
     * Remove the specified shipping address.
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $customer = $request->user();

        $address = CustomerShipping::forCustomer($customer->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $wasDefault = $address->is_default;

        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $nextAddress = CustomerShipping::forCustomer($customer->id)
                ->orderByDesc('created_at')
                ->first();

            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Shipping address deleted successfully',
        ]);
    }

    /**
     * Set address as default.
     */
    public function setDefault(Request $request, string $uuid): JsonResponse
    {
        $customer = $request->user();

        $address = CustomerShipping::forCustomer($customer->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address set successfully',
            'data' => new CustomerShippingResource($address->fresh()),
        ]);
    }

    /**
     * Get the default shipping address.
     */
    public function getDefault(Request $request): JsonResponse
    {
        $customer = $request->user();

        $address = CustomerShipping::forCustomer($customer->id)
            ->default()
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'No default address found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Default address retrieved successfully',
            'data' => new CustomerShippingResource($address),
        ]);
    }
}
