import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/sw.js', 'Admin');

const adminPageGlobs = {
    ...import.meta.glob<DefineComponent>('./pages/Admin/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
};

bootInertia('Admin', (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        adminPageGlobs
    );
});
