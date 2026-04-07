<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const showButton = ref(false);
const deferredPrompt = ref<any>(null);
const isInstallable = ref(false);

onMounted(() => {
    console.log('PWAInstallButton mounted');
    
    // Check if already installed
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
    console.log('Is standalone (button):', isStandalone);
    
    if (isStandalone) {
        console.log('Already installed, hiding button');
        return;
    }

    // Listen for the beforeinstallprompt event
    const handleBeforeInstall = (e: Event) => {
        console.log('beforeinstallprompt event captured in PWAInstallButton');
        e.preventDefault();
        deferredPrompt.value = e;
        isInstallable.value = true;
        showButton.value = true;
    };
    
    window.addEventListener('beforeinstallprompt', handleBeforeInstall);

    // Show button after a short delay to allow event to fire
    setTimeout(() => {
        if (!isInstallable.value) {
            // If no install prompt available, still show button for manual instructions
            console.log('No install prompt available, showing button with fallback instructions');
            showButton.value = true;
        }
    }, 1000);
});

const installApp = async () => {
    console.log('Install button clicked');
    console.log('Has deferred prompt:', !!deferredPrompt.value);
    
    if (!deferredPrompt.value) {
        // Fallback: Show instructions for manual installation
        console.log('No deferred prompt, showing manual instructions');
        alert('To install:\n\nChrome/Edge: Click the menu (⋮) → "Install app"\niOS Safari: Tap Share → "Add to Home Screen"');
        return;
    }

    try {
        console.log('Triggering install prompt from button');
        deferredPrompt.value.prompt();
        const { outcome } = await deferredPrompt.value.userChoice;
        
        console.log('Install outcome:', outcome);
        
        if (outcome === 'accepted') {
            showButton.value = false;
        }
        
        deferredPrompt.value = null;
        isInstallable.value = false;
    } catch (error) {
        console.error('Install prompt error:', error);
    }
};
</script>

<template>
    <button
        v-if="showButton"
        @click="installApp"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
    >
        <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
        Install App
    </button>
</template>
