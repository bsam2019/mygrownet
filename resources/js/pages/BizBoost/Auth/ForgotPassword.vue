<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('bizboost.sub.password.email'));
};
</script>

<template>
    <Head title="Forgot Password - BizBoost" />

    <div class="min-h-screen bg-gradient-to-b from-violet-50 via-white to-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-lg mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Reset your password</h2>
                <p class="text-gray-600 mt-1">Enter your email and we'll send you a reset link</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                <div v-if="status" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800 leading-relaxed">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            autofocus
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="you@example.com"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 focus:outline-none focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10"
                            :class="{ 'border-red-500 bg-red-50': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-600 mt-1.5 font-medium">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 rounded-xl transition-all shadow-sm"
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Sending reset link...' : 'Send Reset Link' }}
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('bizboost.sub.login')" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        &larr; Back to sign in
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
