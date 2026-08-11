import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/growbuilder-sw.js', 'GrowBuilder');

const growBuilderGlobs = {
    ...import.meta.glob<DefineComponent>('./pages/GrowBuilder/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/GrowBuilder/**/*.vue'),
};

bootInertia('GrowBuilder', (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        growBuilderGlobs
    );
});
