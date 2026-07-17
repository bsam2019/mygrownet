import '../../css/app.css';
import 'aos/dist/aos.css';

import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { createPinia } from 'pinia';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { configureEcho } from '@laravel/echo-vue';
import { initializeTheme } from '../composables/useAppearance';
import AOS from 'aos';

(window as any).Pusher = Pusher;

const echoInstance = (window as any).__sfSubdomain ? null : new Echo({
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
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
    },
});

(window as any).Echo = echoInstance;
if (echoInstance) {
    configureEcho(echoInstance);
}

const PWA_CACHE_PREFIXES = ['bizboost-', 'growbiz-', 'growfinance-', 'mygrownet-'];

function isPWAModule(): string | null {
    const path = window.location.pathname;
    if (path.startsWith('/bizboost')) return 'bizboost';
    if (path.startsWith('/growbiz')) return 'growbiz';
    if (path.startsWith('/growfinance')) return 'growfinance';
    if (path === '/' || path.startsWith('/dashboard') || path.startsWith('/member')) return 'mygrownet';
    return null;
}

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

let redirecting = false;

function getLoginUrl(): string {
    if ((window as any).__sfSubdomain) {
        const hostParts = window.location.hostname.split('.');
        if (hostParts.length > 2) {
            const account = hostParts[0];
            return `https://${account}.${hostParts.slice(1).join('.')}/login`;
        }
    }
    return '/login';
}

window.addEventListener('error', (event) => {
    if (event.message?.includes('419')) {
        if (!redirecting) {
            redirecting = true;
            window.location.href = getLoginUrl();
        }
    }
});

const originalFetch = window.fetch;
window.fetch = function (...args) {
    return originalFetch.apply(this, args).then((response) => {
        if (response.status === 419 && !redirecting) {
            redirecting = true;
            setTimeout(() => { window.location.href = getLoginUrl(); }, 500);
        }
        return response;
    }).catch((error) => {
        console.error('[App] Fetch error:', error);
        throw error;
    });
};

const isLocalDevelopment = window.location.hostname === 'localhost' ||
    window.location.hostname === '127.0.0.1' ||
    window.location.hostname.includes('.local');

export function registerModuleSW(moduleSwPath: string, moduleName: string) {
    if ('serviceWorker' in navigator && !isLocalDevelopment) {
        window.addEventListener('load', () => {
            const path = window.location.pathname;
            if (path.startsWith('/admin')) {
                navigator.serviceWorker.getRegistrations().then((registrations) => {
                    registrations.forEach((r) => r.unregister());
                });
                return;
            }
            if ((window as any).__sfSubdomain) {
                navigator.serviceWorker.getRegistrations().then((registrations) => {
                    registrations.forEach((r) => r.unregister());
                });
                return;
            }
            navigator.serviceWorker
                .register(moduleSwPath, { updateViaCache: 'none' })
                .then((registration) => {
                    console.log(`[${moduleName} PWA] Service Worker registered:`, registration.scope);
                    registration.addEventListener('updatefound', () => {
                        console.log(`[${moduleName} PWA] New version available`);
                    });
                })
                .catch((error) => {
                    console.warn(`[${moduleName} PWA] Service Worker registration failed:`, error);
                });
        });
    } else if ('serviceWorker' in navigator && isLocalDevelopment) {
        navigator.serviceWorker.getRegistrations().then((registrations) => {
            registrations.forEach((r) => r.unregister());
        });
    }
}

export function bootInertia(
    appName: string,
    pageResolver: (name: string) => Promise<DefineComponent>,
    progressConfig: any = { delay: 80, color: '#059669', includeCSS: true, showSpinner: false },
) {
    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => {
            const isGrowBuilderSite = name === 'GrowBuilder/Preview/Site';
            if (isGrowBuilderSite) {
                (window as any).__isGrowBuilderSite = true;
            }
            return pageResolver(name);
        },
        setup({ el, App, props, plugin }) {
            const app = createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .use(createPinia());

            app.mount(el);
        },
        progress: (window as any).__isGrowBuilderSite ? false : progressConfig,
    });

    initializeTheme();
    AOS.init({ duration: 800, easing: 'ease-in-out', once: true, offset: 100 });
}
