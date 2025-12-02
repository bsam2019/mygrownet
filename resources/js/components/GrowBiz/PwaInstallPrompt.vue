<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { XMarkIcon, DevicePhoneMobileIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const showPrompt = ref(false);
const deferredPrompt = ref<any>(null);
const isIos = ref(false);
const isStandalone = ref(false);

onMounted(() => {
    // Check if already installed
    isStandalone.value = window.matchMedia('(display-mode: standalone)').matches
        || (window.navigator as any).standalone === true;
    
    if (isStandalone.value) return;

    // Check if iOS
    isIos.value = /iPad|iPhone|iPod/.test(navigator.userAgent);

    // Listen for beforeinstallprompt (Chrome, Edge, etc.)
    window.addEventListener('beforeinstallprompt', handleBeforeInstall);

    // Show prompt after a delay if not dismissed before
    const dismissed = localStorage.getItem('growbiz-pwa-dismissed');
    if (!dismissed) {
        setTimeout(() => {
            if (!isStandalone.value) {
                showPrompt.value = true;
            }
        }, 3000);
    }
});

onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstall);
});

const handleBeforeInstall = (e: Event) => {
    e.preventDefault();
    deferredPrompt.value = e;
};

const installApp = async () => {
    if (deferredPrompt.value) {
        deferredPrompt.value.prompt();
        const { outcome } = await deferredPrompt.value.userChoice;
        if (outcome === 'accepted') {
            showPrompt.value = false;
        }
        deferredPrompt.value = null;
    }
};

const dismissPrompt = () => {
    showPrompt.value = false;
    localStorage.setItem('growbiz-pwa-dismissed', 'true');
};
</script>

<template>
    <!-- PWA Install Prompt Banner -->
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div 
            v-if="showPrompt && !isStandalone"
            class="fixed bottom-20 left-4 right-4 lg:bottom-4 lg:left-auto lg:right-4 lg:w-96 z-50"
        >
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <DevicePhoneMobileIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <span class="text-white font-semibold">Install GrowBiz</span>
                    </div>
                    <button 
                        @click="dismissPrompt"
                        class="p-1.5 hover:bg-white/20 rounded-lg transition-colors"
                        aria-label="Dismiss"
                    >
                        <XMarkIcon class="h-5 w-5 text-white" aria-hidden="true" />
                    </button>
                </div>

                <!-- Content -->
                <div class="p-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Install GrowBiz on your device for quick access and a better experience.
                    </p>

                    <!-- iOS Instructions -->
                    <div v-if="isIos" class="text-sm text-gray-500 space-y-2">
                        <p class="font-medium text-gray-700">To install:</p>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Tap the Share button <span class="inline-block w-4 h-4 align-middle">⬆️</span></li>
                            <li>Scroll down and tap "Add to Home Screen"</li>
                            <li>Tap "Add" to confirm</li>
                        </ol>
                    </div>

                    <!-- Android/Desktop Install Button -->
                    <template v-else>
                        <button 
                            v-if="deferredPrompt"
                            @click="installApp"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors"
                        >
                            <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                            Install App
                        </button>

                        <!-- Fallback with manual instructions -->
                        <div v-else class="space-y-3">
                            <p class="text-sm text-gray-500">
                                Add this page to your home screen for quick access.
                            </p>
                            <div class="text-xs text-gray-400 space-y-1">
                                <p class="font-medium text-gray-500">How to install:</p>
                                <p>• Chrome/Edge: Click the install icon in the address bar (⊕)</p>
                                <p>• Or use browser menu → "Install app" / "Add to Home screen"</p>
                            </div>
                            <button 
                                @click="dismissPrompt"
                                class="w-full px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                Got it
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </Transition>
</template>
