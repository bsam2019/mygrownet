import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

// Imported LAST so growstream's Tailwind token classes override the shared
// app.css utilities (which would otherwise resolve to the light --background
// hsl var). Source order decides the CSS cascade here.
import '../css/growstream.css';

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
