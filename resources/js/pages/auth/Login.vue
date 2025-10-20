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
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- Error Alert -->
        <div v-if="form.errors.email || form.errors.password" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
            <AlertCircle class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" />
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-red-800 mb-1">Login Failed</h3>
                <p class="text-sm text-red-700">
                    {{ form.errors.email || form.errors.password }}
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email or Phone Number</Label>
                    <Input
                        id="email"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="username"
                        v-model="form.email"
                        placeholder="email@example.com or 0977123456"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.email ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200'
                        ]"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        Enter your email address or phone number (e.g., 0977123456 or +260977123456)
                    </p>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm text-blue-600 hover:text-blue-700" :tabindex="5">
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Enter your password"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.password ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200'
                        ]"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center space-x-2" :tabindex="3">
                    <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4" class="border-gray-300 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600" />
                    <Label for="remember" class="text-gray-600">Remember me</Label>
                </div>

                <Button
                    type="submit"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-300 py-6"
                    :tabindex="4"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Sign in to your account
                </Button>
            </div>

            <div class="text-center text-gray-600">
                Don't have an account?
                <TextLink :href="route('register')" class="text-blue-600 hover:text-blue-700 font-medium" :tabindex="5">Create an account</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
