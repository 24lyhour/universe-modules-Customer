<?php

namespace Modules\Customer\Actions\Api;

use Modules\Customer\Services\OtpService;

class SendOtpAction
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Send OTP to phone number.
     */
    public function execute(string $phone, string $type = 'registration'): array
    {
        // Check rate limit
        $rateLimit = $this->otpService->canSendOtp($phone, $type);

        if (!$rateLimit['can_send']) {
            return [
                'success' => false,
                'message' => $rateLimit['message'],
                'retry_after' => $rateLimit['retry_after'],
            ];
        }

        // Send OTP
        return $this->otpService->sendOtp($phone, $type);
    }
}
