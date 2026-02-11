<?php

namespace Modules\Customer\Actions\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Customer\Models\Customer;

class RegisterAction
{
    /**
     * Execute the registration action.
     */
    public function execute(array $data, ?UploadedFile $avatar = null): array
    {
        $customer = $this->createCustomer($data);

        // Handle avatar upload
        if ($avatar) {
            $this->uploadAvatar($customer, $avatar);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;

        return [
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'customer' => $this->formatCustomer($customer),
        ];
    }

    /**
     * Create a new customer.
     */
    private function createCustomer(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => 'active',
        ]);
    }

    /**
     * Upload and save avatar.
     */
    private function uploadAvatar(Customer $customer, UploadedFile $avatar): void
    {
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
            'status' => $customer->status,
        ];
    }
}
