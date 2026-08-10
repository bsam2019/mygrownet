import './bootstrap';
import '../css/app.css';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

registerModuleSW('/sw.js', 'MyGrowNet');

// Only include pages that are actually needed for the main app
// Module-specific pages are handled by their own entry points (app-*.ts files)
const pageGlobs: Record<string, () => Promise<DefineComponent>> = {
    ...import.meta.glob<DefineComponent>('./pages/*.vue'), // Root level pages (Welcome, About, Contact, etc.)
    ...import.meta.glob<DefineComponent>('./pages/Workspace/**/*.vue'), // Workspace/Platform Core
    ...import.meta.glob<DefineComponent>('./pages/Auth/**/*.vue'), // Authentication
    ...import.meta.glob<DefineComponent>('./pages/Apps/**/*.vue'), // App catalog
    
    // Core Platform pages (NOT modules with their own entry points)
    ...import.meta.glob<DefineComponent>('./pages/QuickInvoice/**/*.vue'), // Quick Invoice (no dedicated module)
    ...import.meta.glob<DefineComponent>('./pages/Department/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Investment/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Withdrawals/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Module/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Notification/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Wallet/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Transactions/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Reports/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Portal/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Settings/**/*.vue'),
    ...import.meta.glob<DefineComponent>('./pages/Payments/**/*.vue'), // Shared payment checkout
    
    // Note: The following modules have their own entry points and should NOT be here:
    // - BMS (app-bms.ts)
    // - GrowNet (app-grownet.ts)
    // - GrowBuilder (app-growbuilder.ts)
    // - StockFlow (app-stockflow.ts)
    // - BizDocs (app-bizdocs.ts)
    // - GrowFinance (app-growfinance.ts)
    // - GrowMart (app-growmart.ts)
    // - ZamStay (app-zamstay.ts)
    // - PrimeEdge (app-primeedge.ts)
    // - LifePlus (app-lifephus.ts)
    // - BizBoost (app-bizboost.ts)
    // - GrowStream (app-growstream.ts)
    // - Marketplace (app-marketplace.ts)
    // - Ventures (app-venture.ts)
    // - Admin (app-admin.ts)
    // - Employee (app-employee.ts)
};

bootInertia(appName, (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        pageGlobs
    );
});
