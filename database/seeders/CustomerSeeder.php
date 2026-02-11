<?php

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Customer\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific demo customers
        $this->createDemoCustomers();

        // Create random customers
        $this->createRandomCustomers();
    }

    /**
     * Create specific demo customers for testing.
     */
    private function createDemoCustomers(): void
    {
        // Primary demo customer
        Customer::firstOrCreate(
            ['email' => 'sievching@gmail.com'],
            [
                'name' => 'Siev Ching',
                'password' => bcrypt('12345678'),
                'phone' => '+855 12 345 678',
                'address' => 'Phnom Penh, Cambodia',
                'date_of_birth' => '1995-01-01',
                'gender' => 'male',
                'status' => 'active',
                'email_verified_at' => now(),
                'two_factor_enabled' => false,
            ]
        );

        // Demo customer - active with all features
        Customer::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Main Street, New York, NY 10001',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'status' => 'active',
                'email_verified_at' => now(),
                'two_factor_enabled' => true,
            ]
        );

        // Demo customer - active female
        Customer::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 987-6543',
                'address' => '456 Oak Avenue, Los Angeles, CA 90001',
                'date_of_birth' => '1985-08-22',
                'gender' => 'female',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Demo customer - inactive
        Customer::firstOrCreate(
            ['email' => 'bob.wilson@example.com'],
            [
                'name' => 'Bob Wilson',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 456-7890',
                'address' => '789 Pine Road, Chicago, IL 60601',
                'date_of_birth' => '1978-12-03',
                'gender' => 'male',
                'status' => 'inactive',
                'email_verified_at' => now()->subMonths(6),
            ]
        );

        // Demo customer - suspended
        Customer::firstOrCreate(
            ['email' => 'alice.brown@example.com'],
            [
                'name' => 'Alice Brown',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 321-0987',
                'address' => '321 Maple Lane, Houston, TX 77001',
                'date_of_birth' => '1995-03-18',
                'gender' => 'female',
                'status' => 'suspended',
                'email_verified_at' => null,
            ]
        );

        // Demo customer - unverified email
        Customer::firstOrCreate(
            ['email' => 'charlie.davis@example.com'],
            [
                'name' => 'Charlie Davis',
                'password' => bcrypt('password'),
                'phone' => null,
                'address' => null,
                'date_of_birth' => null,
                'gender' => null,
                'status' => 'active',
                'email_verified_at' => null,
            ]
        );
    }

    /**
     * Create random customers using factory.
     */
    private function createRandomCustomers(): void
    {
        // Create 20 random active customers
        Customer::factory()
            ->count(20)
            ->active()
            ->verified()
            ->create();

        // Create 5 inactive customers
        Customer::factory()
            ->count(5)
            ->inactive()
            ->create();

        // Create 3 suspended customers
        Customer::factory()
            ->count(3)
            ->suspended()
            ->create();

        // Create 5 customers with 2FA enabled
        Customer::factory()
            ->count(5)
            ->active()
            ->verified()
            ->withTwoFactor()
            ->create();

        // Create 5 unverified customers
        Customer::factory()
            ->count(5)
            ->active()
            ->unverified()
            ->create();

        // Create some male customers
        Customer::factory()
            ->count(5)
            ->male()
            ->active()
            ->create();

        // Create some female customers
        Customer::factory()
            ->count(5)
            ->female()
            ->active()
            ->create();
    }
}
