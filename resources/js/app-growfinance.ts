import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/sw.js', 'GrowFinance');

bootInertia('GrowFinance', (name: string) => {
    const pageGlobs: Record<string, () => Promise<DefineComponent>> = {
        ...import.meta.glob<DefineComponent>('./pages/GrowFinance/**/*.vue'),
        ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    };
    return resolvePageComponent(`./pages/${name}.vue`, pageGlobs);
});
