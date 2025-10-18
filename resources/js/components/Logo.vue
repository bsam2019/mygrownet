<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl';
    showText?: boolean;
    variant?: 'default' | 'white' | 'dark';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    showText: false,
    variant: 'default'
});

const logoUrl = computed(() => '/logo.png');

const sizeClasses = {
    xs: 'h-6 w-auto',
    sm: 'h-8 w-auto',
    md: 'h-10 w-auto', 
    lg: 'h-12 w-auto',
    xl: 'h-16 w-auto',
    '2xl': 'h-20 w-auto',
    '3xl': 'h-24 w-auto'
};

const textSizeClasses = {
    xs: 'text-sm',
    sm: 'text-lg',
    md: 'text-xl',
    lg: 'text-2xl', 
    xl: 'text-3xl',
    '2xl': 'text-4xl',
    '3xl': 'text-5xl'
};

const textColorClasses = {
    default: 'bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent',
    white: 'text-white',
    dark: 'text-gray-900'
};

const taglineColorClasses = {
    default: 'text-gray-600',
    white: 'text-white/90',
    dark: 'text-gray-700'
};
</script>

<template>
    <div class="flex items-center">
        <img 
            :src="logoUrl" 
            alt="MyGrowNet Logo" 
            :class="sizeClasses[size]"
            class="object-contain"
        />
    </div>
</template>