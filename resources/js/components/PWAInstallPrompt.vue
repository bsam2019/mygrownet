<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { XMarkIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const showPrompt = ref(false);
const deferredPrompt = ref<any>(null);

onMounted(() => {
    console.log('PWAInstallPrompt mounted');
    
    // Check if already installed
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
    console.log('Is standalone:', isStandalone);
    
    if (isStandalone) {
        console.log('Already installed as PWA, not showing prompt');
        return;
    }

    // Check if user dismissed the prompt before
    const dismissed = localStorage.getItem('pwa-install-dismissed');
    if (dismissed) {
        const dismissedTime = parseInt(dismissed);
        const daysSinceDismissed = (Date.now() - dismissedTime) / (1000 * 60 * 60 * 24);
        
        console.log('Days since dismissed:', daysSinceDismissed);
        
        // Show again after 7 days
        if (daysSinceDismissed < 7) {
            console.log('PWA prompt dismissed recently, will show again in', Math.ceil(7 - daysSinceDismissed), 'days');
            return;
        } else {
            console.log('Dismissal expired, clearing localStorage');
            localStorage.removeItem('pwa-install-dismissed');
        }
    }

    // Listen for the beforeinstallprompt event
    const handleBeforeInstall = (e: Event) => {
        console.log('beforeinstallprompt event fired in PWAInstallPrompt');
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt.value = e;
        // Show our custom install prompt
        showPrompt.value = true;
        console.log('Showing custom install prompt');
    };

    window.addEventListener('beforeinstallprompt', handleBeforeInstall);

    // Listen for successful installation
    window.addEventListener('appinstalled', () => {
        console.log('PWA installed successfully');
        showPrompt.value = false;
        deferredPrompt.value = null;
        localStorage.removeItem('pwa-install-dismissed');
    });
    
    // Debug: Check if event has already fired
    setTimeout(() => {
        if (!deferredPrompt.value) {
            console.log('beforeinstallprompt has not fired yet. This is normal if:');
            console.log('1. PWA is already installed');
            console.log('2. Site is not served over HTTPS');
            console.log('3. Manifest or service worker has errors');
            console.log('4. Browser does not support PWA installation');
        }
    }, 2000);
});

const installApp = async () => {
    if (!deferredPrompt.value) {
        console.error('No deferred prompt available');
        return;
    }

    console.log('Triggering install prompt');
    
    // Show the install prompt
    deferredPrompt.value.prompt();

    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.value.userChoice;

    console.log('User choice:', outcome);

    if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
    } else {
        console.log('User dismissed the install prompt');
    }

    // Clear the deferredPrompt
    deferredPrompt.value = null;
    showPrompt.value = false;
};

const dismissPrompt = () => {
    console.log('User dismissed prompt manually');
    showPrompt.value = false;
    // Remember dismissal for 7 days
    localStorage.setItem('pwa-install-dismissed', Date.now().toString());
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="showPrompt"
            class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md z-50"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Header with close button -->
                <div class="flex items-start justify-between p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <img 
                                src="/images/icon-96x96.png" 
                                alt="MyGrowNet" 
                                class="w-10 h-10 rounded-lg"
                            >
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                Install MyGrowNet
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Quick access from your home screen
                            </p>
                        </div>
                    </div>
                    <button
                        @click="dismissPrompt"
                        aria-label="Dismiss install prompt"
                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <!-- Benefits -->
                <div class="px-4 pb-3">
                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Instant access from home screen
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Works offline
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Faster performance
                        </li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 p-4 pt-3 bg-gray-50 dark:bg-gray-900/50">
                    <button
                        @click="dismissPrompt"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                    >
                        Not now
                    </button>
                    <button
                        @click="installApp"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg transition-all shadow-sm hover:shadow flex items-center justify-center gap-2"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                        Install
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
