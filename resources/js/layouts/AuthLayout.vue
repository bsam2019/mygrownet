<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Logo from '../components/Logo.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-12 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-br from-blue-500/10 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-gradient-to-tr from-blue-600/10 to-transparent rounded-full blur-3xl"></div>

        <div class="relative z-10 max-w-md mx-auto px-4 sm:px-6">
            <div class="text-center mb-8">
                <Link :href="route('home')" class="inline-flex items-center mb-8 hover:opacity-90 transition-opacity">
                    <Logo size="xl" />
                </Link>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ title }}</h2>
                <p class="text-gray-700">{{ description }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-xl p-8 border border-gray-100">
                <slot />
            </div>
        </div>
    </div>
</template>
