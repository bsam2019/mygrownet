import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/service-worker.js', 'BMS');

const bmsGlobs = {
    ...import.meta.glob<DefineComponent>('./pages/BMS/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/BMS/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Apps/**/*.vue'),
};

bootInertia('BMS', (name: string) => {
    return resolvePageComponent(
        `./Pages/${name}.vue`,
        bmsGlobs
    ).catch(() => resolvePageComponent(`./pages/${name}.vue`, bmsGlobs));
});
