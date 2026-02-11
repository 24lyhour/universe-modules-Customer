<?php

namespace Modules\Customer\Actions\Api;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Customer\Models\Customer;

class LoginAction
{
    /**
     * Execute the login action.
     *
     * @throws ValidationException
     */
    public function execute(array $data): array
    {
        $customer = $this->findCustomer($data);

        $this->validateCredentials($customer, $data['password']);
        $this->validateStatus($customer);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return [
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'customer' => $this->formatCustomer($customer),
        ];
    }

    /**
     * Find customer by email or phone.
     */
    private function findCustomer(array $data): ?Customer
    {
        $query = Customer::query();

        if (!empty($data['email'])) {
            $query->where('email', $data['email']);
        } elseif (!empty($data['phone'])) {
            $query->where('phone', $data['phone']);
        } else {
            return null;
        }

        return $query->first();
    }

    /**
     * Validate customer credentials.
     *
     * @throws ValidationException
     */
    private function validateCredentials(?Customer $customer, string $password): void
    {
        if (!$customer || !Hash::check($password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    }

    /**
     * Validate customer status.
     *
     * @throws ValidationException
     */
    private function validateStatus(Customer $customer): void
    {
        if ($customer->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }
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
            'status' => $customer->status,
        ];
    }
}
