<?php

namespace Modules\Customer\Actions\Api;

use Modules\Customer\Services\OtpService;

class VerifyOtpAction
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Verify OTP code.
     */
    public function execute(string $phone, string $code, string $type = 'registration'): array
    {
        return $this->otpService->verifyOtp($phone, $code, $type);
    }

    /**
     * Check if phone is verified.
     */
    public function isPhoneVerified(string $phone, string $type = 'registration'): bool
    {
        return $this->otpService->isPhoneVerified($phone, $type);
    }
}
