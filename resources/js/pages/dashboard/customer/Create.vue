<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import CustomerForm from '../../components/CustomerForm.vue';
import type { CustomerFormData } from '../../components/CustomerForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';

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
    name: '',
    email: '',
    phone: '',
    address: '',
    date_of_birth: '',
    gender: '',
    status: 'active',
    password: '',
    password_confirmation: '',
});

const handleSubmit = () => {
    form.post('/dashboard/customers', {
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
        title="Create Customer"
        description="Add a new customer to the system"
        mode="create"
        size="2xl"
        submit-text="Create Customer"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <CustomerForm v-model="form" mode="create" />
    </ModalForm>
</template>
