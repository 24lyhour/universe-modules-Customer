<?php

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerShipping;

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

        // Create shipping addresses for demo customers
        $this->createDemoShippingAddresses();
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
                'phone_verified_at' => now(),
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
                'phone_verified_at' => now(),
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
                'phone_verified_at' => now(),
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
        // Skip if we already have enough customers (avoid duplicates on re-run)
        $existingCount = Customer::count();
        if ($existingCount >= 50) {
            return;
        }

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

    /**
     * Create shipping addresses for demo customers.
     */
    private function createDemoShippingAddresses(): void
    {
        // Skip if shipping addresses already exist
        if (CustomerShipping::count() > 0) {
            return;
        }

        // Siev Ching - 2 addresses
        $sievChing = Customer::where('email', 'sievching@gmail.com')->first();
        if ($sievChing) {
            CustomerShipping::create([
                'customer_id' => $sievChing->id,
                'label' => 'Home',
                'recipient_name' => 'Siev Ching',
                'phone_number' => '+855 12 345 678',
                'province' => 'Phnom Penh',
                'district' => 'Chamkarmon',
                'commune' => 'Tonle Bassac',
                'street_address' => 'Street 308',
                'house_number' => '12A',
                'floor' => null,
                'landmark' => 'Near Aeon Mall',
                'note' => null,
                'latitude' => 11.5494,
                'longitude' => 104.9339,
                'is_default' => true,
            ]);

            CustomerShipping::create([
                'customer_id' => $sievChing->id,
                'label' => 'Office',
                'recipient_name' => 'Siev Ching',
                'phone_number' => '+855 12 345 678',
                'province' => 'Phnom Penh',
                'district' => 'Daun Penh',
                'commune' => 'Wat Phnom',
                'street_address' => 'Street 94',
                'house_number' => '55',
                'floor' => '3',
                'landmark' => 'Near NagaWorld',
                'note' => 'Use side entrance',
                'latitude' => 11.5731,
                'longitude' => 104.9222,
                'is_default' => false,
            ]);
        }

        // John Doe - 2 addresses
        $johnDoe = Customer::where('email', 'john.doe@example.com')->first();
        if ($johnDoe) {
            CustomerShipping::create([
                'customer_id' => $johnDoe->id,
                'label' => 'Home',
                'recipient_name' => 'John Doe',
                'phone_number' => '+855 77 123 456',
                'province' => 'Phnom Penh',
                'district' => 'Sen Sok',
                'commune' => 'Phnom Penh Thmey',
                'street_address' => 'Street 1986',
                'house_number' => '88',
                'floor' => null,
                'landmark' => 'Near Makro',
                'note' => null,
                'latitude' => 11.5860,
                'longitude' => 104.8873,
                'is_default' => true,
            ]);

            CustomerShipping::create([
                'customer_id' => $johnDoe->id,
                'label' => 'Office',
                'recipient_name' => 'John Doe',
                'phone_number' => '+855 77 123 456',
                'province' => 'Phnom Penh',
                'district' => 'Toul Kork',
                'commune' => 'Boeung Kak 2',
                'street_address' => 'Street 289',
                'house_number' => '15B',
                'floor' => '5',
                'landmark' => 'Near TK Avenue',
                'note' => 'Building B, Office 502',
                'latitude' => 11.5773,
                'longitude' => 104.8996,
                'is_default' => false,
            ]);
        }

        // Jane Smith - 1 address
        $janeSmith = Customer::where('email', 'jane.smith@example.com')->first();
        if ($janeSmith) {
            CustomerShipping::create([
                'customer_id' => $janeSmith->id,
                'label' => 'Home',
                'recipient_name' => 'Jane Smith',
                'phone_number' => '+855 96 789 012',
                'province' => 'Phnom Penh',
                'district' => 'Meanchey',
                'commune' => 'Stung Meanchey',
                'street_address' => 'Street 217',
                'house_number' => '33',
                'floor' => null,
                'landmark' => 'Near Chip Mong Mall',
                'note' => null,
                'latitude' => 11.5345,
                'longitude' => 104.9118,
                'is_default' => true,
            ]);
        }

        // Bob Wilson - 1 address
        $bobWilson = Customer::where('email', 'bob.wilson@example.com')->first();
        if ($bobWilson) {
            CustomerShipping::create([
                'customer_id' => $bobWilson->id,
                'label' => 'Home',
                'recipient_name' => 'Bob Wilson',
                'phone_number' => '+855 15 456 789',
                'province' => 'Siem Reap',
                'district' => 'Siem Reap',
                'commune' => 'Svay Dangkum',
                'street_address' => 'National Road 6',
                'house_number' => '120',
                'floor' => null,
                'landmark' => 'Near Angkor Market',
                'note' => null,
                'latitude' => 13.3527,
                'longitude' => 103.8560,
                'is_default' => true,
            ]);
        }

        // Alice Brown - 2 addresses
        $aliceBrown = Customer::where('email', 'alice.brown@example.com')->first();
        if ($aliceBrown) {
            CustomerShipping::create([
                'customer_id' => $aliceBrown->id,
                'label' => 'Home',
                'recipient_name' => 'Alice Brown',
                'phone_number' => '+855 10 321 098',
                'province' => 'Phnom Penh',
                'district' => 'Russey Keo',
                'commune' => 'Tuol Sangke',
                'street_address' => 'Street 598',
                'house_number' => '7',
                'floor' => null,
                'landmark' => 'Near Big C',
                'note' => null,
                'latitude' => 11.5920,
                'longitude' => 104.9055,
                'is_default' => true,
            ]);

            CustomerShipping::create([
                'customer_id' => $aliceBrown->id,
                'label' => 'Other',
                'recipient_name' => 'Alice Brown',
                'phone_number' => '+855 10 321 098',
                'province' => 'Phnom Penh',
                'district' => 'Chamkarmon',
                'commune' => 'Tumnob Tuek',
                'street_address' => 'Street 63',
                'house_number' => '42',
                'floor' => '2',
                'landmark' => 'Near Russian Market',
                'note' => 'Ring doorbell twice',
                'latitude' => 11.5500,
                'longitude' => 104.9248,
                'is_default' => false,
            ]);
        }
    }
}
