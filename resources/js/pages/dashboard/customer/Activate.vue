<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { CheckCircle } from 'lucide-vue-next';

interface Customer {
    id: number;
    name: string;
    email: string;
    status: string;
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const confirmed = ref(false);

const form = useForm({
    confirmed: false,
});

watch(confirmed, () => {
    form.confirmed = confirmed.value;
});

const canSubmit = computed(() => confirmed.value === true);

const handleConfirmedChange = (value: boolean | 'indeterminate') => {
    confirmed.value = value === true;
};

const handleSubmit = () => {
    form.patch(`/dashboard/customers/${props.customer.id}/activate`, {
        onSuccess: () => {
            close();
            redirect();
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        title="Activate Customer"
        description="Restore customer account access"
        mode="create"
        size="md"
        submit-text="Activate Customer"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Info Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950">
                <CheckCircle class="mt-0.5 h-5 w-5 text-green-600 dark:text-green-400" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        You are about to activate this customer
                    </p>
                    <p class="text-sm text-green-700 dark:text-green-300">
                        <strong>{{ customer.name }}</strong> ({{ customer.email }}) will regain full access to their account.
                    </p>
                </div>
            </div>

            <!-- Current Status Info -->
            <div v-if="customer.status !== 'active'" class="rounded-lg bg-muted p-4">
                <p class="text-sm text-muted-foreground">
                    Current status:
                    <span class="font-medium capitalize" :class="{
                        'text-yellow-600': customer.status === 'inactive',
                        'text-red-600': customer.status === 'suspended',
                    }">
                        {{ customer.status }}
                    </span>
                </p>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox
                    id="confirmed"
                    :checked="confirmed"
                    @update:checked="handleConfirmedChange"
                />
                <div class="space-y-1">
                    <Label for="confirmed" class="cursor-pointer font-medium">
                        I confirm this activation
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        I understand that this customer will regain full access to their account.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.confirmed" class="text-sm text-destructive">
                {{ form.errors.confirmed }}
            </p>
        </div>
    </ModalForm>
</template>
