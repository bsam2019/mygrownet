<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    ClockIcon, 
    WrenchScrewdriverIcon, 
    ExclamationTriangleIcon,
    ArrowPathIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    siteName: string;
    status: 'draft' | 'maintenance' | 'suspended';
    message?: string;
    wasPublished?: boolean;
}>();

// Dynamic content based on status
const config = computed(() => {
    switch (props.status) {
        case 'maintenance':
            return {
                icon: WrenchScrewdriverIcon,
                title: 'Under Maintenance',
                badge: 'Be Right Back',
                badgeColor: 'bg-amber-500/20 border-amber-500/30 text-amber-300',
                iconBg: 'from-amber-500 to-orange-600',
                iconShadow: 'shadow-amber-500/30',
                dots: ['bg-amber-500', 'bg-orange-500', 'bg-yellow-500'],
                gradient: ['bg-amber-500/10', 'bg-orange-500/10'],
            };
        case 'suspended':
            return {
                icon: ExclamationTriangleIcon,
                title: 'Site Unavailable',
                badge: 'Temporarily Offline',
                badgeColor: 'bg-red-500/20 border-red-500/30 text-red-300',
                iconBg: 'from-red-500 to-rose-600',
                iconShadow: 'shadow-red-500/30',
                dots: ['bg-red-500', 'bg-rose-500', 'bg-pink-500'],
                gradient: ['bg-red-500/10', 'bg-rose-500/10'],
            };
        default: // draft
            return {
                icon: props.wasPublished ? ArrowPathIcon : ClockIcon,
                title: props.wasPublished ? 'Coming Back Soon' : 'Coming Soon',
                badge: props.wasPublished ? 'Updating' : 'Under Construction',
                badgeColor: 'bg-blue-500/20 border-blue-500/30 text-blue-300',
                iconBg: 'from-blue-500 to-indigo-600',
                iconShadow: 'shadow-blue-500/30',
                dots: ['bg-blue-500', 'bg-indigo-500', 'bg-purple-500'],
                gradient: ['bg-blue-500/10', 'bg-indigo-500/10'],
            };
    }
});

const pageTitle = computed(() => `${props.siteName} - ${config.value.title}`);
</script>

<template>
    <Head :title="pageTitle" />
    
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4">
        <div class="max-w-lg w-full text-center">
            <!-- Animated Icon -->
            <div class="mb-8">
                <div 
                    :class="[
                        'inline-flex items-center justify-center w-24 h-24 rounded-full shadow-lg',
                        `bg-gradient-to-br ${config.iconBg} ${config.iconShadow}`
                    ]"
                >
                    <component 
                        :is="config.icon" 
                        :class="[
                            'w-12 h-12 text-white',
                            status === 'maintenance' ? 'animate-spin-slow' : 'animate-pulse'
                        ]" 
                    />
                </div>
            </div>

            <!-- Site Name -->
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                {{ siteName }}
            </h1>

            <!-- Status Badge -->
            <div 
                :class="[
                    'inline-flex items-center gap-2 px-4 py-2 border rounded-full mb-6',
                    config.badgeColor
                ]"
            >
                <span class="w-2 h-2 rounded-full animate-pulse" :class="config.dots[0]"></span>
                <span class="text-sm font-medium">{{ config.badge }}</span>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-semibold text-white mb-4">
                {{ config.title }}
            </h2>

            <!-- Message -->
            <p class="text-gray-400 text-lg mb-8 leading-relaxed">
                {{ message }}
            </p>

            <!-- Decorative Elements -->
            <div class="flex justify-center gap-2 mb-8">
                <div 
                    v-for="(dot, i) in config.dots" 
                    :key="i"
                    :class="['w-2 h-2 rounded-full animate-bounce', dot]"
                    :style="{ animationDelay: `${i * 150}ms` }"
                ></div>
            </div>

            <!-- Footer -->
            <p class="text-gray-500 text-sm">
                Powered by <span class="text-blue-400 font-medium">MyGrowNet</span>
            </p>
        </div>

        <!-- Background Decoration -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div :class="['absolute top-1/4 left-1/4 w-96 h-96 rounded-full blur-3xl', config.gradient[0]]"></div>
            <div :class="['absolute bottom-1/4 right-1/4 w-96 h-96 rounded-full blur-3xl', config.gradient[1]]"></div>
        </div>
    </div>
</template>

<style scoped>
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}
</style>
