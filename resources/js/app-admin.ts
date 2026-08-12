import './bootstrap';
import '../css/app.css';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/sw.js', 'Admin');

// Scalable, domain-aware page globber for Platform Admin Entrypoint
const adminPageGlobs = {
    // Central Admin pages
    ...import.meta.glob<DefineComponent>('./pages/Admin/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Admin/**/*.vue'),

    // Domain-Scoped Admin Pages across ALL modules (e.g. GrowNet/Admin, StockFlow/Admin, BizBoost/Admin, etc.)
    ...import.meta.glob<DefineComponent>('./pages/**/Admin/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/**/Admin/**/*.vue'),

    // Shared Workspaces & Apps catalog
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Apps/**/*.vue'),
};

bootInertia('Admin', (name: string) => {
    return resolvePageComponent(
        `./Pages/${name}.vue`,
        adminPageGlobs
    ).catch(() => resolvePageComponent(`./pages/${name}.vue`, adminPageGlobs));
});
