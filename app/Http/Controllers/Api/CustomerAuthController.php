<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Actions\Api\GetCustomerProfileAction;
use Modules\Customer\Actions\Api\LoginAction;
use Modules\Customer\Actions\Api\LogoutAction;
use Modules\Customer\Actions\Api\UpdateProfileAction;
use Modules\Customer\Http\Requests\Api\V1\Customer\LoginRequest;
use Modules\Customer\Http\Requests\Api\V1\Customer\UpdateProfileRequest;

class CustomerAuthController extends Controller
{
    /**
     * Login customer with email or phone
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json($result);
    }

    /**
     * Logout customer
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return response()->json($result);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request, LogoutAction $action): JsonResponse
    {
        $result = $action->logoutAll($request->user());

        return response()->json($result);
    }

    /**
     * Get current customer profile
     */
    public function me(Request $request, GetCustomerProfileAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return response()->json($result);
    }

    /**
     * Update current customer profile
     */
    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            $request->validated(),
            $request->file('avatar')
        );

        return response()->json($result);
    }
}
