import { ref, onMounted } from 'vue';

export function usePWA() {
  const deferredPrompt = ref<any>(null);
  const showInstallPrompt = ref(false);
  const isInstalled = ref(false);
  const isStandalone = ref(false);

  onMounted(() => {
    // Check if already installed
    isStandalone.value = window.matchMedia('(display-mode: standalone)').matches ||
                         (window.navigator as any).standalone === true;
    
    isInstalled.value = isStandalone.value;

    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt.value = e;
      showInstallPrompt.value = !isInstalled.value;
    });

    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
      isInstalled.value = true;
      showInstallPrompt.value = false;
      deferredPrompt.value = null;
    });

    // Register service worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker
        .register('/sw.js')
        .then((registration) => {
          console.log('Service Worker registered:', registration);
        })
        .catch((error) => {
          console.log('Service Worker registration failed:', error);
        });
    }
  });

  const installApp = async () => {
    if (!deferredPrompt.value) {
      return;
    }

    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    
    if (outcome === 'accepted') {
      console.log('User accepted the install prompt');
    } else {
      console.log('User dismissed the install prompt');
    }
    
    deferredPrompt.value = null;
    showInstallPrompt.value = false;
  };

  const dismissInstallPrompt = () => {
    showInstallPrompt.value = false;
    // Store dismissal in localStorage
    localStorage.setItem('pwa-install-dismissed', Date.now().toString());
  };

  return {
    showInstallPrompt,
    isInstalled,
    isStandalone,
    installApp,
    dismissInstallPrompt,
  };
}
