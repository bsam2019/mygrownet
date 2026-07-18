import './bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { bootInertia, registerModuleSW } from './modules/createApp';

registerModuleSW('/grownet-sw.js', 'GrowNet');

bootInertia('GrowNet', (name: string) => {
    return resolvePageComponent(
        `./pages/${name}.vue`,
        import.meta.glob<DefineComponent>([
            './pages/GrowNet/**/*.vue',
            './pages/Membership/**/*.vue',
            './pages/Matrix/**/*.vue',
            './pages/Performance/**/*.vue',
            './pages/Opportunities/**/*.vue',
            './pages/Tools/**/*.vue',
            './pages/ReferralProgram/**/*.vue',
            './pages/Referrals/**/*.vue',
            './pages/Reports/**/*.vue',
            './pages/Training/**/*.vue',
            './pages/Learning/**/*.vue',
            './pages/StarterKit/**/*.vue',
            './pages/StarterKits/**/*.vue',
            './pages/Commission/**/*.vue',
            './pages/CompensationPlan/**/*.vue',
            './pages/LoyaltyReward/**/*.vue',
            './pages/Rewards/**/*.vue',
            './pages/Points/**/*.vue',
            './pages/Wallet/**/*.vue',
        ])
    );
});
