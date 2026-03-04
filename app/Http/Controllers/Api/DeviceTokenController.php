<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Store or update the FCM device token for the authenticated customer.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:255',
        ]);

        $customer = $request->user();
        $customer->update([
            'fcm_token' => $request->token,
        ]);

        return response()->json([
            'message' => 'Device token saved successfully.',
        ]);
    }

    /**
     * Remove the FCM device token for the authenticated customer.
     */
    public function destroy(Request $request): JsonResponse
    {
        $customer = $request->user();
        $customer->update([
            'fcm_token' => null,
        ]);

        return response()->json([
            'message' => 'Device token removed successfully.',
        ]);
    }
}
