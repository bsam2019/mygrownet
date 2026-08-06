import './bootstrap';
import '../css/growstream.css';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

// Unregister existing service workers that might be causing issues
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then((registrations) => {
        registrations.forEach((registration) => {
            console.log('[GrowStream] Unregistering service worker:', registration.scope);
            registration.unregister();
        });
    });
}

// Temporarily disabled - service worker causing video playback issues
// registerModuleSW('/sw.js', 'GrowStream');

bootInertia('GrowStream', (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        {
            ...import.meta.glob<DefineComponent>('./pages/GrowStream/**/*.vue'),
            ...import.meta.glob<DefineComponent>('./pages/Payments/**/*.vue'),
        }
    );
});
