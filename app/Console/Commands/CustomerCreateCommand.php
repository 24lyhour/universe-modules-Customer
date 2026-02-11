<?php

namespace Modules\Customer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Customer\Models\Customer;

class CustomerCreateCommand extends Command
{
    protected $signature = 'customer:create
                            {--name= : Customer name}
                            {--email= : Customer email}
                            {--password= : Customer password}
                            {--phone= : Customer phone}
                            {--interactive : Interactive mode}';

    protected $description = 'Create a new customer';

    public function handle(): int
    {
        $this->info('Create New Customer');
        $this->line('-------------------');

        if ($this->option('interactive') || !$this->option('email')) {
            return $this->createInteractive();
        }

        return $this->createFromOptions();
    }

    protected function createInteractive(): int
    {
        $name = $this->ask('Customer name');
        $email = $this->ask('Customer email');
        $password = $this->secret('Password');
        $phone = $this->ask('Phone number (optional)');
        $gender = $this->choice('Gender', ['male', 'female', 'other'], 0);

        return $this->createCustomer([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'gender' => $gender,
        ]);
    }

    protected function createFromOptions(): int
    {
        return $this->createCustomer([
            'name' => $this->option('name'),
            'email' => $this->option('email'),
            'password' => $this->option('password'),
            'phone' => $this->option('phone'),
        ]);
    }

    protected function createCustomer(array $data): int
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:8',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  - {$error}");
            }
            return Command::FAILURE;
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'status' => 'active',
        ]);

        $this->newLine();
        $this->info("Customer created successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $customer->id],
                ['Name', $customer->name],
                ['Email', $customer->email],
                ['Phone', $customer->phone ?? '-'],
                ['Status', $customer->status],
            ]
        );

        return Command::SUCCESS;
    }
}
