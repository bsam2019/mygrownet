<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
        logo: string | null;
        theme: {
            primaryColor?: string;
            secondaryColor?: string;
        } | null;
    };
    subdomain: string;
}

const props = defineProps<Props>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

// Detect if we're on a subdomain
const isOnSubdomain = computed(() => {
    const host = window.location.hostname;
    const match = host.match(/^(?:www\.)?([a-z0-9-]+)\.mygrownet\.com$/i);
    if (match) {
        const subdomain = match[1].toLowerCase();
        const reserved = ['www', 'mygrownet', 'api', 'admin', 'mail', 'staging', 'dev', 'growbuilder', 'app', 'dashboard'];
        return !reserved.includes(subdomain);
    }
    return !host.includes('mygrownet.com') && !host.includes('localhost') && !host.includes('127.0.0.1');
});

const registerUrl = computed(() => {
    return isOnSubdomain.value ? '/register' : route('site.register', { subdomain: props.subdomain });
});

const forgotPasswordUrl = computed(() => {
    return isOnSubdomain.value ? '/forgot-password' : route('site.password.request', { subdomain: props.subdomain });
});

const submit = () => {
    form.post(route('site.login.submit', { subdomain: props.subdomain }), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="`Login - ${site.name}`" />

    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo & Title -->
            <div class="text-center">
                <img
                    v-if="site.logo"
                    :src="site.logo"
                    :alt="site.name"
                    class="mx-auto h-16 w-auto"
                />
                <div
                    v-else
                    class="mx-auto h-16 w-16 rounded-full flex items-center justify-center text-white text-2xl font-bold"
                    :style="{ backgroundColor: primaryColor }"
                >
                    {{ site.name.charAt(0) }}
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">
                    Welcome back
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Sign in to your {{ site.name }} account
                </p>
            </div>

            <!-- Login Form -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                            :style="{ '--tw-ring-color': primaryColor }"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300"
                            :style="{ accentColor: primaryColor }"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>

                    <Link
                        :href="forgotPasswordUrl"
                        class="text-sm font-medium hover:underline"
                        :style="{ color: primaryColor }"
                    >
                        Forgot password?
                    </Link>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :style="{ backgroundColor: primaryColor }"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign in</span>
                </button>

                <p class="text-center text-sm text-gray-600">
                    Don't have an account?
                    <Link
                        :href="registerUrl"
                        class="font-medium hover:underline"
                        :style="{ color: primaryColor }"
                    >
                        Sign up
                    </Link>
                </p>
            </form>
        </div>
    </div>
</template>
