<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePWA } from '@/composables/usePWA';
import { XMarkIcon, DevicePhoneMobileIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

interface Props {
    module?: 'bizboost' | 'growbiz' | 'growfinance' | 'mygrownet';
    position?: 'top' | 'bottom';
}

const props = withDefaults(defineProps<Props>(), {
    module: 'mygrownet',
    position: 'bottom'
});

const {
    isInstallable,
    isInstalled,
    isStandalone,
    promptInstall,
    dismissInstallPrompt,
    shouldShowInstallPrompt,
    getIOSInstallInstructions
} = usePWA();

const showIOSInstructions = ref(false);
const iosInstructions = getIOSInstallInstructions();

const moduleConfig = computed(() => {
    const configs = {
        bizboost: {
            name: 'BizBoost',
            color: 'violet',
            bgClass: 'bg-violet-600',
            hoverClass: 'hover:bg-violet-700',
            textClass: 'text-violet-600',
            description: 'Install BizBoost for quick access to your marketing console'
        },
        growbiz: {
            name: 'GrowBiz',
            color: 'emerald',
            bgClass: 'bg-emerald-600',
            hoverClass: 'hover:bg-emerald-700',
            textClass: 'text-emerald-600',
            description: 'Install GrowBiz for quick access to your business management'
        },
        growfinance: {
            name: 'GrowFinance',
            color: 'emerald',
            bgClass: 'bg-emerald-500',
            hoverClass: 'hover:bg-emerald-600',
            textClass: 'text-emerald-500',
            description: 'Install GrowFinance for quick access to your accounting'
        },
        mygrownet: {
            name: 'MyGrowNet',
            color: 'blue',
            bgClass: 'bg-blue-600',
            hoverClass: 'hover:bg-blue-700',
            textClass: 'text-blue-600',
            description: 'Install MyGrowNet for quick access to your dashboard'
        }
    };
    return configs[props.module];
});

const showBanner = computed(() => {
    if (isInstalled.value || isStandalone.value) return false;
    return shouldShowInstallPrompt() || iosInstructions.show;
});

const handleInstall = async () => {
    if (iosInstructions.show) {
        showIOSInstructions.value = true;
    } else {
        await promptInstall();
    }
};

const handleDismiss = () => {
    dismissInstallPrompt();
    showIOSInstructions.value = false;
};
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="showBanner"
            :class="[
                'fixed left-0 right-0 z-50 mx-4 sm:mx-auto sm:max-w-md',
                position === 'top' ? 'top-4' : 'bottom-4'
            ]"
        >
            <!-- iOS Instructions Modal -->
            <div
                v-if="showIOSInstructions"
                class="bg-white rounded-2xl shadow-2xl p-6 border border-gray-100"
            >
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div :class="['p-2 rounded-xl', moduleConfig.bgClass]">
                            <DevicePhoneMobileIcon class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="font-semibold text-gray-900">Install {{ moduleConfig.name }}</h3>
                    </div>
                    <button
                        @click="handleDismiss"
                        class="p-1 rounded-lg hover:bg-gray-100 transition-colors"
                        aria-label="Close"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-400" />
                    </button>
                </div>
                
                <ol class="space-y-3 text-sm text-gray-600">
                    <li v-for="(step, index) in iosInstructions.steps" :key="index" class="flex gap-3">
                        <span :class="['flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-medium', moduleConfig.bgClass]">
                            {{ index + 1 }}
                        </span>
                        <span>{{ step }}</span>
                    </li>
                </ol>
            </div>

            <!-- Standard Install Banner -->
            <div
                v-else
                class="bg-white rounded-2xl shadow-2xl p-4 border border-gray-100"
            >
                <div class="flex items-center gap-4">
                    <div :class="['p-3 rounded-xl', moduleConfig.bgClass]">
                        <ArrowDownTrayIcon class="h-6 w-6 text-white" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 text-sm">Install {{ moduleConfig.name }}</h3>
                        <p class="text-xs text-gray-500 truncate">{{ moduleConfig.description }}</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button
                            @click="handleDismiss"
                            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            aria-label="Dismiss"
                        >
                            <XMarkIcon class="h-5 w-5 text-gray-400" />
                        </button>
                        <button
                            @click="handleInstall"
                            :class="['px-4 py-2 rounded-xl text-white text-sm font-medium transition-colors', moduleConfig.bgClass, moduleConfig.hoverClass]"
                        >
                            Install
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
