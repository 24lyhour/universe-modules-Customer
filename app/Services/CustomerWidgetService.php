<?php

namespace Modules\Customer\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Models\Customer;

class CustomerWidgetService
{
    /**
     * Get complete widget data for the dashboard.
     */
    public function getWidgetData(string $dateRange = '30d'): array
    {
        $period = $this->getPeriodDates($dateRange);

        return [
            'metrics' => $this->getMetrics($dateRange),
            'growthData' => $this->getGrowthData($dateRange),
            'statusDistribution' => $this->getStatusDistribution(),
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Get metrics for the widget.
     */
    public function getMetrics(string $dateRange = '30d'): array
    {
        $period = $this->getPeriodDates($dateRange);
        $previousPeriod = $this->getPreviousPeriodDates($dateRange);

        $total = Customer::count();
        $previousTotal = Customer::where('created_at', '<', $period['start'])->count();

        // New customers in current period
        $newThisPeriod = Customer::whereBetween('created_at', [$period['start'], $period['end']])->count();
        $newPreviousPeriod = Customer::whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])->count();

        // New this month (always current month)
        $newThisMonth = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Active customers (status = active)
        $active = Customer::active()->count();

        // Returning customers (have made more than one order - simplified: updated more than once)
        $returning = $this->getReturningCustomersCount();

        // At risk customers (inactive for 30+ days but not churned)
        $atRisk = $this->getAtRiskCustomersCount();

        // Churned customers (inactive for 90+ days or status = inactive/suspended)
        $churned = $this->getChurnedCustomersCount();

        // VIP customers (top 10% by activity or manually flagged)
        $vip = $this->getVipCustomersCount();

        // Calculate growth percentage
        $growthPercent = $previousTotal > 0
            ? (($total - $previousTotal) / $previousTotal) * 100
            : ($total > 0 ? 100 : 0);

        // Retention rate (customers who stayed active from previous period)
        $retentionRate = $this->calculateRetentionRate($period, $previousPeriod);

        // Churn rate
        $churnRate = $total > 0 ? ($churned / $total) * 100 : 0;

        // Revenue metrics (simulated - would come from Order module)
        $revenueData = $this->getRevenueMetrics();

        return [
            'total' => $total,
            'newThisMonth' => $newThisMonth,
            'newThisPeriod' => $newThisPeriod,
            'active' => $active,
            'returning' => $returning,
            'atRisk' => $atRisk,
            'churned' => $churned,
            'vip' => $vip,
            'growthPercent' => round($growthPercent, 1),
            'previousPeriodTotal' => $previousTotal,
            'retentionRate' => round($retentionRate, 1),
            'churnRate' => round($churnRate, 1),
            'averageRevenue' => $revenueData['averageRevenue'],
            'totalRevenue' => $revenueData['totalRevenue'],
        ];
    }

    /**
     * Get growth data for charts.
     */
    public function getGrowthData(string $dateRange = '30d'): array
    {
        $period = $this->getPeriodDates($dateRange);
        $data = [];

        // Determine grouping based on date range
        $grouping = $this->getGroupingFormat($dateRange);

        // Get cumulative customer counts by date
        $dailyData = Customer::select(
            DB::raw("DATE_FORMAT(created_at, '{$grouping['format']}') as period"),
            DB::raw('COUNT(*) as new_customers')
        )
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        // Get churned counts (simplified: customers who became inactive)
        $churnedData = Customer::select(
            DB::raw("DATE_FORMAT(updated_at, '{$grouping['format']}') as period"),
            DB::raw('COUNT(*) as churned')
        )
            ->where('status', '!=', 'active')
            ->whereBetween('updated_at', [$period['start'], $period['end']])
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        // Generate data points
        $currentDate = Carbon::parse($period['start']);
        $endDate = Carbon::parse($period['end']);
        $runningTotal = Customer::where('created_at', '<', $period['start'])->count();

        while ($currentDate <= $endDate) {
            $periodKey = $currentDate->format($grouping['carbon_format']);
            $label = $currentDate->format($grouping['label_format']);

            $newCustomers = $dailyData->get($periodKey)?->new_customers ?? 0;
            $churned = $churnedData->get($periodKey)?->churned ?? 0;

            $runningTotal += $newCustomers;

            $data[] = [
                'label' => $label,
                'value' => $runningTotal,
                'newCustomers' => $newCustomers,
                'churned' => $churned,
            ];

            $currentDate->add($grouping['interval'], 1);
        }

        return $data;
    }

    /**
     * Get status distribution for donut chart.
     */
    public function getStatusDistribution(): array
    {
        $statusCounts = Customer::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $colors = [
            'active' => '#22c55e',    // green
            'inactive' => '#eab308',  // yellow
            'suspended' => '#ef4444', // red
        ];

        $distribution = [];
        foreach (['active', 'inactive', 'suspended'] as $status) {
            $distribution[] = [
                'status' => ucfirst($status),
                'count' => $statusCounts->get($status)?->count ?? 0,
                'color' => $colors[$status],
            ];
        }

        return $distribution;
    }

    /**
     * Get returning customers count.
     * Simplified: customers who have been updated (indicating activity).
     */
    protected function getReturningCustomersCount(): int
    {
        // In a real app, this would check orders table
        // For now, count customers who have been active multiple times
        return Customer::active()
            ->where('updated_at', '>', DB::raw('created_at'))
            ->count();
    }

    /**
     * Get at-risk customers count.
     * Customers who haven't been active for 30-90 days.
     */
    protected function getAtRiskCustomersCount(): int
    {
        return Customer::active()
            ->where('updated_at', '<', now()->subDays(30))
            ->where('updated_at', '>=', now()->subDays(90))
            ->count();
    }

    /**
     * Get churned customers count.
     * Inactive/suspended or no activity for 90+ days.
     */
    protected function getChurnedCustomersCount(): int
    {
        return Customer::where(function ($query) {
            $query->whereIn('status', ['inactive', 'suspended'])
                ->orWhere('updated_at', '<', now()->subDays(90));
        })->count();
    }

    /**
     * Get VIP customers count.
     * Top 10% most active customers.
     */
    protected function getVipCustomersCount(): int
    {
        $total = Customer::count();
        // Simplified: top 10% is the most recently active
        return (int) ceil($total * 0.1);
    }

    /**
     * Calculate retention rate.
     */
    protected function calculateRetentionRate(array $currentPeriod, array $previousPeriod): float
    {
        $customersAtStartOfPrevious = Customer::where('created_at', '<', $previousPeriod['start'])->count();

        if ($customersAtStartOfPrevious === 0) {
            return 100;
        }

        $retainedCustomers = Customer::active()
            ->where('created_at', '<', $previousPeriod['start'])
            ->count();

        return ($retainedCustomers / $customersAtStartOfPrevious) * 100;
    }

    /**
     * Get revenue metrics (simulated).
     * In a real app, this would query the Orders table.
     */
    protected function getRevenueMetrics(): array
    {
        $total = Customer::count();

        // Simulated values - in production, these would come from actual orders
        $averageRevenue = $total > 0 ? rand(50, 200) : 0;
        $totalRevenue = $total * $averageRevenue;

        return [
            'averageRevenue' => $averageRevenue,
            'totalRevenue' => $totalRevenue,
        ];
    }

    /**
     * Get period dates based on date range string.
     */
    protected function getPeriodDates(string $dateRange): array
    {
        $end = now();

        $start = match ($dateRange) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            'year' => now()->startOfYear(),
            default => now()->subDays(30),
        };

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Get previous period dates for comparison.
     */
    protected function getPreviousPeriodDates(string $dateRange): array
    {
        $period = $this->getPeriodDates($dateRange);
        $duration = $period['start']->diffInDays($period['end']);

        return [
            'start' => $period['start']->copy()->subDays($duration + 1),
            'end' => $period['start']->copy()->subDay(),
        ];
    }

    /**
     * Get grouping format based on date range.
     */
    protected function getGroupingFormat(string $dateRange): array
    {
        return match ($dateRange) {
            'today' => [
                'format' => '%Y-%m-%d %H:00:00',
                'carbon_format' => 'Y-m-d H:00:00',
                'label_format' => 'H:00',
                'interval' => 'hour',
            ],
            '7d' => [
                'format' => '%Y-%m-%d',
                'carbon_format' => 'Y-m-d',
                'label_format' => 'D',
                'interval' => 'day',
            ],
            '30d' => [
                'format' => '%Y-%m-%d',
                'carbon_format' => 'Y-m-d',
                'label_format' => 'M d',
                'interval' => 'day',
            ],
            '90d' => [
                'format' => '%Y-%u',
                'carbon_format' => 'Y-W',
                'label_format' => 'M d',
                'interval' => 'week',
            ],
            'year' => [
                'format' => '%Y-%m',
                'carbon_format' => 'Y-m',
                'label_format' => 'M',
                'interval' => 'month',
            ],
            default => [
                'format' => '%Y-%m-%d',
                'carbon_format' => 'Y-m-d',
                'label_format' => 'M d',
                'interval' => 'day',
            ],
        };
    }
}
