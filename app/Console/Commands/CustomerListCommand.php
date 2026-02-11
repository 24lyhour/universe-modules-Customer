<?php

namespace Modules\Customer\Console\Commands;

use Illuminate\Console\Command;
use Modules\Customer\Models\Customer;

class CustomerListCommand extends Command
{
    protected $signature = 'customer:list
                            {--status= : Filter by status (active, inactive, suspended)}
                            {--verified : Show only verified customers}
                            {--limit=20 : Limit results}';

    protected $description = 'List all customers in the system';

    public function handle(): int
    {
        $this->info('Customer List');
        $this->line('--------------');

        $query = Customer::query();

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        if ($this->option('verified')) {
            $query->whereNotNull('email_verified_at');
        }

        $customers = $query->latest()->limit((int) $this->option('limit'))->get();

        if ($customers->isEmpty()) {
            $this->warn('No customers found.');
            return Command::SUCCESS;
        }

        $rows = $customers->map(function ($customer) {
            return [
                $customer->id,
                $customer->name,
                $customer->email,
                $customer->phone ?? '-',
                $customer->status,
                $customer->email_verified_at ? 'Yes' : 'No',
                $customer->two_factor_enabled ? 'Yes' : 'No',
                $customer->created_at->format('Y-m-d'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Email', 'Phone', 'Status', 'Verified', '2FA', 'Created'],
            $rows
        );

        $this->newLine();
        $this->info("Total: " . Customer::count() . " customers");

        return Command::SUCCESS;
    }
}
