<?php

namespace Modules\Customer\Services;

use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerOtp;

class OtpService
{
    /**
     * OTP expiration time in minutes.
     */
    protected int $expirationMinutes = 5;

    /**
     * OTP length.
     */
    protected int $otpLength = 6;

    /**
     * Generate a random OTP code.
     */
    public function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), $this->otpLength, '0', STR_PAD_LEFT);
    }

    /**
     * Create and send OTP to phone number.
     */
    public function sendOtp(
        string $phone,
        string $type = 'registration',
        ?Customer $customer = null
    ): array {
        // Invalidate any existing OTPs for this phone and type
        $this->invalidateExistingOtps($phone, $type);

        // Generate new OTP
        $code = $this->generateCode();

        // Store OTP
        $otp = CustomerOtp::create([
            'customer_id' => $customer?->id,
            'phone' => $phone,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addMinutes($this->expirationMinutes),
        ]);

        // Send OTP via SMS (implement your SMS provider here)
        $sent = $this->sendSms($phone, $code);

        return [
            'success' => $sent,
            'message' => $sent
                ? 'OTP sent successfully'
                : 'Failed to send OTP',
            'expires_in' => $this->expirationMinutes * 60, // seconds
            'phone' => $this->maskPhone($phone),
        ];
    }

    /**
     * Verify OTP code.
     */
    public function verifyOtp(string $phone, string $code, string $type = 'registration'): array
    {
        $otp = CustomerOtp::where('phone', $phone)
            ->where('type', $type)
            ->where('verified', false)
            ->latest()
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'OTP not found. Please request a new one.',
            ];
        }

        if ($otp->isExpired()) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ];
        }

        if ($otp->maxAttemptsReached()) {
            return [
                'success' => false,
                'message' => 'Maximum attempts reached. Please request a new OTP.',
            ];
        }

        if ($otp->code !== $code) {
            $otp->incrementAttempts();
            $remainingAttempts = 5 - $otp->attempts;

            return [
                'success' => false,
                'message' => "Invalid OTP. {$remainingAttempts} attempts remaining.",
                'remaining_attempts' => $remainingAttempts,
            ];
        }

        // Mark OTP as verified
        $otp->markAsVerified();

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'verified_at' => $otp->verified_at->toISOString(),
        ];
    }

    /**
     * Check if phone has been verified recently.
     */
    public function isPhoneVerified(string $phone, string $type = 'registration', int $withinMinutes = 30): bool
    {
        return CustomerOtp::where('phone', $phone)
            ->where('type', $type)
            ->where('verified', true)
            ->where('verified_at', '>=', now()->subMinutes($withinMinutes))
            ->exists();
    }

    /**
     * Invalidate existing OTPs for a phone and type.
     */
    protected function invalidateExistingOtps(string $phone, string $type): void
    {
        CustomerOtp::where('phone', $phone)
            ->where('type', $type)
            ->where('verified', false)
            ->update(['expires_at' => now()]);
    }

    /**
     * Send SMS with OTP code.
     * Override this method to implement your SMS provider.
     */
    protected function sendSms(string $phone, string $code): bool
    {
        // TODO: Implement your SMS provider here
        // Example with Twilio, Nexmo, or any other provider
        //
        // try {
        //     $client = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
        //     $client->messages->create($phone, [
        //         'from' => config('services.twilio.from'),
        //         'body' => "Your verification code is: {$code}. Valid for {$this->expirationMinutes} minutes.",
        //     ]);
        //     return true;
        // } catch (\Exception $e) {
        //     \Log::error('SMS sending failed: ' . $e->getMessage());
        //     return false;
        // }

        // For development, log the OTP instead
        \Log::info("OTP for {$phone}: {$code}");

        return true;
    }

    /**
     * Mask phone number for display.
     */
    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        return substr($phone, 0, 3) . str_repeat('*', $length - 6) . substr($phone, -3);
    }

    /**
     * Check rate limit for sending OTPs.
     */
    public function canSendOtp(string $phone, string $type = 'registration', int $cooldownSeconds = 60): array
    {
        $lastOtp = CustomerOtp::where('phone', $phone)
            ->where('type', $type)
            ->latest()
            ->first();

        if (!$lastOtp) {
            return ['can_send' => true];
        }

        $secondsSinceLastOtp = now()->diffInSeconds($lastOtp->created_at);

        if ($secondsSinceLastOtp < $cooldownSeconds) {
            return [
                'can_send' => false,
                'retry_after' => $cooldownSeconds - $secondsSinceLastOtp,
                'message' => 'Please wait before requesting a new OTP.',
            ];
        }

        return ['can_send' => true];
    }
}
