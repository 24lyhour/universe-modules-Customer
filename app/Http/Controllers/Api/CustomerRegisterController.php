<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Customer\Actions\Api\CheckAvailabilityAction;
use Modules\Customer\Actions\Api\RegisterAction;
use Modules\Customer\Actions\Api\SendOtpAction;
use Modules\Customer\Actions\Api\VerifyOtpAction;
use Modules\Customer\Http\Requests\Api\V1\Customer\CheckEmailRequest;
use Modules\Customer\Http\Requests\Api\V1\Customer\CheckPhoneRequest;
use Modules\Customer\Http\Requests\Api\V1\Customer\RegisterRequest;
use Modules\Customer\Http\Requests\Api\V1\Customer\SendOtpRequest;
use Modules\Customer\Http\Requests\Api\V1\Customer\VerifyOtpRequest;

class CustomerRegisterController extends Controller
{
    /**
     * Register new customer
     */
    public function register(RegisterRequest $request, RegisterAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->validated(),
            $request->file('avatar')
        );

        return response()->json($result, 201);
    }

    /**
     * Check if email is available
     */
    public function checkEmail(CheckEmailRequest $request, CheckAvailabilityAction $action): JsonResponse
    {
        $result = $action->checkEmail($request->email);

        return response()->json($result);
    }

    /**
     * Check if phone is available
     */
    public function checkPhone(CheckPhoneRequest $request, CheckAvailabilityAction $action): JsonResponse
    {
        $result = $action->checkPhone($request->phone);

        return response()->json($result);
    }

    /**
     * Send OTP to phone number
     */
    public function sendOtp(SendOtpRequest $request, SendOtpAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->phone,
            $request->input('type', 'registration')
        );

        $statusCode = $result['success'] ? 200 : 429;

        return response()->json($result, $statusCode);
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(VerifyOtpRequest $request, VerifyOtpAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->phone,
            $request->code,
            $request->input('type', 'registration')
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }
}
