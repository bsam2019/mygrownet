<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimeEdgeAuthLayout from '@/layouts/PrimeEdgeAuthLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('primeedge.login.store'), {
        onError: () => form.reset('password'),
    });
};
</script>

<template>
    <PrimeEdgeAuthLayout title="Welcome Back" description="Sign in to your PrimeEdge Advisory account">
        <Head title="Sign In - PrimeEdge Advisory" />

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="login-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="form.email" id="login-email" type="email" autocomplete="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="you@example.com" />
                <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <label for="login-password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input v-model="form.password" id="login-password" type="password" autocomplete="current-password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Your password" />
                <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input v-model="form.remember" id="login-remember" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                    <span class="text-sm text-gray-600">Remember me</span>
                </label>
            </div>
<button type="submit" :disabled="form.processing" class="w-full px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-md">
                {{ form.processing ? 'Signing in...' : 'Sign In' }}
            </button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-6">
            Don't have an account? <Link :href="route('primeedge.register')" class="text-emerald-700 font-medium hover:underline">Get started</Link>
        </p>
    </PrimeEdgeAuthLayout>
</template>