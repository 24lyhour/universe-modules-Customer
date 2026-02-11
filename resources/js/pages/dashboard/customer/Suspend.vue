<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { AlertTriangle } from 'lucide-vue-next';

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

// Watch and sync refs to form
watch([confirmed, reason], () => {
    form.confirmed = confirmed.value;
    form.reason = reason.value;
});

const canSubmit = computed(() => confirmed.value === true && reason.value.trim().length > 0);

const handleConfirmedChange = (value: boolean | 'indeterminate') => {
    confirmed.value = value === true;
};

const handleSubmit = () => {
    form.patch(`/dashboard/customers/${props.customer.id}/suspend`, {
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
        title="Suspend Customer"
        description="This action will restrict customer access"
        mode="delete"
        size="md"
        submit-text="Suspend Customer"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Warning Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-amber-600 dark:text-amber-400" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                        You are about to suspend this customer
                    </p>
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        <strong>{{ customer.name }}</strong> ({{ customer.email }}) will be suspended and lose access to their account.
                    </p>
                </div>
            </div>

            <!-- Reason Field -->
            <div class="space-y-2">
                <Label for="reason">
                    Reason for suspension <span class="text-destructive">*</span>
                </Label>
                <Textarea
                    id="reason"
                    v-model="reason"
                    placeholder="Enter the reason for suspending this customer..."
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
                        I confirm this suspension
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        I understand that this customer will lose access to their account until reactivated.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.confirmed" class="text-sm text-destructive">
                {{ form.errors.confirmed }}
            </p>
        </div>
    </ModalForm>
</template>
