<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { usePWA } from '@/composables/usePWA';
import { useHaptics } from '@/composables/useHaptics';
import BottomSheet from './BottomSheet.vue';
import {
    DevicePhoneMobileIcon,
    XMarkIcon,
    ArrowDownTrayIcon,
    ShareIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';
import { SparklesIcon } from '@heroicons/vue/24/solid';

interface Props {
    /** Show as banner instead of modal */
    variant?: 'banner' | 'modal';
    /** Delay before showing (ms) */
    showDelay?: number;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'banner',
    showDelay: 3000,
});

const {
    isInstallable,
    isInstalled,
    shouldShowInstallPrompt,
    promptInstall,
    dismissInstallPrompt,
    getIOSInstallInstructions,
} = usePWA();

const { light, success } = useHaptics();

// State
const isVisible = ref(false);
const showIOSSheet = ref(false);
const isInstalling = ref(false);

// iOS instructions
const iosInstructions = computed(() => getIOSInstallInstructions());

// Show prompt after delay
onMounted(() => {
    setTimeout(() => {
        if (shouldShowInstallPrompt()) {
            isVisible.value = true;
        }
    }, props.showDelay);
});

const handleInstall = async () => {
    light();
    
    // Check if iOS
    if (iosInstructions.value.show) {
        showIOSSheet.value = true;
        return;
    }

    isInstalling.value = true;
    const installed = await promptInstall();
    isInstalling.value = false;

    if (installed) {
        success();
        isVisible.value = false;
    }
};

const handleDismiss = () => {
    light();
    dismissInstallPrompt();
    isVisible.value = false;
};

const handleIOSSheetClose = () => {
    showIOSSheet.value = false;
};
</script>

<template>
    <!-- Banner variant -->
    <Transition name="slide-up">
        <div
            v-if="isVisible && variant === 'banner' && !isInstalled"
            class="fixed bottom-20 left-4 right-4 z-40 lg:hidden"
        >
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-4">
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <SparklesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-slate-900 dark:text-white">
                            Install BizBoost
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Add to your home screen for quick access
                        </p>
                    </div>

                    <!-- Close button -->
                    <button
                        @click="handleDismiss"
                        class="flex-shrink-0 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                        aria-label="Dismiss install prompt"
                    >
                        <XMarkIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 mt-3">
                    <button
                        @click="handleDismiss"
                        class="flex-1 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        Not now
                    </button>
                    <button
                        @click="handleInstall"
                        :disabled="isInstalling"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <ArrowDownTrayIcon v-if="!isInstalling" class="h-4 w-4" aria-hidden="true" />
                        <span v-if="isInstalling">Installing...</span>
                        <span v-else>Install</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- iOS Instructions Sheet -->
    <BottomSheet v-model="showIOSSheet" title="Install BizBoost" @close="handleIOSSheetClose">
        <div class="p-4 pb-8">
            <p class="text-slate-600 dark:text-slate-400 mb-6">
                To install BizBoost on your iPhone or iPad, follow these steps:
            </p>

            <ol class="space-y-4">
                <li class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 font-semibold text-sm">
                        1
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="text-slate-900 dark:text-white font-medium">
                            Tap the Share button
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                            <ShareIcon class="h-4 w-4" aria-hidden="true" />
                            at the bottom of Safari
                        </p>
                    </div>
                </li>

                <li class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 font-semibold text-sm">
                        2
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="text-slate-900 dark:text-white font-medium">
                            Scroll down and tap "Add to Home Screen"
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            in the share menu
                        </p>
                    </div>
                </li>

                <li class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 font-semibold text-sm">
                        3
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="text-slate-900 dark:text-white font-medium">
                            Tap "Add" to confirm
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            BizBoost will appear on your home screen
                        </p>
                    </div>
                </li>
            </ol>

            <button
                @click="handleIOSSheetClose"
                class="w-full mt-6 px-4 py-3 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-xl transition-colors"
            >
                Got it
            </button>
        </div>
    </BottomSheet>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>
