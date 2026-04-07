<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const showButton = ref(false);
const deferredPrompt = ref<any>(null);

onMounted(() => {
    // Check if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        return;
    }

    // Always show button for testing
    showButton.value = true;

    // Listen for the beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt.value = e;
        showButton.value = true;
    });
});

const installApp = async () => {
    if (!deferredPrompt.value) {
        // Fallback: Show instructions for manual installation
        alert('To install:\n\nChrome/Edge: Click the menu (⋮) → "Install app"\niOS Safari: Tap Share → "Add to Home Screen"');
        return;
    }

    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    
    if (outcome === 'accepted') {
        showButton.value = false;
    }
    
    deferredPrompt.value = null;
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
