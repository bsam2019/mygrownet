<script setup lang="ts">
import Logo from '@/components/Logo.vue';

interface Props {
    message?: string;
}

const props = withDefaults(defineProps<Props>(), {
    message: 'Loading...'
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4">
        <div class="text-center">
            <!-- Animated Logo -->
            <div class="mb-8 animate-pulse">
                <Logo size="xl" />
            </div>
            
            <!-- Loading Spinner -->
            <div class="mb-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            </div>
            
            <!-- Loading Message -->
            <p class="text-gray-600">{{ message }}</p>
        </div>
    </div>
</template>