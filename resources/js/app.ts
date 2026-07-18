import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

registerModuleSW('/sw.js', 'MyGrowNet');

// Exclude modules that have their own dedicated entry points
const pageGlobs: Record<string, () => Promise<DefineComponent>> = import.meta.glob<DefineComponent>([
    './pages/**/*.vue',
    '!./pages/StockFlow/**/*.vue',
    '!./pages/GrowNet/**/*.vue',
    '!./pages/Membership/**/*.vue',
    '!./pages/Matrix/**/*.vue',
    '!./pages/Performance/**/*.vue',
    '!./pages/Opportunities/**/*.vue',
    '!./pages/Tools/**/*.vue',
    '!./pages/ReferralProgram/**/*.vue',
    '!./pages/Referrals/**/*.vue',
    '!./pages/Reports/**/*.vue',
    '!./pages/Training/**/*.vue',
    '!./pages/Learning/**/*.vue',
    '!./pages/StarterKit/**/*.vue',
    '!./pages/StarterKits/**/*.vue',
    '!./pages/Commission/**/*.vue',
    '!./pages/CompensationPlan/**/*.vue',
    '!./pages/LoyaltyReward/**/*.vue',
    '!./pages/Rewards/**/*.vue',
    '!./pages/Points/**/*.vue',
    '!./pages/Wallet/**/*.vue',
    '!./pages/GrowBuilder/**/*.vue',
    '!./pages/BizDocs/**/*.vue',
    '!./pages/Ventures/**/*.vue',
    '!./pages/BizBoost/**/*.vue',
    '!./pages/CMS/**/*.vue',
    '!./pages/GrowMart/**/*.vue',
    '!./pages/ZamStay/**/*.vue',
    '!./pages/PrimeEdge/**/*.vue',
    '!./pages/Admin/**/*.vue',
    '!./pages/Employee/**/*.vue',
    '!./pages/GrowBiz/**/*.vue',
    '!./pages/LifePlus/**/*.vue',
    '!./pages/GrowFinance/**/*.vue',
    '!./pages/Marketplace/**/*.vue',
]);

bootInertia(appName, (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        pageGlobs
    );
});
