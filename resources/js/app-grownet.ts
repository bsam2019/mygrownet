import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/grownet-sw.js', 'GrowNet');

const growNetGlobs = {
    ...import.meta.glob<DefineComponent>('./pages/GrowNet/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/GrowNet/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Membership/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Membership/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Learning/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Learning/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Skills/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Skills/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Apps/**/*.vue'),
};

bootInertia('GrowNet', (name: string) => {
    return resolvePageComponent(
        `./Pages/${name}.vue`,
        growNetGlobs
    ).catch(() => resolvePageComponent(`./pages/${name}.vue`, growNetGlobs));
});
