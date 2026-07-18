import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/sw.js', 'Marketplace');

bootInertia('Marketplace', (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        import.meta.glob<DefineComponent>([
            './pages/Marketplace/**/*.vue',
            './pages/Admin/Marketplace/**/*.vue',
        ])
    );
});
