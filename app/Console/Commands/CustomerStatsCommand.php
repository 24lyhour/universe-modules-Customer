<?php

namespace Modules\Customer\Console\Commands;

use Illuminate\Console\Command;
use Modules\Customer\Models\Customer;

class CustomerStatsCommand extends Command
{
    protected $signature = 'customer:stats';

    protected $description = 'Display customer statistics';

    public function handle(): int
    {
        $this->info('Customer Statistics');
        $this->line('-------------------');

        $total = Customer::count();
        $active = Customer::where('status', 'active')->count();
        $inactive = Customer::where('status', 'inactive')->count();
        $suspended = Customer::where('status', 'suspended')->count();
        $verified = Customer::whereNotNull('email_verified_at')->count();
        $twoFactor = Customer::where('two_factor_enabled', true)->count();
        $thisMonth = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $thisWeek = Customer::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        $this->newLine();
        $this->info('Overview');
        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Total Customers', $total, '100%'],
                ['Active', $active, $total > 0 ? round(($active / $total) * 100, 1) . '%' : '0%'],
                ['Inactive', $inactive, $total > 0 ? round(($inactive / $total) * 100, 1) . '%' : '0%'],
                ['Suspended', $suspended, $total > 0 ? round(($suspended / $total) * 100, 1) . '%' : '0%'],
            ]
        );

        $this->newLine();
        $this->info('Security');
        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Email Verified', $verified, $total > 0 ? round(($verified / $total) * 100, 1) . '%' : '0%'],
                ['2FA Enabled', $twoFactor, $total > 0 ? round(($twoFactor / $total) * 100, 1) . '%' : '0%'],
            ]
        );

        $this->newLine();
        $this->info('Growth');
        $this->table(
            ['Period', 'New Customers'],
            [
                ['This Week', $thisWeek],
                ['This Month', $thisMonth],
            ]
        );

        // Gender breakdown
        $byGender = Customer::selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        if (!empty($byGender)) {
            $this->newLine();
            $this->info('By Gender');
            $genderRows = collect($byGender)->map(function ($count, $gender) {
                return [ucfirst($gender ?? 'Not specified'), $count];
            })->values()->toArray();
            $this->table(['Gender', 'Count'], $genderRows);
        }

        return Command::SUCCESS;
    }
}
