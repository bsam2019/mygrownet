<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import BizBoostGuestLayout from '@/layouts/BizBoostGuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import PasswordStrengthIndicator from '@/components/PasswordStrengthIndicator.vue';
import { ref } from 'vue';

defineProps<{
    status?: string;
}>();

const showPassword = ref(false);
const contactInput = ref('');

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    referral_code: '',
});

const handleContactInput = (event: Event) => {
    const value = (event.target as HTMLInputElement).value.trim();
    contactInput.value = value;
    form.email = '';
    form.phone = '';
    if (!value) return;
    if (value.includes('@')) {
        form.email = value;
    } else {
        form.phone = value;
    }
};

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
    <BizBoostGuestLayout>
        <Head title="Sign up - BizBoost" />

        <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                    <p class="text-gray-600 mt-1">Start growing your business with BizBoost</p>
                </div>

                <!-- Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
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
                            <Label for="contact">Email or Phone</Label>
                            <Input
                                id="contact"
                                type="text"
                                v-model="contactInput"
                                @input="handleContactInput"
                                placeholder="you@example.com or 0977123456"
                                autocomplete="username"
                                class="mt-1"
                            />
                            <InputError :message="form.errors.email || form.errors.phone" class="mt-1" />
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

                        <div>
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                placeholder="Confirm your password"
                                class="mt-1"
                            />
                            <InputError :message="form.errors.password_confirmation" class="mt-1" />
                        </div>

                        <Button
                            type="submit"
                            class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium py-5 shadow-sm"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Create account
                        </Button>

                        <div class="text-center text-sm text-gray-600 pt-2">
                            Already have an account?
                            <Link :href="route('bizboost.sub.login')" class="text-purple-600 hover:text-purple-700 font-medium">Sign in</Link>
                        </div>
                    </form>

                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 text-center leading-relaxed">
                            By creating an account, you agree to our
                            <Link :href="route('bizboost.sub.terms')" class="text-purple-600 hover:text-purple-700">Terms of Service</Link>
                            and
                            <Link :href="route('bizboost.sub.privacy')" class="text-purple-600 hover:text-purple-700">Privacy Policy</Link>.
                            Your account is shared across the MyGrowNet platform.
                        </p>
                    </div>
                </div>

                <!-- Back to welcome -->
                <div class="text-center mt-6">
                    <Link :href="route('bizboost.sub.welcome')" class="text-sm text-gray-500 hover:text-gray-700">
                        &larr; Back to home
                    </Link>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
