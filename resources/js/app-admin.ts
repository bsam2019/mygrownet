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

    // All Module Domain Pages accessible to platform admins
    ...import.meta.glob<DefineComponent>('./pages/GrowNet/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/GrowNet/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/BMS/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/BMS/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/StockFlow/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/StockFlow/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/BizBoost/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/BizBoost/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/BizDocs/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/BizDocs/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/GrowBuilder/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/GrowBuilder/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/GrowFinance/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/GrowFinance/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/GrowMart/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/GrowMart/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/GrowStream/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/GrowStream/**/*.vue'),

    // Shared Workspaces, Apps catalog & Employee Portal
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Workspace/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Apps/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Employee/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./Pages/Employee/**/*.vue'),
};

bootInertia('Admin', (name: string) => {
    return resolvePageComponent(
        `./Pages/${name}.vue`,
        adminPageGlobs
    ).catch(() => resolvePageComponent(`./pages/${name}.vue`, adminPageGlobs));
});
