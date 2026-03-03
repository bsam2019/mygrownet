import { ref, onMounted, onUnmounted } from 'vue';

const APP_VERSION_KEY = 'app_version';
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

            if (!storedVersion) {
                // First time - store current version
                localStorage.setItem(APP_VERSION_KEY, metaVersion);
                currentVersion.value = metaVersion;
                console.log(`[Version Check] Initial version stored: ${metaVersion}`);
                return;
            }

            currentVersion.value = storedVersion;

            // Check if version has changed
            if (storedVersion !== metaVersion) {
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

    const applyUpdate = () => {
        if (newVersion.value) {
            // Update localStorage first
            localStorage.setItem(APP_VERSION_KEY, newVersion.value);
            
            // Hide notification immediately
            hasUpdate.value = false;
            
            // Clear all caches
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => caches.delete(name));
                }).then(() => {
                    // Reload after caches are cleared
                    window.location.reload();
                });
            } else {
                // Reload immediately if no cache API
                window.location.reload();
            }
        }
    };

    const dismissUpdate = () => {
        // User dismissed - update localStorage to prevent showing again
        if (newVersion.value) {
            localStorage.setItem(APP_VERSION_KEY, newVersion.value);
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
