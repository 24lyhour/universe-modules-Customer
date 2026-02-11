<?php

namespace Modules\Customer\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Models\Customer;

class CustomerService
{
    /**
     * Get all customers with optional filters.
     */
    public function all(array $filters = []): Collection
    {
        $query = Customer::query();

        $this->applyFilters($query, $filters);

        return $query->latest()->get();
    }

    /**
     * Get paginated customers.
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Customer::query();

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find customer by ID.
     */
    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * Find customer by email.
     */
    public function findByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }

    /**
     * Create a new customer.
     */
    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'status' => $data['status'] ?? 'active',
                'outlet_id' => $data['outlet_id'] ?? null,
                'wallet_id' => $data['wallet_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null, // referrer
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return $customer;
        });
    }

    /**
     * Update a customer.
     */
    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $updateData = collect($data)->only([
                'name',
                'email',
                'phone',
                'address',
                'date_of_birth',
                'gender',
                'status',
                'outlet_id',
                'wallet_id',
                'avatar',
            ])->filter()->toArray();

            if (isset($data['password']) && $data['password']) {
                $updateData['password'] = $data['password'];
            }

            $updateData['updated_by'] = auth()->id();

            $customer->update($updateData);

            return $customer->fresh();
        });
    }

    /**
     * Delete a customer.
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * Activate a customer.
     */
    public function activate(Customer $customer): Customer
    {
        $customer->update([
            'status' => 'active',
            'status_reason' => null,
            'updated_by' => auth()->id(),
        ]);

        return $customer;
    }

    /**
     * Deactivate a customer.
     */
    public function deactivate(Customer $customer, ?string $reason = null): Customer
    {
        $customer->update([
            'status' => 'inactive',
            'status_reason' => $reason,
            'updated_by' => auth()->id(),
        ]);

        return $customer;
    }

    /**
     * Suspend a customer.
     */
    public function suspend(Customer $customer, ?string $reason = null): Customer
    {
        $customer->update([
            'status' => 'suspended',
            'status_reason' => $reason,
            'updated_by' => auth()->id(),
        ]);

        return $customer;
    }

    /**
     * Verify customer email.
     */
    public function verifyEmail(Customer $customer): Customer
    {
        $customer->markEmailAsVerified();

        return $customer;
    }

    /**
     * Verify customer phone.
     */
    public function verifyPhone(Customer $customer): Customer
    {
        $customer->markPhoneAsVerified();

        return $customer;
    }

    /**
     * Enable two-factor authentication.
     */
    public function enableTwoFactor(Customer $customer): array
    {
        $customer->enableTwoFactor();
        $recoveryCodes = $customer->generateRecoveryCodes();

        return [
            'enabled' => true,
            'recovery_codes' => $recoveryCodes,
        ];
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(Customer $customer): bool
    {
        return $customer->disableTwoFactor();
    }

    /**
     * Update customer password.
     */
    public function updatePassword(Customer $customer, string $password): Customer
    {
        $customer->update([
            'password' => $password,
            'updated_by' => auth()->id(),
        ]);

        return $customer;
    }

    /**
     * Get customer statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => Customer::count(),
            'active' => Customer::active()->count(),
            'inactive' => Customer::inactive()->count(),
            'suspended' => Customer::suspended()->count(),
            'verified' => Customer::whereNotNull('email_verified_at')->count(),
            'two_factor_enabled' => Customer::where('two_factor_enabled', true)->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'new_this_week' => Customer::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
        ];
    }

    /**
     * Search customers.
     */
    public function search(string $term, int $limit = 10): Collection
    {
        return Customer::where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->limit($limit)
            ->get();
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['verified']) && $filters['verified']) {
            $query->whereNotNull('email_verified_at');
        }

        if (isset($filters['two_factor']) && $filters['two_factor']) {
            $query->where('two_factor_enabled', true);
        }

        if (isset($filters['outlet_id'])) {
            $query->where('outlet_id', $filters['outlet_id']);
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
    }
}
