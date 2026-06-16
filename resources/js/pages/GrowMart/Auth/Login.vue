<template>
    <Head title="Sign In - GrowMart" />
    <div class="min-h-screen bg-emerald-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
                <p class="text-gray-500 mt-1">Sign in to your GrowMart account</p>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                        :class="{ 'border-red-500': form.errors.email }" />
                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input v-model="form.password" type="password" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                        :class="{ 'border-red-500': form.errors.password }" />
                    <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2">
                        <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                </div>
                <button type="submit" :disabled="form.processing"
                    class="w-full py-2.5 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                    Sign In
                </button>
            </form>
            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account?
                <Link :href="route('growmart.register')" class="text-emerald-600 hover:text-emerald-700 font-medium">Create one</Link>
            </p>
            <div class="mt-4 text-center">
                <Link :href="route('growmart.home')" class="text-sm text-gray-400 hover:text-gray-600">← Back to store</Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('growmart.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
