import { ref, onMounted, onUnmounted } from 'vue';

const APP_VERSION_KEY = 'app_version';
const UPDATE_DISMISSED_KEY = 'update_dismissed_version';
const CHECK_INTERVAL = 5 * 60 * 1000; // Check every 5 minutes

export function useVersionCheck() {
    const hasUpdate = ref(false);
    const currentVersion = ref<string | null>(null);
    const newVersion = ref<string | null>(null);
    let intervalId: ReturnType<typeof setInterval> | null = null;

    const checkVersion = async () => {
        try {
            // Get current version from meta tag
            const metaVersion = document.querySelector('meta[name="app-version"]')?.getAttribute('content');
            
            if (!metaVersion) {
                console.warn('[Version Check] No app-version meta tag found');
                return;
            }

            // Get stored version
            const storedVersion = localStorage.getItem(APP_VERSION_KEY);
            const dismissedVersion = localStorage.getItem(UPDATE_DISMISSED_KEY);

            if (!storedVersion) {
                // First time - store current version and don't show notification
                localStorage.setItem(APP_VERSION_KEY, metaVersion);
                currentVersion.value = metaVersion;
                console.log(`[Version Check] Initial version stored: ${metaVersion}`);
                return;
            }

            currentVersion.value = storedVersion;

            // Check if version has changed
            if (storedVersion !== metaVersion) {
                // Don't show if user already dismissed this version
                if (dismissedVersion === metaVersion) {
                    console.log(`[Version Check] Update dismissed by user: ${metaVersion}`);
                    // Update stored version silently
                    localStorage.setItem(APP_VERSION_KEY, metaVersion);
                    hasUpdate.value = false;
                    return;
                }
                
                // Only show notification if not already showing
                if (!hasUpdate.value) {
                    hasUpdate.value = true;
                    newVersion.value = metaVersion;
                    console.log(`[Version Check] Update available: ${storedVersion} → ${metaVersion}`);
                }
            } else {
                // Versions match - ensure notification is hidden
                hasUpdate.value = false;
            }
        } catch (error) {
            console.error('[Version Check] Error:', error);
        }
    };

    const applyUpdate = async () => {
        if (newVersion.value) {
            // Update localStorage first
            localStorage.setItem(APP_VERSION_KEY, newVersion.value);
            // Clear dismissed flag since user is updating
            localStorage.removeItem(UPDATE_DISMISSED_KEY);
            
            // Hide notification immediately
            hasUpdate.value = false;
            
            console.log(`[Version Check] Applying update to: ${newVersion.value}`);
            
            try {
                // Clear all caches
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    await Promise.all(cacheNames.map(name => caches.delete(name)));
                    console.log('[Version Check] All caches cleared');
                }
                
                // Unregister all service workers
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    await Promise.all(registrations.map(reg => reg.unregister()));
                    console.log('[Version Check] All service workers unregistered');
                }
            } catch (error) {
                console.error('[Version Check] Error clearing caches:', error);
            }
            
            // Force hard reload (bypass cache)
            window.location.reload();
        }
    };

    const dismissUpdate = () => {
        // User dismissed - store this version as dismissed
        if (newVersion.value) {
            localStorage.setItem(UPDATE_DISMISSED_KEY, newVersion.value);
            localStorage.setItem(APP_VERSION_KEY, newVersion.value);
            console.log(`[Version Check] Update dismissed: ${newVersion.value}`);
        }
        hasUpdate.value = false;
    };

    onMounted(() => {
        checkVersion();
        
        // Check periodically
        intervalId = setInterval(checkVersion, CHECK_INTERVAL);
    });

    onUnmounted(() => {
        if (intervalId) {
            clearInterval(intervalId);
        }
    });

    return {
        hasUpdate,
        currentVersion,
        newVersion,
        applyUpdate,
        dismissUpdate,
    };
}
