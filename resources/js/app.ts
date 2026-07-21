import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

registerModuleSW('/sw.js', 'MyGrowNet');

// Only include pages that are actually needed for the main app
// Module-specific pages are handled by their own entry points
const pageGlobs: Record<string, () => Promise<DefineComponent>> = {
    ...import.meta.glob<DefineComponent>('./pages/*.vue'), // Root level pages
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'), // Workspace pages
    ...import.meta.glob<DefineComponent>('./pages/Auth/**/*.vue'), // Auth pages
    ...import.meta.glob<DefineComponent>('./pages/BMS/**/*.vue'), // BMS pages
    ...import.meta.glob<DefineComponent>('./pages/Department/**/*.vue'), // Department pages
    ...import.meta.glob<DefineComponent>('./pages/Investment/**/*.vue'), // Investment pages
    ...import.meta.glob<DefineComponent>('./pages/Profile/**/*.vue'), // Profile pages
    ...import.meta.glob<DefineComponent>('./pages/Withdrawal/**/*.vue'), // Withdrawal pages
    ...import.meta.glob<DefineComponent>('./pages/Subscription/**/*.vue'), // Subscription pages
    ...import.meta.glob<DefineComponent>('./pages/Library/**/*.vue'), // Library pages
    ...import.meta.glob<DefineComponent>('./pages/Notification/**/*.vue'), // Notification pages
    ...import.meta.glob<DefineComponent>('./pages/Messaging/**/*.vue'), // Messaging pages
    ...import.meta.glob<DefineComponent>('./pages/Workshop/**/*.vue'), // Workshop pages
    ...import.meta.glob<DefineComponent>('./pages/Investor/**/*.vue'), // Investor pages
    ...import.meta.glob<DefineComponent>('./pages/ProfitSharing/**/*.vue'), // Profit sharing pages
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'), // App catalog pages
    ...import.meta.glob<DefineComponent>('./pages/GrowBuilder/**/*.vue'), // GrowBuilder pages
    ...import.meta.glob<DefineComponent>('./pages/StockFlow/**/*.vue'), // StockFlow pages
    ...import.meta.glob<DefineComponent>('./pages/BizDocs/**/*.vue'), // BizDocs pages
    ...import.meta.glob<DefineComponent>('./pages/GrowBiz/**/*.vue'), // GrowBiz pages
    ...import.meta.glob<DefineComponent>('./pages/QuickInvoice/**/*.vue'), // Quick Invoice pages
    ...import.meta.glob<DefineComponent>('./pages/GrowNet/**/*.vue'), // GrowNet pages
    ...import.meta.glob<DefineComponent>('./pages/GrowFinance/**/*.vue'), // GrowFinance pages
    ...import.meta.glob<DefineComponent>('./pages/GrowMart/**/*.vue'), // GrowMart pages
    ...import.meta.glob<DefineComponent>('./pages/ZamStay/**/*.vue'), // ZamStay pages
    ...import.meta.glob<DefineComponent>('./pages/PrimeEdge/**/*.vue'), // PrimeEdge pages
    ...import.meta.glob<DefineComponent>('./pages/LifePlus/**/*.vue'), // LifePlus pages
    ...import.meta.glob<DefineComponent>('./pages/GrowStorage/**/*.vue'), // GrowStorage pages
};

bootInertia(appName, (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        pageGlobs
    );
});
