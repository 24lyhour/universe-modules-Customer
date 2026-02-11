<?php

namespace Modules\Customer\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

trait TwoFactorAuthentication
{
    /**
     * Enable two-factor authentication for the customer.
     */
    public function enableTwoFactor(): bool
    {
        $this->two_factor_enabled = true;
        $this->two_factor_secret = $this->generateTwoFactorSecret();

        return $this->save();
    }

    /**
     * Disable two-factor authentication for the customer.
     */
    public function disableTwoFactor(): bool
    {
        $this->two_factor_enabled = false;
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;

        return $this->save();
    }

    /**
     * Check if two-factor authentication is enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return (bool) $this->two_factor_enabled;
    }

    /**
     * Generate a new two-factor secret.
     * Note: Encryption is handled by model casts.
     */
    protected function generateTwoFactorSecret(): string
    {
        return Str::random(32);
    }

    /**
     * Generate recovery codes for two-factor authentication.
     * Note: Encryption is handled by model casts.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = collect(range(1, $count))->map(function () {
            return Str::random(10) . '-' . Str::random(10);
        })->toArray();

        $this->two_factor_recovery_codes = json_encode($codes);
        $this->save();

        return $codes;
    }

    /**
     * Get the recovery codes.
     * Note: Decryption is handled by model casts.
     */
    public function getRecoveryCodes(): array
    {
        if (!$this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode($this->two_factor_recovery_codes, true) ?? [];
    }

    /**
     * Use a recovery code.
     */
    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->getRecoveryCodes();

        $key = array_search($code, $codes);

        if ($key === false) {
            return false;
        }

        unset($codes[$key]);

        $this->two_factor_recovery_codes = json_encode(array_values($codes));
        $this->save();

        return true;
    }

    /**
     * Generate and send a two-factor code via email/SMS.
     */
    public function sendTwoFactorCode(string $channel = 'email'): string
    {
        $code = $this->generateNumericCode();

        // Store the code in cache for 10 minutes
        Cache::put(
            $this->getTwoFactorCacheKey(),
            Hash::make($code),
            now()->addMinutes(10)
        );

        // Send via email or SMS based on channel
        if ($channel === 'email' && $this->email) {
            // You can dispatch a notification here
            // $this->notify(new TwoFactorCodeNotification($code));
        } elseif ($channel === 'sms' && $this->phone) {
            // Send SMS notification
            // $this->notify(new TwoFactorSmsNotification($code));
        }

        return $code;
    }

    /**
     * Verify the two-factor code.
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        $hashedCode = Cache::get($this->getTwoFactorCacheKey());

        if (!$hashedCode) {
            return false;
        }

        if (Hash::check($code, $hashedCode)) {
            Cache::forget($this->getTwoFactorCacheKey());
            return true;
        }

        return false;
    }

    /**
     * Generate a 6-digit numeric code.
     */
    protected function generateNumericCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the cache key for the two-factor code.
     */
    protected function getTwoFactorCacheKey(): string
    {
        return 'two_factor_code_' . $this->getTable() . '_' . $this->getKey();
    }

    /**
     * Check if phone is verified.
     */
    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * Mark the phone as verified.
     */
    public function markPhoneAsVerified(): bool
    {
        $this->phone_verified_at = now();
        return $this->save();
    }

    /**
     * Check if email is verified.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = now();
        return $this->save();
    }
}
