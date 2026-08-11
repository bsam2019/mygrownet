import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/sw.js', 'StockFlow');

const stockFlowGlobs = {
    ...import.meta.glob<DefineComponent>('./pages/StockFlow/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/StockFlow/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Apps/**/*.vue'),
};

bootInertia('StockFlow', (name: string) => {
    return resolvePageComponent(
        `./Pages/${name}.vue`,
        stockFlowGlobs
    ).catch(() => resolvePageComponent(`./pages/${name}.vue`, stockFlowGlobs));
});
