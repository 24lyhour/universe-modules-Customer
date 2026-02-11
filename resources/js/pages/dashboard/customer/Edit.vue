<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import CustomerForm from '../../components/CustomerForm.vue';
import type { CustomerFormData } from '../../components/CustomerForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    date_of_birth: string | null;
    gender: string | null;
    status: 'active' | 'inactive' | 'suspended';
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

// Get modal controls from momentum-modal
const { show, close, redirect } = useModal();

// Create a computed property for v-model:open binding
const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const form = useForm<CustomerFormData>({
    name: props.customer.name,
    email: props.customer.email,
    phone: props.customer.phone || '',
    address: props.customer.address || '',
    date_of_birth: props.customer.date_of_birth || '',
    gender: props.customer.gender || '',
    status: props.customer.status,
    password: '',
    password_confirmation: '',
});

const handleSubmit = () => {
    form.put(`/dashboard/customers/${props.customer.id}`, {
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
        :title="`Edit ${customer.name}`"
        description="Update customer information"
        mode="edit"
        size="2xl"
        submit-text="Save Changes"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <CustomerForm v-model="form" mode="edit" />
    </ModalForm>
</template>
