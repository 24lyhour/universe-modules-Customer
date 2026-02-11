<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, type VNode } from 'vue';
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    CheckCircle,
    XCircle,
    Ban,
    Shield,
    Users,
    UserCheck,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    TableReusable,
    ModalConfirm,
    StatsCard,
    type TableColumn,
    type TableAction,
} from '@/components/shared';
import { type BreadcrumbItem } from '@/types';
import type { Customer, CustomerIndexProps } from '../../../types';

// Persistent layout required for momentum-modal to work
defineOptions({
    layout: (h: (type: unknown, props: unknown, children: unknown) => VNode, page: VNode) =>
        h(AppLayout, { breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Customers', href: '/dashboard/customers' },
        ] as BreadcrumbItem[] }, () => page),
});

const props = defineProps<CustomerIndexProps>();

// Search and filters
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

// Filtered data based on search (client-side filtering for immediate feedback)
const filteredCustomers = computed(() => {
    if (!searchQuery.value) {
        return props.customers.data;
    }
    const query = searchQuery.value.toLowerCase();
    return props.customers.data.filter(
        (item) =>
            item.name.toLowerCase().includes(query) ||
            item.email.toLowerCase().includes(query) ||
            item.phone?.toLowerCase().includes(query)
    );
});

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedCustomer = ref<Customer | null>(null);

// Table columns
const columns: TableColumn<Customer>[] = [
    { key: 'name', label: 'Customer', width: '30%' },
    { key: 'phone', label: 'Phone' },
    { key: 'status', label: 'Status' },
    { key: 'email_verified', label: 'Verified' },
    { key: 'two_factor_enabled', label: '2FA' },
    { key: 'created_at', label: 'Created' },
];

// Table actions
const tableActions: TableAction<Customer>[] = [
    {
        label: 'View',
        icon: Eye,
        onClick: (item) => handleShow(item),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (item) => handleEdit(item),
    },
    {
        label: 'Activate',
        icon: CheckCircle,
        onClick: (item) => updateStatus(item, 'activate'),
        show: (item) => item.status !== 'active',
    },
    {
        label: 'Deactivate',
        icon: XCircle,
        onClick: (item) => updateStatus(item, 'deactivate'),
        show: (item) => item.status === 'active',
    },
    {
        label: 'Suspend',
        icon: Ban,
        onClick: (item) => updateStatus(item, 'suspend'),
        show: (item) => item.status !== 'suspended',
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (item) => openDeleteModal(item),
        variant: 'destructive',
        separator: true,
    },
];

/**
 * Handlers call back
 */
const handleCreate = () => {
    router.visit('/dashboard/customers/create');
};

/**
 * handle show
 * @param item 
 */
const handleShow = (item: Customer) => {
    router.visit(`/dashboard/customers/${item.id}`);
};

/**
 * handle edit
 * @param item 
 */
const handleEdit = (item: Customer) => {
    router.visit(`/dashboard/customers/${item.id}/edit`);
};

/**
 * dailog
 * @param customer
 */
const openDeleteModal = (customer: Customer) => {
    selectedCustomer.value = customer;
    isDeleteModalOpen.value = true;
};

/**
 * submit delete
 * 
 * @param id
 */
const handleDelete = () => {
    if (!selectedCustomer.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/customers/${selectedCustomer.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedCustomer.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const updateStatus = (customer: Customer, action: 'activate' | 'deactivate' | 'suspend') => {
    router.visit(`/dashboard/customers/${customer.id}/${action}`);
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/customers', {
        page,
        search: searchQuery.value,
        status: statusFilter.value,
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/customers', {
        per_page: perPage,
        search: searchQuery.value,
        status: statusFilter.value,
    }, { preserveState: true });
};

const handleStatusFilter = (status: string) => {
    statusFilter.value = status;
    router.get('/dashboard/customers', {
        search: searchQuery.value,
        status: status,
    }, { preserveState: true });
};

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
</script>

<template>
    <Head title="Customers" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Stats Cards using StatsCard component -->
        <div class="grid gap-4 md:grid-cols-4">
            <StatsCard
                title="Total Customers"
                :value="stats.total"
                :icon="Users"
                icon-color="text-muted-foreground"
            />
            <StatsCard
                title="Active"
                :value="stats.active"
                :icon="UserCheck"
                icon-color="text-green-500"
                value-color="text-green-600"
            />
            <StatsCard
                title="Verified"
                :value="stats.verified"
                :icon="CheckCircle"
                icon-color="text-blue-500"
                value-color="text-blue-600"
            />
            <StatsCard
                title="2FA Enabled"
                :value="stats.two_factor_enabled"
                :icon="Shield"
                icon-color="text-purple-500"
                value-color="text-purple-600"
            />
        </div>

        <!-- Main Card with Table -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Customers</CardTitle>
                        <CardDescription>Manage your customers</CardDescription>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Customer
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <!-- Reusable Table -->
                <TableReusable
                    v-model:search-query="searchQuery"
                    :data="filteredCustomers"
                    :columns="columns"
                    :actions="tableActions"
                    :pagination="customers.meta"
                    search-placeholder="Search customers..."
                    empty-message="No customers found."
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <!-- Toolbar slot for status filters -->
                    <template #toolbar>
                        <div class="flex gap-2">
                            <Button
                                :variant="statusFilter === '' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('')"
                            >
                                All
                            </Button>
                            <Button
                                :variant="statusFilter === 'active' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('active')"
                            >
                                Active
                            </Button>
                            <Button
                                :variant="statusFilter === 'inactive' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('inactive')"
                            >
                                Inactive
                            </Button>
                            <Button
                                :variant="statusFilter === 'suspended' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('suspended')"
                            >
                                Suspended
                            </Button>
                        </div>
                    </template>

                    <!-- Custom cell for customer name/email -->
                    <template #cell-name="{ item }">
                        <div>
                            <div class="font-medium">{{ item.name }}</div>
                            <div class="text-sm text-muted-foreground">{{ item.email }}</div>
                        </div>
                    </template>

                    <!-- Custom cell for phone -->
                    <template #cell-phone="{ item }">
                        {{ item.phone || '-' }}
                    </template>

                    <!-- Custom cell for status badge -->
                    <template #cell-status="{ item }">
                        <Badge :variant="getStatusVariant(item.status)">
                            {{ item.status }}
                        </Badge>
                    </template>

                    <!-- Custom cell for verified icon -->
                    <template #cell-email_verified="{ item }">
                        <CheckCircle
                            v-if="item.email_verified"
                            class="h-5 w-5 text-green-500"
                        />
                        <XCircle v-else class="h-5 w-5 text-muted-foreground" />
                    </template>

                    <!-- Custom cell for 2FA icon -->
                    <template #cell-two_factor_enabled="{ item }">
                        <Shield
                            v-if="item.two_factor_enabled"
                            class="h-5 w-5 text-purple-500"
                        />
                        <span v-else class="text-muted-foreground">-</span>
                    </template>

                    <!-- Custom cell for date -->
                    <template #cell-created_at="{ item }">
                        <span class="text-sm text-muted-foreground">
                            {{ new Date(item.created_at).toLocaleDateString() }}
                        </span>
                    </template>
                </TableReusable>
            </CardContent>
        </Card>
    </div>

    <!-- Delete Confirmation Modal -->
    <ModalConfirm
        v-model:open="isDeleteModalOpen"
        title="Delete Customer"
        :description="`Are you sure you want to delete '${selectedCustomer?.name}'? This action cannot be undone.`"
        confirm-text="Delete"
        variant="danger"
        :loading="isDeleting"
        @confirm="handleDelete"
    />
</template>
