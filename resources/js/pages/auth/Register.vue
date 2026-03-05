<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import PasswordStrengthIndicator from '@/components/PasswordStrengthIndicator.vue';
import { ref } from 'vue';

// Get referral code from URL query parameter
const urlParams = new URLSearchParams(window.location.search);
const referralCode = urlParams.get('ref') || '';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    referral_code: referralCode,
});

const submit = () => {
    form.post(route('register'), {
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
    <AuthBase title="Create account" description="Join MyGrowNet today">
        <Head title="Register" />

        <!-- Error Message -->
        <div v-if="form.hasErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm font-medium text-red-800 mb-1">Please fix the following:</p>
            <ul class="text-sm text-red-700 space-y-0.5 ml-4">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <Label for="name">Full Name</Label>
                <Input
                    id="name"
                    type="text"
                    autofocus
                    autocomplete="name"
                    v-model="form.name"
                    placeholder="John Doe"
                    class="mt-1"
                />
                <InputError :message="form.errors.name" class="mt-1" />
            </div>

            <div>
                <Label for="email">Email <span class="text-gray-400 text-xs">(or phone below)</span></Label>
                <Input
                    id="email"
                    type="email"
                    autocomplete="email"
                    v-model="form.email"
                    placeholder="you@example.com"
                    class="mt-1"
                />
                <InputError :message="form.errors.email" class="mt-1" />
            </div>

            <div>
                <Label for="phone">Phone <span class="text-gray-400 text-xs">(or email above)</span></Label>
                <Input
                    id="phone"
                    type="tel"
                    autocomplete="tel"
                    v-model="form.phone"
                    placeholder="0977123456"
                    class="mt-1"
                />
                <InputError :message="form.errors.phone" class="mt-1" />
            </div>

            <div v-if="referralCode">
                <Label for="referral_code">Referral Code</Label>
                <Input
                    id="referral_code"
                    type="text"
                    v-model="form.referral_code"
                    placeholder="Referral code"
                    class="mt-1"
                    readonly
                />
                <p class="text-xs text-green-600 mt-1">✓ Referral code applied</p>
            </div>

            <div>
                <Label for="password">Password</Label>
                <div class="relative mt-1">
                    <Input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Create a secure password"
                        class="pr-10"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                        <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <PasswordStrengthIndicator :password="form.password" class="mt-2" />
                <InputError :message="form.errors.password" class="mt-1" />
            </div>

            <Button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-5"
                :disabled="form.processing"
            >
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                Create account
            </Button>

            <div class="text-center text-sm text-gray-600 pt-2">
                Already have an account?
                <TextLink :href="route('login')" class="text-blue-600 hover:text-blue-700 font-medium">Sign in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
