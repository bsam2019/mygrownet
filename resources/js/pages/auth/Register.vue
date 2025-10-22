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
import { Transition } from 'vue';

// Get referral code from URL query parameter
const urlParams = new URLSearchParams(window.location.search);
const referralCode = urlParams.get('ref') || '';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    referral_code: referralCode,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
        onError: () => {
            // Scroll to top to show error alert
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

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
                        <h3 class="text-sm font-semibold text-red-900 mb-2">Unable to create account</h3>
                        <ul class="space-y-1">
                            <li v-for="(error, field) in form.errors" :key="field" class="text-sm text-red-700 flex items-start gap-2">
                                <span class="text-red-500 mt-0.5">•</span>
                                <span>{{ error }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </Transition>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Full Name</Label>
                    <Input
                        id="name"
                        type="text"
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
                    <Label for="email">
                        Email Address 
                        <span class="text-gray-400 font-normal">(Optional if phone provided)</span>
                    </Label>
                    <Input
                        id="email"
                        type="email"
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
                    <Label for="phone">
                        Phone Number 
                        <span class="text-gray-400 font-normal">(Optional if email provided)</span>
                    </Label>
                    <Input
                        id="phone"
                        type="tel"
                        :tabindex="3"
                        autocomplete="tel"
                        v-model="form.phone"
                        placeholder="0977123456 or +260977123456"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.phone ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200'
                        ]"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        Enter your Zambian phone number (e.g., 0977123456)
                    </p>
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-700">
                        <strong>Note:</strong> You must provide at least one - either email or phone number (or both).
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="referral_code">
                        Referral Code 
                        <span class="text-gray-400 font-normal">(Optional)</span>
                    </Label>
                    <Input
                        id="referral_code"
                        type="text"
                        :tabindex="4"
                        v-model="form.referral_code"
                        placeholder="Enter referral code if you have one"
                        :class="[
                            'bg-gray-50 focus-visible:ring-blue-500',
                            form.errors.referral_code ? 'border-red-300 focus-visible:ring-red-500' : 'border-gray-200',
                            referralCode ? 'border-green-300 bg-green-50' : ''
                        ]"
                    />
                    <p v-if="referralCode" class="text-xs text-green-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Referral code applied from invitation link
                    </p>
                    <p v-else class="text-xs text-gray-500 mt-1">
                        If someone referred you, enter their code here
                    </p>
                    <InputError :message="form.errors.referral_code" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        :tabindex="5"
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
                        :tabindex="6"
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
                    :tabindex="7"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Create your account
                </Button>
            </div>

            <div class="text-center text-gray-600">
                Already have an account?
                <TextLink :href="route('login')" class="text-blue-600 hover:text-blue-700 font-medium ml-1" :tabindex="8">
                    Sign in instead
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
