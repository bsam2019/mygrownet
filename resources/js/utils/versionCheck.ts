/**
 * Version Check Utility
 * Automatically detects when a new version is deployed and prompts user to reload
 */

const APP_VERSION_KEY = 'mygrownet_app_version';
const CHECK_INTERVAL = 5 * 60 * 1000; // Check every 5 minutes

export function getCurrentVersion(): string {
    // Get version from meta tag (set by Laravel)
    const metaTag = document.querySelector('meta[name="app-version"]');
    return metaTag?.getAttribute('content') || 'unknown';
}

export function getStoredVersion(): string | null {
    return localStorage.getItem(APP_VERSION_KEY);
}

export function storeVersion(version: string): void {
    localStorage.setItem(APP_VERSION_KEY, version);
}

export function hasVersionChanged(): boolean {
    const currentVersion = getCurrentVersion();
    const storedVersion = getStoredVersion();
    
    if (!storedVersion) {
        storeVersion(currentVersion);
        return false;
    }
    
    return currentVersion !== storedVersion && currentVersion !== 'unknown';
}

export async function clearAllCaches(): Promise<void> {
    // Clear service worker caches
    if ('caches' in window) {
        const cacheNames = await caches.keys();
        await Promise.all(cacheNames.map(name => caches.delete(name)));
    }
    
    // Send message to service worker to skip waiting
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
    }
}

export function setupVersionCheck(onNewVersion: () => void): void {
    // Check on page load
    if (hasVersionChanged()) {
        onNewVersion();
    }
    
    // Check periodically
    setInterval(() => {
        if (hasVersionChanged()) {
            onNewVersion();
        }
    }, CHECK_INTERVAL);
    
    // Listen for service worker updates
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('[Version Check] Service worker updated');
            onNewVersion();
        });
    }
}

export async function reloadWithCacheClear(): Promise<void> {
    await clearAllCaches();
    storeVersion(getCurrentVersion());
    window.location.reload();
}
