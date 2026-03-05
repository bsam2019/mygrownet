<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Logo from '../components/Logo.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-8 sm:py-12 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-br from-blue-500/10 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-gradient-to-tr from-blue-600/10 to-transparent rounded-full blur-3xl"></div>

        <div class="relative z-10 max-w-md mx-auto px-4 sm:px-6">
            <!-- Sticky header on mobile -->
            <div class="sticky top-0 bg-gray-50/95 backdrop-blur-sm pb-4 sm:pb-6 mb-4 sm:mb-8 z-20">
                <div class="text-center pt-4 sm:pt-0">
                    <Link :href="route('home')" class="inline-flex items-center mb-4 sm:mb-6 hover:opacity-90 transition-opacity">
                        <Logo size="xl" />
                    </Link>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1 sm:mb-2">{{ title }}</h2>
                    <p class="text-sm sm:text-base text-gray-700">{{ description }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-xl p-6 sm:p-8 border border-gray-100 mb-8">
                <slot />
            </div>
        </div>
    </div>
</template>
