import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/bizdocs-sw.js', 'BizDocs');

bootInertia('BizDocs', (name: string) => {
    const pageGlobs: Record<string, () => Promise<DefineComponent>> = {
        ...import.meta.glob<DefineComponent>('./pages/BizDocs/**/*.vue'),
        ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'), // App catalog
    };
    return resolvePageComponent(`./pages/${name}.vue`, pageGlobs);
});
