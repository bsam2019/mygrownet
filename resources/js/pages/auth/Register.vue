<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, AlertCircle } from 'lucide-vue-next';
import PasswordStrengthIndicator from '@/components/PasswordStrengthIndicator.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <!-- Error Alert -->
        <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
            <AlertCircle class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" />
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-red-800 mb-1">Registration Failed</h3>
                <p class="text-sm text-red-700 mb-2">Please correct the following errors:</p>
                <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                </ul>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Full Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        v-model="form.name"
                        placeholder="Enter your full name"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.name ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200'
                        ]"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="you@example.com"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.email ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200'
                        ]"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Create a secure password"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.password ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200'
                        ]"
                    />
                    <PasswordStrengthIndicator :password="form.password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm your password"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.password_confirmation ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200'
                        ]"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-300 py-6"
                    :tabindex="5"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Create your account
                </Button>
            </div>

            <div class="text-center text-gray-600">
                Already have an account?
                <TextLink :href="route('login')" class="text-blue-600 hover:text-blue-700 font-medium ml-1" :tabindex="6">
                    Sign in instead
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
