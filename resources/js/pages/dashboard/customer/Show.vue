<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Mail,
    Phone,
    MapPin,
    Calendar,
    User,
    Shield,
    ShieldCheck,
    ShieldOff,
    Edit,
    Trash2,
    CheckCircle,
    XCircle,
    Clock,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Separator } from '@/components/ui/separator';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

interface Outlet {
    id: number;
    name: string;
}

interface Wallet {
    id: number;
    balance: number;
    currency: string;
}

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    date_of_birth: string | null;
    gender: string | null;
    status: 'active' | 'inactive' | 'suspended';
    avatar: string | null;
    email_verified_at: string | null;
    two_factor_enabled: boolean;
    referral_code: string | null;
    outlet: Outlet | null;
    wallet: Wallet | null;
    referrer: Customer | null;
    referrals: Customer[];
    created_at: string;
    updated_at: string;
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Customers', href: '/dashboard/customers' },
    { title: props.customer?.name ?? 'Customer', href: `/dashboard/customers/${props.customer?.id}` },
];

const deleting = ref(false);

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        case 'suspended':
            return 'destructive';
        default:
            return 'outline';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'active':
            return CheckCircle;
        case 'inactive':
            return Clock;
        case 'suspended':
            return XCircle;
        default:
            return Clock;
    }
};

const getInitials = (name: string | undefined | null) => {
    if (!name) return '??';
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const formatDate = (date: string | null) => {
    if (!date) return 'Not set';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

const handleDelete = () => {
    deleting.value = true;
    router.delete(`/dashboard/customers/${props.customer.id}`, {
        onFinish: () => {
            deleting.value = false;
        },
    });
};

const handleStatusChange = (action: 'activate' | 'deactivate' | 'suspend') => {
    router.patch(`/dashboard/customers/${props.customer.id}/${action}`);
};

const handleVerifyEmail = () => {
    router.patch(`/dashboard/customers/${props.customer.id}/verify-email`);
};

const handleToggleTwoFactor = () => {
    if (props.customer.two_factor_enabled) {
        router.delete(`/dashboard/customers/${props.customer.id}/disable-2fa`);
    } else {
        router.post(`/dashboard/customers/${props.customer.id}/enable-2fa`);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="customer.name" />

        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/dashboard/customers">
                        <Button variant="outline" size="icon">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div class="flex items-center gap-4">
                        <Avatar class="h-16 w-16">
                            <AvatarImage v-if="customer.avatar" :src="customer.avatar" :alt="customer.name" />
                            <AvatarFallback class="text-lg">{{ getInitials(customer.name) }}</AvatarFallback>
                        </Avatar>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-bold">{{ customer.name }}</h1>
                                <Badge :variant="getStatusVariant(customer.status)">
                                    <component :is="getStatusIcon(customer.status)" class="mr-1 h-3 w-3" />
                                    {{ customer.status }}
                                </Badge>
                            </div>
                            <p class="text-muted-foreground">{{ customer.email }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="`/dashboard/customers/${customer.id}/edit`">
                        <Button variant="outline">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                    </Link>
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Delete Customer</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Are you sure you want to delete {{ customer.name }}? This action cannot be undone.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction
                                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                    :disabled="deleting"
                                    @click="handleDelete"
                                >
                                    {{ deleting ? 'Deleting...' : 'Delete' }}
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Contact Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Information</CardTitle>
                            <CardDescription>Customer contact details</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <Mail class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted-foreground">Email</p>
                                        <p class="font-medium">{{ customer.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <Phone class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted-foreground">Phone</p>
                                        <p class="font-medium">{{ customer.phone || 'Not set' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 sm:col-span-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <MapPin class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted-foreground">Address</p>
                                        <p class="font-medium">{{ customer.address || 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Personal Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Personal Information</CardTitle>
                            <CardDescription>Additional customer details</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <Calendar class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted-foreground">Date of Birth</p>
                                        <p class="font-medium">{{ formatDate(customer.date_of_birth) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <User class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted-foreground">Gender</p>
                                        <p class="font-medium capitalize">{{ customer.gender || 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Referrals -->
                    <Card v-if="customer.referrals && customer.referrals.length > 0">
                        <CardHeader>
                            <CardTitle>Referrals</CardTitle>
                            <CardDescription>
                                Customers referred by {{ customer.name }} ({{ customer.referrals.length }})
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="referral in customer.referrals"
                                    :key="referral.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <Avatar class="h-8 w-8">
                                            <AvatarFallback class="text-xs">{{ getInitials(referral.name) }}</AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <p class="font-medium">{{ referral.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ referral.email }}</p>
                                        </div>
                                    </div>
                                    <Badge :variant="getStatusVariant(referral.status)">
                                        {{ referral.status }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status & Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Status & Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <p class="text-sm text-muted-foreground">Current Status</p>
                                <Badge :variant="getStatusVariant(customer.status)" class="text-sm">
                                    <component :is="getStatusIcon(customer.status)" class="mr-1 h-4 w-4" />
                                    {{ customer.status }}
                                </Badge>
                            </div>
                            <Separator />
                            <div class="flex flex-col gap-2">
                                <Button
                                    v-if="customer.status !== 'active'"
                                    variant="outline"
                                    class="w-full justify-start"
                                    @click="handleStatusChange('activate')"
                                >
                                    <CheckCircle class="mr-2 h-4 w-4 text-green-500" />
                                    Activate
                                </Button>
                                <Button
                                    v-if="customer.status !== 'inactive'"
                                    variant="outline"
                                    class="w-full justify-start"
                                    @click="handleStatusChange('deactivate')"
                                >
                                    <Clock class="mr-2 h-4 w-4 text-yellow-500" />
                                    Deactivate
                                </Button>
                                <Button
                                    v-if="customer.status !== 'suspended'"
                                    variant="outline"
                                    class="w-full justify-start"
                                    @click="handleStatusChange('suspend')"
                                >
                                    <XCircle class="mr-2 h-4 w-4 text-red-500" />
                                    Suspend
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Security -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Security</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Email Verified</span>
                                </div>
                                <Badge :variant="customer.email_verified_at ? 'default' : 'secondary'">
                                    {{ customer.email_verified_at ? 'Verified' : 'Not Verified' }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Shield class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Two-Factor Auth</span>
                                </div>
                                <Badge :variant="customer.two_factor_enabled ? 'default' : 'secondary'">
                                    {{ customer.two_factor_enabled ? 'Enabled' : 'Disabled' }}
                                </Badge>
                            </div>
                            <Separator />
                            <div class="flex flex-col gap-2">
                                <Button
                                    v-if="!customer.email_verified_at"
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    @click="handleVerifyEmail"
                                >
                                    <ShieldCheck class="mr-2 h-4 w-4" />
                                    Verify Email
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    @click="handleToggleTwoFactor"
                                >
                                    <component
                                        :is="customer.two_factor_enabled ? ShieldOff : ShieldCheck"
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{ customer.two_factor_enabled ? 'Disable 2FA' : 'Enable 2FA' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Wallet -->
                    <Card v-if="customer.wallet">
                        <CardHeader>
                            <CardTitle>Wallet</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center">
                                <p class="text-3xl font-bold">
                                    {{ formatCurrency(customer.wallet.balance, customer.wallet.currency) }}
                                </p>
                                <p class="text-sm text-muted-foreground">Available Balance</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Referral Code -->
                    <Card v-if="customer.referral_code">
                        <CardHeader>
                            <CardTitle>Referral Code</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-lg bg-muted p-3 text-center">
                                <code class="text-lg font-mono font-bold">{{ customer.referral_code }}</code>
                            </div>
                            <div v-if="customer.referrer" class="mt-3">
                                <p class="text-sm text-muted-foreground">Referred by:</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <Avatar class="h-6 w-6">
                                        <AvatarFallback class="text-xs">{{ getInitials(customer.referrer.name) }}</AvatarFallback>
                                    </Avatar>
                                    <span class="text-sm font-medium">{{ customer.referrer.name }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timestamps -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Activity</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <p class="text-sm text-muted-foreground">Created</p>
                                <p class="text-sm font-medium">{{ formatDateTime(customer.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Last Updated</p>
                                <p class="text-sm font-medium">{{ formatDateTime(customer.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
