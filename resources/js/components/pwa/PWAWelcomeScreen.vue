<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { usePWA } from '@/composables/usePWA';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    /** Module name for branding */
    module?: 'mygrownet' | 'bizboost' | 'growbiz' | 'growfinance';
    /** Auto-dismiss delay in milliseconds */
    duration?: number;
    /** Session storage key to track if shown */
    storageKey?: string;
}

const props = withDefaults(defineProps<Props>(), {
    module: 'mygrownet',
    duration: 2500,
    storageKey: 'pwa-welcome-shown'
});

const emit = defineEmits<{
    (e: 'complete'): void;
}>();

const { isStandalone } = usePWA();

const isVisible = ref(false);
const isAnimating = ref(true);

// Module-specific branding
const moduleConfig = computed(() => {
    const configs = {
        mygrownet: {
            name: 'MyGrowNet',
            tagline: 'Grow Together, Succeed Together',
            gradient: 'from-blue-600 via-blue-500 to-indigo-600',
            accentColor: 'text-blue-200',
            logoColor: 'text-white'
        },
        bizboost: {
            name: 'BizBoost',
            tagline: 'Boost Your Business Growth',
            gradient: 'from-violet-600 via-purple-500 to-fuchsia-600',
            accentColor: 'text-violet-200',
            logoColor: 'text-white'
        },
        growbiz: {
            name: 'GrowBiz',
            tagline: 'Manage Your Team Efficiently',
            gradient: 'from-emerald-600 via-green-500 to-teal-600',
            accentColor: 'text-emerald-200',
            logoColor: 'text-white'
        },
        growfinance: {
            name: 'GrowFinance',
            tagline: 'Smart Financial Management',
            gradient: 'from-emerald-500 via-teal-500 to-cyan-600',
            accentColor: 'text-emerald-200',
            logoColor: 'text-white'
        }
    };
    return configs[props.module];
});

// Check if we should show the welcome screen
const shouldShow = (): boolean => {
    // Only show in standalone mode (installed PWA)
    if (!isStandalone.value) return false;
    
    // Check if already shown this session
    const shown = sessionStorage.getItem(props.storageKey);
    if (shown) return false;
    
    return true;
};

const dismissWelcome = () => {
    isAnimating.value = false;
    
    // Fade out animation
    setTimeout(() => {
        isVisible.value = false;
        sessionStorage.setItem(props.storageKey, 'true');
        emit('complete');
    }, 300);
};

onMounted(() => {
    if (shouldShow()) {
        isVisible.value = true;
        
        // Auto-dismiss after duration
        setTimeout(() => {
            dismissWelcome();
        }, props.duration);
    } else {
        emit('complete');
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isVisible"
                class="fixed inset-0 z-[9999] flex flex-col items-center justify-center"
                :class="[`bg-gradient-to-br ${moduleConfig.gradient}`]"
                @click="dismissWelcome"
            >
                <!-- Background pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');" />
                </div>

                <!-- Content -->
                <div 
                    class="relative flex flex-col items-center gap-6 px-8"
                    :class="{ 'animate-fade-in-up': isAnimating }"
                >
                    <!-- Logo with pulse animation -->
                    <div class="relative">
                        <div class="absolute inset-0 animate-ping-slow opacity-30">
                            <AppLogoIcon class="h-24 w-24 text-white" />
                        </div>
                        <AppLogoIcon 
                            class="h-24 w-24 relative z-10" 
                            :class="moduleConfig.logoColor"
                        />
                    </div>

                    <!-- App name -->
                    <h1 class="text-3xl font-bold text-white tracking-tight">
                        {{ moduleConfig.name }}
                    </h1>

                    <!-- Tagline -->
                    <p :class="['text-sm font-medium', moduleConfig.accentColor]">
                        {{ moduleConfig.tagline }}
                    </p>

                    <!-- Loading indicator -->
                    <div class="flex items-center gap-1.5 mt-4">
                        <span 
                            v-for="i in 3" 
                            :key="i"
                            class="w-2 h-2 rounded-full bg-white/60 animate-bounce"
                            :style="{ animationDelay: `${(i - 1) * 150}ms` }"
                        />
                    </div>
                </div>

                <!-- Skip hint -->
                <p class="absolute bottom-8 text-white/50 text-xs">
                    Tap anywhere to skip
                </p>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes ping-slow {
    0% {
        transform: scale(1);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.2);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 0.3;
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out;
}

.animate-ping-slow {
    animation: ping-slow 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
