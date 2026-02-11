<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import type { InertiaForm } from '@inertiajs/vue3';

export interface CustomerFormData {
    name: string;
    email: string;
    phone: string;
    address: string;
    date_of_birth: string;
    gender: string;
    status: 'active' | 'inactive' | 'suspended';
    password: string;
    password_confirmation: string;
}

interface Props {
    mode?: 'create' | 'edit';
}

withDefaults(defineProps<Props>(), {
    mode: 'create',
});

const model = defineModel<InertiaForm<CustomerFormData>>({ required: true });
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Information Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Basic Information</h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? "Enter the customer's basic details" : "Update the customer's basic details" }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <Label for="name">Full Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="name"
                        v-model="model.name"
                        type="text"
                        placeholder="John Doe"
                    />
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="email">Email Address <span class="text-destructive">*</span></Label>
                    <Input
                        id="email"
                        v-model="model.email"
                        type="email"
                        placeholder="john@example.com"
                    />
                    <p v-if="model.errors.email" class="text-sm text-destructive">
                        {{ model.errors.email }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="phone">Phone Number</Label>
                    <Input
                        id="phone"
                        v-model="model.phone"
                        type="tel"
                        placeholder="+1234567890"
                    />
                    <p v-if="model.errors.phone" class="text-sm text-destructive">
                        {{ model.errors.phone }}
                    </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <Label for="address">Address</Label>
                    <Input
                        id="address"
                        v-model="model.address"
                        type="text"
                        placeholder="123 Main St, City"
                    />
                    <p v-if="model.errors.address" class="text-sm text-destructive">
                        {{ model.errors.address }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Information Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Additional Information</h3>
                <p class="text-sm text-muted-foreground">Optional details and settings</p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="date_of_birth">Date of Birth</Label>
                    <Input
                        id="date_of_birth"
                        v-model="model.date_of_birth"
                        type="date"
                    />
                    <p v-if="model.errors.date_of_birth" class="text-sm text-destructive">
                        {{ model.errors.date_of_birth }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="gender">Gender</Label>
                    <Select v-model="model.gender">
                        <SelectTrigger>
                            <SelectValue placeholder="Select gender" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="male">Male</SelectItem>
                            <SelectItem value="female">Female</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.gender" class="text-sm text-destructive">
                        {{ model.errors.gender }}
                    </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <Label for="status">Status</Label>
                    <Select v-model="model.status">
                        <SelectTrigger>
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                            <SelectItem value="suspended">Suspended</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.status" class="text-sm text-destructive">
                        {{ model.errors.status }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Password Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">
                    {{ mode === 'create' ? 'Password' : 'Change Password' }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? 'Set a password for the customer account' : 'Leave blank to keep current password' }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="password">
                        {{ mode === 'create' ? 'Password' : 'New Password' }}
                        <span v-if="mode === 'create'" class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="password"
                        v-model="model.password"
                        type="password"
                        :placeholder="mode === 'create' ? 'Enter password' : 'Enter new password'"
                    />
                    <p v-if="model.errors.password" class="text-sm text-destructive">
                        {{ model.errors.password }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">
                        Confirm Password
                        <span v-if="mode === 'create'" class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="password_confirmation"
                        v-model="model.password_confirmation"
                        type="password"
                        :placeholder="mode === 'create' ? 'Confirm password' : 'Confirm new password'"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
