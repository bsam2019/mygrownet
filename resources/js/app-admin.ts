import './bootstrap';
import '../css/app.css';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/sw.js', 'Admin');

const adminPageGlobs = {
    ...import.meta.glob<DefineComponent>('./pages/Admin/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Admin/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Employee/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Employee/**/*.vue'),
};

bootInertia('Admin', (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        adminPageGlobs
    );
});
