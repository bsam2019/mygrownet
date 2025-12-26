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
        } | null;
    };
    subdomain: string;
    token: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: '',
    password: '',
    password_confirmation: '',
});

const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

const submit = () => {
    form.post(route('site.password.update', { subdomain: props.subdomain }), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="`Reset Password - ${site.name}`" />

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
                    Reset your password
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Enter your new password below
                </p>
            </div>

            <!-- Form -->
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
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm new password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :style="{ backgroundColor: primaryColor }"
                >
                    <span v-if="form.processing">Resetting...</span>
                    <span v-else>Reset password</span>
                </button>

                <p class="text-center text-sm text-gray-600">
                    <Link
                        :href="route('site.login', { subdomain })"
                        class="font-medium hover:underline"
                        :style="{ color: primaryColor }"
                    >
                        Back to login
                    </Link>
                </p>
            </form>
        </div>
    </div>
</template>
