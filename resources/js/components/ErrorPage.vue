<script setup lang="ts">
import Logo from '@/components/Logo.vue';
import { Link } from '@inertiajs/vue3';

interface Props {
    code?: string;
    title?: string;
    message?: string;
    showHomeLink?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    code: '404',
    title: 'Page Not Found',
    message: 'The page you are looking for could not be found.',
    showHomeLink: true
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4">
        <div class="text-center max-w-md mx-auto">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <Logo size="xl" />
            </div>
            
            <!-- Error Code -->
            <div class="mb-4">
                <span class="text-6xl font-bold text-blue-600">{{ code }}</span>
            </div>
            
            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ title }}</h1>
            
            <!-- Message -->
            <p class="text-gray-600 mb-8">{{ message }}</p>
            
            <!-- Home Link -->
            <Link 
                v-if="showHomeLink"
                :href="route('dashboard')" 
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                Return to Dashboard
            </Link>
        </div>
    </div>
</template>