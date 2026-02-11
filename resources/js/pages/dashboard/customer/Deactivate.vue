<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { UserX } from 'lucide-vue-next';

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
const reason = ref('');

const form = useForm({
    confirmed: false,
    reason: '',
});

watch([confirmed, reason], () => {
    form.confirmed = confirmed.value;
    form.reason = reason.value;
});

const canSubmit = computed(() => confirmed.value === true);

const handleConfirmedChange = (value: boolean | 'indeterminate') => {
    confirmed.value = value === true;
};

const handleSubmit = () => {
    form.patch(`/dashboard/customers/${props.customer.id}/deactivate`, {
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
        title="Deactivate Customer"
        description="Temporarily disable customer account"
        mode="edit"
        size="md"
        submit-text="Deactivate Customer"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Warning Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900 dark:bg-yellow-950">
                <UserX class="mt-0.5 h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        You are about to deactivate this customer
                    </p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        <strong>{{ customer.name }}</strong> ({{ customer.email }}) will be temporarily disabled.
                    </p>
                </div>
            </div>

            <!-- Reason Field (Optional) -->
            <div class="space-y-2">
                <Label for="reason">
                    Reason for deactivation <span class="text-muted-foreground">(optional)</span>
                </Label>
                <Textarea
                    id="reason"
                    v-model="reason"
                    placeholder="Enter the reason for deactivating this customer..."
                    rows="3"
                />
                <p v-if="form.errors.reason" class="text-sm text-destructive">
                    {{ form.errors.reason }}
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
                        I confirm this deactivation
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        I understand that this customer will lose access until reactivated.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.confirmed" class="text-sm text-destructive">
                {{ form.errors.confirmed }}
            </p>
        </div>
    </ModalForm>
</template>
