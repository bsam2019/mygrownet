import { ref, onMounted } from 'vue';

const INSTALL_PROMPT_COOLDOWN = 7 * 24 * 60 * 60 * 1000; // 7 days
const INSTALL_PROMPT_STORAGE_KEY = 'pwa-install-dismissed-at';
const INSTALL_PROMPT_SHOW_DELAY = 1000; // 1 second after page load
const INSTALL_STATE_KEY = 'pwa-install-state';

export function usePWA() {
  const deferredPrompt = ref<any>(null);
  const showInstallPrompt = ref(false);
  const isInstalled = ref(false);
  const isStandalone = ref(false);
  const updateAvailable = ref(false);
  const swRegistration = ref<ServiceWorkerRegistration | null>(null);

  onMounted(() => {
    // Check if already installed using multiple methods
    const isDisplayModeStandalone = window.matchMedia('(display-mode: standalone)').matches;
    const isIOSStandalone = (window.navigator as any).standalone === true;
    const isInStandaloneMode = isDisplayModeStandalone || isIOSStandalone;
    
    // Additional check: if URL has ?source=pwa or running in app window
    const urlParams = new URLSearchParams(window.location.search);
    const isPWASource = urlParams.get('source') === 'pwa';
    
    // Check if running in a PWA window (desktop Chrome/Edge)
    const isDesktopPWA = window.matchMedia('(display-mode: window-controls-overlay)').matches;
    
    isStandalone.value = isInStandaloneMode || isDesktopPWA || isPWASource;
    
    // Check localStorage for install state
    const savedInstallState = localStorage.getItem(INSTALL_STATE_KEY);
    isInstalled.value = isStandalone.value || savedInstallState === 'true';
    
    // Log detection results for debugging
    console.log('[PWA] Detection:', {
      isDisplayModeStandalone,
      isIOSStandalone,
      isDesktopPWA,
      isPWASource,
      savedInstallState,
      finalIsInstalled: isInstalled.value
    });

    // Additional check using getInstalledRelatedApps API (Chrome/Edge)
    if ('getInstalledRelatedApps' in navigator) {
      (navigator as any).getInstalledRelatedApps()
        .then((relatedApps: any[]) => {
          if (relatedApps.length > 0) {
            console.log('[PWA] Detected installed via getInstalledRelatedApps');
            isInstalled.value = true;
            localStorage.setItem(INSTALL_STATE_KEY, 'true');
          }
        })
        .catch((error: any) => {
          console.warn('[PWA] getInstalledRelatedApps failed:', error);
        });
    }

    // Register service worker with update checking
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker
        .register('/sw.js', { scope: '/' })
        .then((registration) => {
          console.log('[PWA] Service Worker registered:', registration);
          swRegistration.value = registration;

          // Check for updates immediately
          registration.update().catch((error) => {
            console.warn('[PWA] Initial update check failed:', error);
          });

          // Check for updates every 30 seconds (more frequent)
          setInterval(() => {
            registration.update().catch((error) => {
              console.warn('[PWA] Update check failed:', error);
            });
          }, 30000);

          // Listen for updates
          registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;
            if (!newWorker) return;

            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // New service worker is ready
                console.log('[PWA] Update available');
                updateAvailable.value = true;
                
                // Notify user about update
                notifyUpdateAvailable();
              }
            });
          });
        })
        .catch((error) => {
          console.warn('[PWA] Service Worker registration failed:', error);
        });

      // Listen for messages from service worker
      navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data && event.data.type === 'SW_ACTIVATED') {
          console.log('[PWA] Service worker activated with version:', event.data.version);
          // Could trigger a refresh notification here
        }
      });
    }

    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt.value = e;
      
      console.log('[PWA] Install prompt available');
      
      // Check if we should show the prompt
      if (shouldShowInstallPrompt()) {
        // Show the prompt after a short delay
        setTimeout(() => {
          if (!isInstalled.value && deferredPrompt.value) {
            console.log('[PWA] Showing install prompt');
            showInstallPrompt.value = true;
          }
        }, INSTALL_PROMPT_SHOW_DELAY);
      }
    });

    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
      console.log('[PWA] App installed successfully');
      isInstalled.value = true;
      showInstallPrompt.value = false;
      deferredPrompt.value = null;
      localStorage.setItem(INSTALL_STATE_KEY, 'true');
      localStorage.removeItem(INSTALL_PROMPT_STORAGE_KEY);
      
      // Show success notification only after actual installation
      showInstallationSuccess();
    });

    // Handle visibility changes to check for updates
    document.addEventListener('visibilitychange', () => {
      if (document.hidden === false && swRegistration.value) {
        // Page became visible, check for updates
        console.log('[PWA] Page visible, checking for updates');
        swRegistration.value.update().catch((error) => {
          console.warn('[PWA] Update check failed:', error);
        });
      }
    });

    // Listen for online/offline events
    window.addEventListener('online', () => {
      console.log('[PWA] Back online, checking for updates');
      if (swRegistration.value) {
        swRegistration.value.update();
      }
    });

    // Check for updates when page regains focus
    window.addEventListener('focus', () => {
      if (swRegistration.value) {
        swRegistration.value.update();
      }
    });
  });

  const shouldShowInstallPrompt = (): boolean => {
    // Don't show if already installed
    if (isInstalled.value) return false;

    // Check if user dismissed it recently
    const dismissedAt = localStorage.getItem(INSTALL_PROMPT_STORAGE_KEY);
    if (dismissedAt) {
      const timeSinceDismissal = Date.now() - parseInt(dismissedAt);
      if (timeSinceDismissal < INSTALL_PROMPT_COOLDOWN) {
        return false;
      }
    }

    return true;
  };

  const installApp = async () => {
    if (!deferredPrompt.value) {
      console.warn('[PWA] No install prompt available');
      return;
    }

    try {
      // Show the native install prompt
      deferredPrompt.value.prompt();
      
      // Wait for user choice
      const { outcome } = await deferredPrompt.value.userChoice;
      
      if (outcome === 'accepted') {
        console.log('[PWA] User accepted the install prompt');
        // Don't set isInstalled here - wait for appinstalled event
        // The appinstalled event will fire when installation is actually complete
      } else {
        console.log('[PWA] User dismissed the install prompt');
        dismissInstallPrompt();
      }
    } catch (error) {
      console.error('[PWA] Install error:', error);
    } finally {
      deferredPrompt.value = null;
      showInstallPrompt.value = false;
    }
  };

  const dismissInstallPrompt = () => {
    showInstallPrompt.value = false;
    // Store dismissal timestamp to implement cooldown
    localStorage.setItem(INSTALL_PROMPT_STORAGE_KEY, Date.now().toString());
  };

  const notifyUpdateAvailable = () => {
    // Show a notification or banner that an update is available
    console.log('[PWA] Update available - user should refresh');
    
    // Optional: Show a toast/notification to user
    if ('Notification' in window && Notification.permission === 'granted') {
      new Notification('MyGrowNet Update Available', {
        body: 'A new version is available. Refresh to update.',
        icon: '/images/icon-192x192.png',
        badge: '/images/icon-192x192.png',
      });
    }
  };

  const applyUpdate = () => {
    if (!swRegistration.value?.waiting) {
      console.warn('[PWA] No waiting service worker');
      return;
    }

    // Tell the waiting service worker to skip waiting
    swRegistration.value.waiting.postMessage({ type: 'SKIP_WAITING' });

    // Reload the page once the new service worker is active
    let refreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
      if (!refreshing) {
        refreshing = true;
        window.location.reload();
      }
    });
  };

  const clearCache = async () => {
    if (swRegistration.value?.active) {
      swRegistration.value.active.postMessage({ type: 'CLEAR_CACHE' });
    }
    
    // Also clear caches directly
    const cacheNames = await caches.keys();
    await Promise.all(
      cacheNames.map((cacheName) => caches.delete(cacheName))
    );
    
    console.log('[PWA] Cache cleared');
  };

  const showInstallationSuccess = () => {
    // Show a success message to the user
    console.log('[PWA] Installation successful!');
    
    // You can trigger a toast notification here
    // For now, just log it
    if (typeof window !== 'undefined' && (window as any).showToast) {
      (window as any).showToast('App installed successfully!', 'success');
    }
  };

  return {
    showInstallPrompt,
    isInstalled,
    isStandalone,
    updateAvailable,
    installApp,
    dismissInstallPrompt,
    applyUpdate,
    clearCache,
  };
}
