<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, AlertCircle } from 'lucide-vue-next';
import { Transition } from 'vue';

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
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <!-- Success Message -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 transform scale-95"
            enter-to-class="opacity-100 transform scale-100"
        >
            <div v-if="status" class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-green-900">{{ status }}</p>
                </div>
            </div>
        </Transition>

        <!-- Error Alert -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 transform scale-95"
            enter-to-class="opacity-100 transform scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 transform scale-100"
            leave-to-class="opacity-0 transform scale-95"
        >
            <div v-if="form.hasErrors" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <AlertCircle class="h-5 w-5 text-red-600" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-red-900 mb-1">Unable to sign in</h3>
                        <p class="text-sm text-red-700">
                            {{ form.errors.email || form.errors.password }}
                        </p>
                    </div>
                </div>
            </div>
        </Transition>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email or Phone Number</Label>
                    <Input
                        id="email"
                        type="text"
                        autofocus
                        autocomplete="username"
                        v-model="form.email"
                        placeholder="email@example.com or 0977123456"
                    />
                    <p class="text-xs text-gray-700 mt-1">
                        Enter your email address or phone number (e.g., 0977123456 or +260977123456)
                    </p>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm text-blue-600 hover:text-blue-700">
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Enter your password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox id="remember" v-model:checked="form.remember" class="border-gray-300 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600" />
                    <Label for="remember">Remember me</Label>
                </div>

                <Button
                    type="submit"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-300 py-6"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Sign in to your account
                </Button>
            </div>

            <div class="text-center text-gray-700">
                Don't have an account?
                <TextLink :href="route('register')" class="text-blue-600 hover:text-blue-700 font-medium">Create an account</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
