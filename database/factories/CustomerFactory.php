<?php

namespace Modules\Customer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Customer\Models\Customer;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password', // Will be hashed by model cast
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive', 'suspended']), // More active customers
            'avatar' => null,
            'two_factor_enabled' => fake()->boolean(20), // 20% have 2FA enabled
            'email_verified_at' => fake()->boolean(80) ? now() : null, // 80% verified
            'phone_verified_at' => fake()->boolean(60) ? now() : null, // 60% phone verified
        ];
    }

    /**
     * Indicate that the customer is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the customer is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the customer is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the customer has verified email.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the customer has unverified email.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the customer has 2FA enabled.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt(Str::random(32)),
            'two_factor_recovery_codes' => encrypt(json_encode([
                Str::random(10),
                Str::random(10),
                Str::random(10),
                Str::random(10),
            ])),
        ]);
    }

    /**
     * Indicate that the customer is male.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->name('male'),
            'gender' => 'male',
        ]);
    }

    /**
     * Indicate that the customer is female.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->name('female'),
            'gender' => 'female',
        ]);
    }
}
