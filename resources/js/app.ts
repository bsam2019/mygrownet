import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

// Cache buster - Clear old caches on app load
(function() {
    if ('serviceWorker' in navigator) {
        // Unregister old service workers
        navigator.serviceWorker.getRegistrations().then(registrations => {
            registrations.forEach(registration => {
                registration.unregister();
            });
        });

        // Clear all caches
        if ('caches' in window) {
            caches.keys().then(cacheNames => {
                cacheNames.forEach(cacheName => {
                    caches.delete(cacheName);
                });
            });
        }

        // Clear localStorage
        localStorage.clear();
    }
})();

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize service worker registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js', { scope: '/' })
            .then((registration) => {
                console.log('[App] Service Worker registered:', registration);
                
                // Check for updates periodically
                setInterval(() => {
                    registration.update();
                }, 60000); // Every minute
                
                // Listen for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New service worker available
                                console.log('[App] New version available!');
                                // Auto-reload after 3 seconds
                                setTimeout(() => {
                                    console.log('[App] Reloading to get new version...');
                                    window.location.reload();
                                }, 3000);
                            }
                        });
                    }
                });
            })
            .catch((error) => {
                console.warn('[App] Service Worker registration failed:', error);
            });
    });
}

// Handle 419 (CSRF token expired) errors with better logic
let redirecting = false;

window.addEventListener('error', (event) => {
    if (event.message && event.message.includes('419')) {
        console.warn('[App] Session expired (419)');
        if (!redirecting) {
            redirecting = true;
            window.location.href = '/login';
        }
    }
});

// Intercept fetch errors for 419 responses
const originalFetch = window.fetch;
window.fetch = function(...args) {
    return originalFetch.apply(this, args).then((response) => {
        if (response.status === 419) {
            console.warn('[App] Session expired (419 from fetch)');
            if (!redirecting) {
                redirecting = true;
                // Redirect to login after a short delay
                setTimeout(() => {
                    window.location.href = '/login';
                }, 500);
            }
        }
        return response;
    }).catch((error) => {
        console.error('[App] Fetch error:', error);
        throw error;
    });
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#2563eb',
        showSpinner: true,
    },
});

// This will set light / dark mode on page load...
initializeTheme();
