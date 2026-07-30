<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import VenturesGuestLayout from '@/layouts/VenturesGuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{ status?: string }>();

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('venture.sub.password.email'), {
        onFinish: () => form.reset(),
        onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
    });
};
</script>

<template>
    <VenturesGuestLayout>
        <Head title="Forgot Password - Venture Builder" />
        <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Reset your password</h2>
                    <p class="text-gray-600 mt-1">Enter your email and we'll send you a reset link</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                    <div v-if="status" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ status }}</div>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" autofocus autocomplete="email" v-model="form.email" placeholder="you@example.com" class="mt-1" />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                        <Button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-medium py-5 shadow-sm" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Send reset link
                        </Button>
                    </form>
                    <div class="text-center mt-6">
                        <Link :href="route('venture.sub.login')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">&larr; Back to sign in</Link>
                    </div>
                </div>
                <div class="text-center mt-6">
                    <Link :href="route('venture.sub.welcome')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to home</Link>
                </div>
            </div>
        </div>
    </VenturesGuestLayout>
</template>
