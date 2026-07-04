<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import GrowStorageGuestLayout from '@/layouts/GrowStorageGuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{ status?: string; canResetPassword: boolean }>();

const currentUrl = encodeURIComponent(window.location.href);

const form = useForm({ email: '', password: '', remember: false });

const submit = () => {
    form.post(route('growstorage.sub.login'), {
        onFinish: () => form.reset('password'),
        onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
    });
};
</script>

<template>
    <GrowStorageGuestLayout>
        <Head title="Sign in - GrowStorage" />
        <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                    <p class="text-gray-600 mt-1">Sign in with your MyGrowNet account</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                    <div class="mb-5 p-3 bg-cyan-50 border border-cyan-200 rounded-lg">
                        <p class="text-xs text-cyan-800 leading-relaxed">
                            GrowStorage uses your <strong>MyGrowNet</strong> account.
                            Already have one? Sign in below.
                            New here?
                            <Link :href="route('growstorage.sub.register')" class="text-cyan-700 font-medium hover:underline">Create an account</Link>
                            &mdash; it's shared across all MyGrowNet services.
                        </p>
                    </div>
                    <div v-if="status" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ status }}</div>
                    <div v-if="form.hasErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">{{ form.errors.email || form.errors.password }}</div>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="email">Email or Phone</Label>
                            <Input id="email" type="text" autofocus autocomplete="username" v-model="form.email" placeholder="email@example.com or 0977123456" class="mt-1" />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <Label for="password">Password</Label>
                                <Link :href="route('growstorage.sub.password.request')" class="text-xs text-cyan-600 hover:text-cyan-700 font-medium">Forgot?</Link>
                            </div>
                            <Input id="password" type="password" autocomplete="current-password" v-model="form.password" placeholder="Enter your password" class="mt-1" />
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>
                        <Button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-medium py-5 shadow-sm" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Sign in
                        </Button>
                        <div class="relative my-5">
                            <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-200"></span></div>
                            <div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-gray-500">or continue with</span></div>
                        </div>
                        <a :href="`/auth/google?redirect=${currentUrl}`" class="flex items-center justify-center gap-3 w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Sign in with Google
                        </a>
                        <div class="text-center text-sm text-gray-600 pt-2">
                            Don't have an account?
                            <Link :href="route('growstorage.sub.register')" class="text-cyan-600 hover:text-cyan-700 font-medium">Sign up</Link>
                        </div>
                    </form>
                </div>
                <div class="text-center mt-6">
                    <Link :href="route('growstorage.sub.welcome')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to home</Link>
                </div>
            </div>
        </div>
    </GrowStorageGuestLayout>
</template>
