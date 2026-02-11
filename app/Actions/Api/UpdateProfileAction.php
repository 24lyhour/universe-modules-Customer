<?php

namespace Modules\Customer\Actions\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Customer\Models\Customer;

class UpdateProfileAction
{
    /**
     * Execute the update profile action.
     */
    public function execute(Customer $customer, array $data, ?UploadedFile $avatar = null): array
    {
        // Handle avatar upload
        if ($avatar) {
            $this->uploadAvatar($customer, $avatar);
        }

        // Update customer data
        $customer->update($this->filterData($data));

        return [
            'message' => 'Profile updated successfully',
            'customer' => $this->formatCustomer($customer->fresh()),
        ];
    }

    /**
     * Filter data to only include allowed fields.
     */
    private function filterData(array $data): array
    {
        return array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address' => $data['address'] ?? null,
        ], fn($value) => $value !== null);
    }

    /**
     * Upload and save avatar.
     */
    private function uploadAvatar(Customer $customer, UploadedFile $avatar): void
    {
        // Delete old avatar if exists
        if ($customer->avatar) {
            $oldPath = str_replace('/storage/', '', $customer->avatar);
            Storage::disk('public')->delete($oldPath);
        }

        $filename = 'customer_' . $customer->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
        $path = $avatar->storeAs('avatars/customers', $filename, 'public');

        $customer->update([
            'avatar' => Storage::url($path),
        ]);
    }

    /**
     * Format customer data for response.
     */
    private function formatCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'avatar' => $customer->avatar,
            'gender' => $customer->gender,
            'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
            'address' => $customer->address,
            'status' => $customer->status,
        ];
    }
}
