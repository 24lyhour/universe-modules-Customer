<script setup lang="ts">
import { computed, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    ChartContainer,
    ChartCrosshair,
    ChartTooltip,
    ChartTooltipContent,
    componentToString,
} from '@/components/ui/chart';
import {
    VisXYContainer,
    VisStackedBar,
    VisAxis,
    VisSingleContainer,
    VisDonut,
    VisArea,
    VisLine,
} from '@unovis/vue';
import { ref } from 'vue';
import {
    Users,
    UserPlus,
    UserCheck,
    UserX,
    TrendingUp,
    TrendingDown,
    Crown,
    AlertTriangle,
    RefreshCw,
    Calendar,
    ArrowUpRight,
    ArrowDownRight,
    Activity,
    DollarSign,
    Repeat,
    Target,
} from 'lucide-vue-next';
import type { ChartConfig } from '@/components/ui/chart';

// Types
export interface CustomerMetrics {
    total: number;
    newThisMonth: number;
    newThisPeriod: number;
    active: number;
    returning: number;
    atRisk: number;
    churned: number;
    vip: number;
    growthPercent: number;
    previousPeriodTotal: number;
    retentionRate: number;
    churnRate: number;
    averageRevenue: number;
    totalRevenue: number;
}

export interface GrowthDataPoint {
    label: string;
    value: number;
    newCustomers: number;
    churned: number;
}

export interface StatusDistribution {
    status: string;
    count: number;
    color: string;
}

export interface CustomerWidgetProps {
    metrics: CustomerMetrics;
    growthData: GrowthDataPoint[];
    statusDistribution: StatusDistribution[];
    dateRange?: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<CustomerWidgetProps>(), {
    dateRange: '30d',
    loading: false,
});

const emit = defineEmits<{
    (e: 'dateRangeChange', value: string): void;
    (e: 'refresh'): void;
}>();

const selectedDateRange = ref(props.dateRange);

// Date range options
const dateRangeOptions = [
    { value: 'today', label: 'Today' },
    { value: '7d', label: 'Last 7 Days' },
    { value: '30d', label: 'Last 30 Days' },
    { value: '90d', label: 'Last 90 Days' },
    { value: 'year', label: 'This Year' },
];

// Computed metrics
const growthTrend = computed(() => ({
    isPositive: props.metrics.growthPercent >= 0,
    value: Math.abs(props.metrics.growthPercent),
}));

const retentionTrend = computed(() => ({
    isPositive: props.metrics.retentionRate >= 70,
    value: props.metrics.retentionRate,
}));

// Chart configs - use CSS variables for shadcn-vue compatibility
const growthChartConfig: ChartConfig = {
    value: { label: 'Total Customers', color: 'var(--chart-1)' },
    newCustomers: { label: 'New Customers', color: 'var(--chart-2)' },
    churned: { label: 'Churned', color: 'var(--chart-3)' },
};

const statusChartConfig: ChartConfig = {
    new: { label: 'New', color: 'var(--chart-1)' },
    active: { label: 'Active', color: 'var(--chart-2)' },
    returning: { label: 'Returning', color: 'var(--chart-3)' },
    atRisk: { label: 'At Risk', color: 'var(--chart-4)' },
    vip: { label: 'VIP', color: 'var(--chart-5)' },
    churned: { label: 'Churned', color: 'var(--primary)' },
};

// Donut chart data - use config colors
const donutData = computed(() => [
    { label: 'New', value: props.metrics.newThisPeriod, color: statusChartConfig.new.color },
    { label: 'Active', value: props.metrics.active, color: statusChartConfig.active.color },
    { label: 'Returning', value: props.metrics.returning, color: statusChartConfig.returning.color },
    { label: 'At Risk', value: props.metrics.atRisk, color: statusChartConfig.atRisk.color },
    { label: 'VIP', value: props.metrics.vip, color: statusChartConfig.vip.color },
]);

// Status bar data
const statusBarData = computed(() => [
    { label: 'New', value: props.metrics.newThisPeriod },
    { label: 'Active', value: props.metrics.active },
    { label: 'Returning', value: props.metrics.returning },
    { label: 'At Risk', value: props.metrics.atRisk },
    { label: 'Churned', value: props.metrics.churned },
    { label: 'VIP', value: props.metrics.vip },
]);

// Watch date range changes
watch(selectedDateRange, (newValue) => {
    emit('dateRangeChange', newValue);
});

// Methods
const handleRefresh = () => {
    emit('refresh');
};

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};

const formatCurrency = (num: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

const formatPercent = (num: number) => {
    return `${num.toFixed(1)}%`;
};

// Get status badge variant
const getStatusBadgeVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status.toLowerCase()) {
        case 'new':
            return 'default';
        case 'active':
            return 'secondary';
        case 'vip':
            return 'default';
        case 'at risk':
        case 'atrisk':
            return 'destructive';
        case 'churned':
            return 'outline';
        default:
            return 'secondary';
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header with Date Filter -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">Customer Growth Metrics</h2>
                <p class="text-sm text-muted-foreground">Track customer acquisition and retention</p>
            </div>
            <div class="flex items-center gap-2">
                <Select v-model="selectedDateRange">
                    <SelectTrigger class="w-[160px]">
                        <Calendar class="mr-2 h-4 w-4" />
                        <SelectValue placeholder="Select period" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in dateRangeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="outline" size="icon" @click="handleRefresh" :disabled="loading">
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
                </Button>
            </div>
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Customers -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Customers</CardTitle>
                    <Users class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(metrics.total) }}</div>
                    <div class="flex items-center text-xs">
                        <component
                            :is="growthTrend.isPositive ? ArrowUpRight : ArrowDownRight"
                            class="mr-1 h-3 w-3"
                            :class="growthTrend.isPositive ? 'text-green-500' : 'text-red-500'"
                        />
                        <span :class="growthTrend.isPositive ? 'text-green-500' : 'text-red-500'">
                            {{ growthTrend.isPositive ? '+' : '-' }}{{ formatPercent(growthTrend.value) }}
                        </span>
                        <span class="ml-1 text-muted-foreground">vs previous period</span>
                    </div>
                </CardContent>
            </Card>

            <!-- New Customers -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">New Customers</CardTitle>
                    <UserPlus class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatNumber(metrics.newThisPeriod) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatNumber(metrics.newThisMonth) }} this month
                    </p>
                </CardContent>
            </Card>

            <!-- Active Customers -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active Customers</CardTitle>
                    <UserCheck class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">{{ formatNumber(metrics.active) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatPercent((metrics.active / metrics.total) * 100) }} of total
                    </p>
                </CardContent>
            </Card>

            <!-- Returning Customers -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Returning Customers</CardTitle>
                    <Repeat class="h-4 w-4 text-purple-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-purple-600">{{ formatNumber(metrics.returning) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatPercent((metrics.returning / metrics.total) * 100) }} return rate
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Secondary Metrics Row -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Retention Rate -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Retention Rate</CardTitle>
                    <Target class="h-4 w-4 text-emerald-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold" :class="retentionTrend.isPositive ? 'text-emerald-600' : 'text-amber-600'">
                        {{ formatPercent(metrics.retentionRate) }}
                    </div>
                    <div class="mt-1 h-1.5 w-full rounded-full bg-muted">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="retentionTrend.isPositive ? 'bg-emerald-500' : 'bg-amber-500'"
                            :style="{ width: `${Math.min(metrics.retentionRate, 100)}%` }"
                        ></div>
                    </div>
                </CardContent>
            </Card>

            <!-- Churn Rate -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Churn Rate</CardTitle>
                    <UserX class="h-4 w-4 text-red-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold" :class="metrics.churnRate > 5 ? 'text-red-600' : 'text-emerald-600'">
                        {{ formatPercent(metrics.churnRate) }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatNumber(metrics.churned) }} churned customers
                    </p>
                </CardContent>
            </Card>

            <!-- Revenue per Customer -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Avg. Revenue/Customer</CardTitle>
                    <DollarSign class="h-4 w-4 text-amber-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatCurrency(metrics.averageRevenue) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatCurrency(metrics.totalRevenue) }} total revenue
                    </p>
                </CardContent>
            </Card>

            <!-- VIP Customers -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">VIP Customers</CardTitle>
                    <Crown class="h-4 w-4 text-yellow-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-yellow-600">{{ formatNumber(metrics.vip) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatPercent((metrics.vip / metrics.total) * 100) }} of total
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- At Risk Alert -->
        <Card v-if="metrics.atRisk > 0" class="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/20">
            <CardContent class="flex items-center gap-4 pt-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50">
                    <AlertTriangle class="h-5 w-5 text-amber-600" />
                </div>
                <div class="flex-1">
                    <p class="font-medium text-amber-800 dark:text-amber-200">
                        {{ formatNumber(metrics.atRisk) }} customers at risk of churning
                    </p>
                    <p class="text-sm text-amber-600 dark:text-amber-400">
                        Consider reaching out with a retention campaign
                    </p>
                </div>
                <Button variant="outline" size="sm" class="border-amber-300 text-amber-700 hover:bg-amber-100">
                    View At Risk
                </Button>
            </CardContent>
        </Card>

        <!-- Charts Section -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Growth Chart -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Activity class="h-5 w-5" />
                        Customer Growth Trend
                    </CardTitle>
                    <CardDescription>Customer acquisition over time</CardDescription>
                </CardHeader>
                <CardContent>
                    <ChartContainer :config="growthChartConfig" class="h-[280px]">
                        <VisXYContainer :data="props.growthData" :margin="{ top: 10, bottom: 10 }">
                            <VisArea
                                :x="(d: GrowthDataPoint, i: number) => i"
                                :y="(d: GrowthDataPoint) => d.value"
                                :color="growthChartConfig.value.color"
                                :opacity="0.4"
                            />
                            <VisLine
                                :x="(d: GrowthDataPoint, i: number) => i"
                                :y="(d: GrowthDataPoint) => d.value"
                                :color="growthChartConfig.value.color"
                                :line-width="2"
                            />
                            <VisAxis
                                type="x"
                                :tick-line="false"
                                :domain-line="false"
                                :grid-line="false"
                                :tick-format="(i: number) => props.growthData[i]?.label || ''"
                            />
                            <VisAxis
                                type="y"
                                :num-ticks="5"
                                :tick-line="false"
                                :domain-line="false"
                            />
                            <ChartTooltip />
                            <ChartCrosshair
                                :template="componentToString(growthChartConfig, ChartTooltipContent, { labelKey: 'label', indicator: 'line' })"
                                :color="growthChartConfig.value.color"
                            />
                        </VisXYContainer>
                    </ChartContainer>
                </CardContent>
            </Card>

            <!-- Status Distribution Chart -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        Customer Status Distribution
                    </CardTitle>
                    <CardDescription>Breakdown by customer status</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Donut Chart -->
                        <ChartContainer :config="statusChartConfig" class="h-[200px]">
                            <VisSingleContainer :data="donutData">
                                <VisDonut
                                    :value="(d: any) => d.value"
                                    :color="(d: any) => d.color"
                                    :arcWidth="50"
                                    :padAngle="0.02"
                                    :cornerRadius="4"
                                />
                            </VisSingleContainer>
                        </ChartContainer>

                        <!-- Legend -->
                        <div class="flex flex-col justify-center space-y-3">
                            <div
                                v-for="(item, index) in donutData"
                                :key="item.label"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="h-3 w-3 rounded-full"
                                        :class="{
                                            'bg-[var(--chart-1)]': index === 0,
                                            'bg-[var(--chart-2)]': index === 1,
                                            'bg-[var(--chart-3)]': index === 2,
                                            'bg-[var(--chart-4)]': index === 3,
                                            'bg-[var(--chart-5)]': index === 4,
                                        }"
                                    ></span>
                                    <span class="text-sm">{{ item.label }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ formatNumber(item.value) }}</span>
                                    <Badge :variant="getStatusBadgeVariant(item.label)" class="text-xs">
                                        {{ formatPercent((item.value / metrics.total) * 100) }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Status Bar Chart -->
        <Card>
            <CardHeader>
                <CardTitle>Customer Status Overview</CardTitle>
                <CardDescription>All customer statuses at a glance</CardDescription>
            </CardHeader>
            <CardContent>
                <ChartContainer :config="statusChartConfig" class="h-[200px]">
                    <VisXYContainer :data="statusBarData" :margin="{ top: 10, bottom: 10 }">
                        <VisStackedBar
                            :x="(_: any, i: number) => i"
                            :y="(d: any) => d.value"
                            :color="(_: any, i: number) => {
                                const colors = [
                                    statusChartConfig.new.color,
                                    statusChartConfig.active.color,
                                    statusChartConfig.returning.color,
                                    statusChartConfig.atRisk.color,
                                    statusChartConfig.churned.color,
                                    statusChartConfig.vip.color,
                                ];
                                return colors[i % colors.length];
                            }"
                            :barPadding="0.3"
                            :roundedCorners="4"
                        />
                        <VisAxis
                            type="x"
                            :tick-line="false"
                            :domain-line="false"
                            :grid-line="false"
                            :tick-format="(i: number) => statusBarData[i]?.label || ''"
                        />
                        <VisAxis
                            type="y"
                            :num-ticks="5"
                            :tick-line="false"
                            :domain-line="false"
                        />
                        <ChartTooltip />
                        <ChartCrosshair
                            :template="componentToString(statusChartConfig, ChartTooltipContent, { indicator: 'dot' })"
                        />
                    </VisXYContainer>
                </ChartContainer>
            </CardContent>
        </Card>
    </div>
</template>
