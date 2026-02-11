<?php

namespace Modules\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->date_of_birth?->age,
            'gender' => $this->gender,
            'status' => $this->status,
            'avatar' => $this->avatar,

            // Verification status
            'email_verified' => $this->hasVerifiedEmail(),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'phone_verified' => $this->hasVerifiedPhone(),
            'phone_verified_at' => $this->phone_verified_at?->toISOString(),

            // Security
            'two_factor_enabled' => $this->two_factor_enabled,
            'referral_code' => $this->referral_code ?? null,

            // Relationships (loaded conditionally)
            'outlet' => $this->whenLoaded('outlet', function () {
                return [
                    'id' => $this->outlet->id,
                    'name' => $this->outlet->name ?? null,
                ];
            }),
            'wallet' => $this->whenLoaded('wallet', function () {
                return [
                    'id' => $this->wallet->id,
                    'balance' => $this->wallet->balance ?? 0,
                ];
            }),
            'referrer' => $this->whenLoaded('referrer', function () {
                return [
                    'id' => $this->referrer->id,
                    'name' => $this->referrer->name,
                ];
            }),
            'referrals' => $this->whenLoaded('referrals', function () {
                return $this->referrals->map(fn ($referral) => [
                    'id' => $referral->id,
                    'name' => $referral->name,
                    'email' => $referral->email,
                    'status' => $referral->status,
                ]);
            }),
            'referrals_count' => $this->whenCounted('referrals'),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
