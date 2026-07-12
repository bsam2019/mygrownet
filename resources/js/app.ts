import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

registerModuleSW('/sw.js', 'MyGrowNet');

bootInertia(appName, (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        import.meta.glob<DefineComponent>('./pages/**/*.vue')
    );
});
