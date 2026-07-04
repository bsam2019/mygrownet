<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import GrowNetGuestLayout from '@/layouts/GrowNetGuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import PasswordStrengthIndicator from '@/components/PasswordStrengthIndicator.vue';
import { ref } from 'vue';

defineProps<{ status?: string }>();

const showPassword = ref(false);
const contactInput = ref('');
const currentUrl = encodeURIComponent(window.location.href);

const form = useForm({ name: '', email: '', phone: '', password: '', password_confirmation: '', referral_code: '' });

const handleContactInput = (event: Event) => {
    const value = (event.target as HTMLInputElement).value.trim();
    contactInput.value = value;
    form.email = '';
    form.phone = '';
    if (!value) return;
    if (value.includes('@')) { form.email = value; } else { form.phone = value; }
};

const submit = () => {
    form.post(route('grownet.sub.register'), {
        onFinish: () => form.reset('password'),
        onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
    });
};
</script>

<template>
    <GrowNetGuestLayout>
        <Head title="Sign up - GrowNet" />
        <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-700 to-indigo-800 flex items-center justify-center shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                    <p class="text-gray-600 mt-1">Start learning with lessons, videos, e-books, and more</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                    <div v-if="form.hasErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm font-medium text-red-800 mb-1">Please fix the following:</p>
                        <ul class="text-sm text-red-700 space-y-0.5 ml-4">
                            <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                        </ul>
                    </div>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="name">Full Name</Label>
                            <Input id="name" type="text" autofocus autocomplete="name" v-model="form.name" placeholder="John Doe" class="mt-1" />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <Label for="contact">Email or Phone</Label>
                            <Input id="contact" type="text" v-model="contactInput" @input="handleContactInput" placeholder="you@example.com or 0977123456" autocomplete="username" class="mt-1" />
                            <InputError :message="form.errors.email || form.errors.phone" class="mt-1" />
                        </div>
                        <div>
                            <Label for="password">Password</Label>
                            <div class="relative mt-1">
                                <Input id="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" v-model="form.password" placeholder="Create a secure password" class="pr-10" />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                            <PasswordStrengthIndicator :password="form.password" class="mt-2" />
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>
                        <div>
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" type="password" autocomplete="new-password" v-model="form.password_confirmation" placeholder="Confirm your password" class="mt-1" />
                            <InputError :message="form.errors.password_confirmation" class="mt-1" />
                        </div>
                        <Button type="submit" class="w-full bg-gradient-to-r from-slate-700 to-indigo-800 hover:from-slate-800 hover:to-indigo-900 text-white font-medium py-5 shadow-sm" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Create account
                        </Button>
                        <div class="relative my-5">
                            <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-200"></span></div>
                            <div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-gray-500">or sign up with</span></div>
                        </div>
                        <a :href="`/auth/google?redirect=${currentUrl}`" class="flex items-center justify-center gap-3 w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Sign up with Google
                        </a>
                        <div class="text-center text-sm text-gray-600 pt-2">
                            Already have an account?
                            <Link :href="route('grownet.sub.login')" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign in</Link>
                        </div>
                    </form>
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 text-center leading-relaxed">
                            GrowNet uses your <strong>MyGrowNet</strong> account.
                            Your account is shared across the MyGrowNet platform.
                        </p>
                    </div>
                </div>
                <div class="text-center mt-6">
                    <Link :href="route('grownet.sub.welcome')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to home</Link>
                </div>
            </div>
        </div>
    </GrowNetGuestLayout>
</template>
