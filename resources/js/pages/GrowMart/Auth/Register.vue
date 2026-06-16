<template>
    <Head title="Create Account - GrowMart" />
    <div class="min-h-screen bg-emerald-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
                <p class="text-gray-500 mt-1">Join GrowMart for fresh groceries delivered to your door</p>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input v-model="form.name" type="text" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                        :class="{ 'border-red-500': form.errors.name }" />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors" />
                </div>
                <button type="submit" :disabled="form.processing"
                    class="w-full py-2.5 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                    Create Account
                </button>
            </form>
            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account?
                <Link :href="route('growmart.login')" class="text-emerald-600 hover:text-emerald-700 font-medium">Sign in</Link>
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('growmart.register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
