<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Customer\Http\Resources\CustomerResource;
use Modules\Customer\Models\Customer;
use Modules\Customer\Services\CustomerService;

class CustomerStatusApiController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Activate a customer.
     */
    public function activate(Customer $customer): JsonResponse
    {
        $customer = $this->customerService->activate($customer);

        return response()->json([
            'message' => 'Customer activated successfully.',
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Deactivate a customer.
     */
    public function deactivate(Customer $customer): JsonResponse
    {
        $customer = $this->customerService->deactivate($customer);

        return response()->json([
            'message' => 'Customer deactivated successfully.',
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Suspend a customer.
     */
    public function suspend(Customer $customer): JsonResponse
    {
        $customer = $this->customerService->suspend($customer);

        return response()->json([
            'message' => 'Customer suspended successfully.',
            'data' => new CustomerResource($customer),
        ]);
    }
}
