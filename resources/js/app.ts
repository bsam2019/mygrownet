import './bootstrap';
import '../css/app.css';
import 'aos/dist/aos.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { configureEcho } from '@laravel/echo-vue';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import AOS from 'aos';

// Make Pusher available globally for Echo
(window as any).Pusher = Pusher;

// Configure Echo with Reverb settings
const echoInstance = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    },
});

// Expose Echo globally for composables
(window as any).Echo = echoInstance;

// Configure @laravel/echo-vue to use the same instance
configureEcho(echoInstance);

// PWA Cache prefixes for each module - DO NOT clear these caches
const PWA_CACHE_PREFIXES = ['bizboost-', 'growbiz-', 'growfinance-', 'mygrownet-'];

// Check if current path is a PWA-enabled module
function isPWAModule(): string | null {
    const path = window.location.pathname;
    if (path.startsWith('/bizboost')) return 'bizboost';
    if (path.startsWith('/growbiz')) return 'growbiz';
    if (path.startsWith('/growfinance')) return 'growfinance';
    // Main MyGrowNet site (root paths)
    if (path === '/' || path.startsWith('/dashboard') || path.startsWith('/member')) return 'mygrownet';
    return null;
}

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

// Service worker registration for PWA functionality
// Register module-specific service workers for each PWA-enabled module
// DISABLED IN LOCAL DEVELOPMENT to prevent offline page issues
const isLocalDevelopment = window.location.hostname === 'localhost' || 
                          window.location.hostname === '127.0.0.1' ||
                          window.location.hostname.includes('.local');

if ('serviceWorker' in navigator && !isLocalDevelopment) {
    window.addEventListener('load', () => {
        const path = window.location.pathname;
        let swPath: string | null = null;
        let moduleName = '';
        
        // Determine which service worker to register based on current route
        if (path.startsWith('/bizboost')) {
            swPath = '/bizboost-sw.js';
            moduleName = 'BizBoost';
        } else if (path.startsWith('/growbiz')) {
            swPath = '/growbiz-sw.js';
            moduleName = 'GrowBiz';
        } else if (path.startsWith('/growfinance')) {
            swPath = '/growfinance-sw.js';
            moduleName = 'GrowFinance';
        } else if (path.startsWith('/marketplace')) {
            swPath = '/marketplace-sw.js';
            moduleName = 'Marketplace';
        } else if (path.startsWith('/cms')) {
            swPath = '/cms-sw.js';
            moduleName = 'CMS';
        } else {
            // Default: register main service worker for all other routes
            swPath = '/sw.js';
            moduleName = 'MyGrowNet';
        }
        
        if (swPath) {
            navigator.serviceWorker
                .register(swPath)
                .then((registration) => {
                    console.log(`[${moduleName} PWA] Service Worker registered:`, registration.scope);
                    
                    // Check for updates periodically
                    setInterval(() => {
                        registration.update();
                    }, 60 * 60 * 1000); // Check every hour
                    
                    // Listen for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log(`[${moduleName} PWA] New version available`);
                                }
                            });
                        }
                    });
                })
                .catch((error) => {
                    console.warn(`[${moduleName} PWA] Service Worker registration failed:`, error);
                });
        }
    });
} else if ('serviceWorker' in navigator && isLocalDevelopment) {
    // Unregister all service workers in local development
    navigator.serviceWorker.getRegistrations().then((registrations) => {
        registrations.forEach((registration) => {
            registration.unregister().then((success) => {
                if (success) {
                    console.log('[Dev] Service Worker unregistered:', registration.scope);
                }
            });
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
window.fetch = function (...args) {
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

// Initialize AOS (Animate On Scroll)
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    offset: 100,
});
