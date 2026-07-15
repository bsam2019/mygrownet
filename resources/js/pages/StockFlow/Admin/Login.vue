<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('stockflow.admin.login.store'), {
        onError: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="StockFlow Admin Login" />

    <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-emerald-600 text-white mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-white">StockFlow Admin</h1>
                <p class="text-gray-400 text-sm mt-1">Sign in to manage companies</p>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-xl p-6 space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" id="email" type="email" autocomplete="email" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm" placeholder="admin@stockflow.com" />
                    <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input v-model="form.password" id="password" type="password" autocomplete="current-password" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm" />
                    <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium disabled:opacity-50">
                    {{ form.processing ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>
        </div>
    </div>
</template>
