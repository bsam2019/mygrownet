<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
        onError: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });
};
</script>

<template>
    <AuthBase title="Welcome back" description="Sign in to your account">
        <Head title="Log in" />

        <!-- Status Message -->
        <div v-if="status" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            {{ status }}
        </div>

        <!-- Error Message -->
        <div v-if="form.hasErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            {{ form.errors.email || form.errors.password }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <Label for="email">Email or Phone</Label>
                <Input
                    id="email"
                    type="text"
                    autofocus
                    autocomplete="username"
                    v-model="form.email"
                    placeholder="email@example.com or 0977123456"
                    class="mt-1"
                />
                <InputError :message="form.errors.email" class="mt-1" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <Label for="password">Password</Label>
                    <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-xs text-blue-600 hover:text-blue-700">
                        Forgot?
                    </TextLink>
                </div>
                <Input
                    id="password"
                    type="password"
                    autocomplete="current-password"
                    v-model="form.password"
                    placeholder="Enter your password"
                    class="mt-1"
                />
                <InputError :message="form.errors.password" class="mt-1" />
            </div>

            <Button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-5"
                :disabled="form.processing"
            >
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                Sign in
            </Button>

            <div class="text-center text-sm text-gray-600 pt-2">
                Don't have an account?
                <TextLink :href="route('register')" class="text-blue-600 hover:text-blue-700 font-medium">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
