<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { XMarkIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const showPrompt = ref(false);
const deferredPrompt = ref<any>(null);

onMounted(() => {
    // Check if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        return;
    }

    // Check if user dismissed before
    const dismissed = localStorage.getItem('pwa-install-dismissed');
    if (dismissed) {
        const dismissedDate = new Date(dismissed);
        const daysSinceDismissed = (Date.now() - dismissedDate.getTime()) / (1000 * 60 * 60 * 24);
        if (daysSinceDismissed < 7) {
            return; // Don't show for 7 days after dismissal
        }
    }

    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt.value = e;
        
        // Show prompt after 30 seconds
        setTimeout(() => {
            showPrompt.value = true;
        }, 30000);
    });

    // Register service worker with force update
    if ('serviceWorker' in navigator) {
        // First, unregister any old service workers to force fresh install
        navigator.serviceWorker.getRegistrations().then((registrations) => {
            registrations.forEach((registration) => {
                // Check if it's an old version by checking the script URL
                if (registration.active) {
                    registration.update(); // Force check for updates
                }
            });
        });

        navigator.serviceWorker.register('/marketplace-sw.js', {
            scope: '/marketplace',
            updateViaCache: 'none' // Always fetch fresh SW from network
        }).then((registration) => {
            console.log('Service Worker registered:', registration);
            
            // Force update check
            registration.update();
            
            // Listen for updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                if (newWorker) {
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New version available, skip waiting
                            newWorker.postMessage({ type: 'SKIP_WAITING' });
                        }
                    });
                }
            });
        }).catch((error) => {
            console.error('Service Worker registration failed:', error);
        });

        // Reload when new SW takes control
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (!refreshing) {
                refreshing = true;
                window.location.reload();
            }
        });
    }
});

const install = async () => {
    if (!deferredPrompt.value) {
        return;
    }

    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    
    console.log(`User response to install prompt: ${outcome}`);
    
    deferredPrompt.value = null;
    showPrompt.value = false;
};

const dismiss = () => {
    localStorage.setItem('pwa-install-dismissed', new Date().toISOString());
    showPrompt.value = false;
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div 
            v-if="showPrompt"
            class="fixed bottom-0 left-0 right-0 z-50 p-4 bg-white border-t-2 border-orange-500 shadow-2xl md:bottom-4 md:left-4 md:right-auto md:max-w-sm md:rounded-xl md:border-2"
        >
            <button
                @click="dismiss"
                class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 transition-colors"
                aria-label="Dismiss"
            >
                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>

            <div class="flex items-start gap-4">
                <div class="w-16 h-16 bg-orange-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                </div>

                <div class="flex-1 pt-1">
                    <h3 class="font-bold text-gray-900 mb-1">Install GrowNet Market</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Get the app for faster access, offline browsing, and instant notifications!
                    </p>

                    <div class="flex gap-2">
                        <button
                            @click="install"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium text-sm"
                        >
                            <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                            Install App
                        </button>
                        <button
                            @click="dismiss"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors font-medium text-sm"
                        >
                            Not Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
