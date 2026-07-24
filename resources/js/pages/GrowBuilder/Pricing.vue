<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Tier {
    key: string;
    name: string;
    description: string;
    price_monthly: number;
    price_annual: number;
    popular?: boolean;
    sort_order?: number;
    features: string[];
    labeled_features?: { key: string; label: string }[];
    limits?: Record<string, number>;
    labeled_limits?: Record<string, { key: string; label: string; value: number }>;
}

const props = defineProps<{
    tiers?: Record<string, Tier>;
}>();

const isSubdomain = computed(() => window.location.hostname === 'growbuilder.mygrownet.com');
const billing = ref<'monthly' | 'annual'>('monthly');
const annualNotice = ref(true);

const sortedTiers = computed(() => {
    if (!props.tiers) return [];
    return Object.values(props.tiers).sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
});

const formatPrice = (price: number) => price === 0 ? 'Free' : `K${price.toLocaleString()}`;

const featuresList = [
    { key: 'sites', label: 'Number of Sites' },
    { key: 'storage_mb', label: 'Storage' },
    { key: 'products', label: 'Products' },
    { key: 'ai_prompts', label: 'AI Prompts / month' },
    { key: 'subdomain', label: 'Free Subdomain' },
    { key: 'custom_domain', label: 'Custom Domain' },
    { key: 'ecommerce', label: 'E-Commerce' },
    { key: 'payment_integration', label: 'Payment Gateway' },
    { key: 'unlimited_products', label: 'Unlimited Products' },
    { key: 'remove_branding', label: 'Remove Branding' },
    { key: 'ai_section_generator', label: 'AI Section Generator' },
    { key: 'ai_seo', label: 'AI SEO Assistant' },
    { key: 'marketing_tools', label: 'Marketing Tools' },
    { key: 'own_smtp', label: 'Custom SMTP' },
    { key: 'white_label', label: 'White Label' },
    { key: 'multi_site', label: 'Multi-Site Management' },
    { key: 'priority_support', label: 'Priority Support' },
    { key: 'free_domain_after_3mo', label: 'Free Domain' },
    { key: 'analytics', label: 'Advanced Analytics' },
];

function tierHasFeature(tier: Tier, featureKey: string): boolean {
    return tier.features?.includes(featureKey) ?? false;
}

function tierLimit(tier: Tier, limitKey: string): string {
    const val = tier.limits?.[limitKey];
    if (val === undefined || val === null) return '—';
    if (val === -1) return 'Unlimited';
    if (limitKey === 'storage_mb') {
        return val >= 1024 ? `${(val / 1024).toFixed(0)} GB` : `${val} MB`;
    }
    return val.toString();
}

function annualPrice(tier: Tier): number {
    return tier.price_annual > 0 ? tier.price_annual : tier.price_monthly * 12;
}

function annualSavings(tier: Tier): number {
    if (tier.price_monthly === 0) return 0;
    const annualIfMonthly = tier.price_monthly * 12;
    return annualIfMonthly - annualPrice(tier);
}
</script>

<template>
    <Head>
        <title>Pricing — GrowBuilder | MyGrowNet</title>
        <meta name="description" content="Simple, transparent pricing for GrowBuilder. Start free, upgrade as your business grows." />
    </Head>

    <div class="min-h-screen bg-white">
        <!-- Nav -->
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="isSubdomain ? '/' : '/growbuilder'" class="flex items-center gap-2.5 hover:opacity-80 transition-opacity">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-600/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        </div>
                        <span class="text-base font-bold tracking-tight text-gray-900">GrowBuilder</span>
                    </Link>
                    <nav class="hidden md:flex items-center gap-8">
                        <Link href="/growbuilder" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Home</Link>
                        <Link href="/growbuilder#features" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Features</Link>
                        <span class="text-sm font-medium text-blue-600">Pricing</span>
                    </nav>
                    <div class="flex items-center gap-3">
                        <Link href="/login" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5">Sign In</Link>
                        <Link href="/register" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-5 py-2 rounded-lg transition-all shadow-lg shadow-blue-600/20">Get Started Free</Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Annual discount banner -->
        <div v-if="annualNotice && billing === 'annual'" class="bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <p class="text-sm text-green-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    You're saving 17–20% with annual billing compared to monthly.
                </p>
                <button @click="annualNotice = false" class="text-xs text-green-600 hover:text-green-800 font-medium">&times;</button>
            </div>
        </div>

        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-b from-blue-50/60 to-white py-16 sm:py-20">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-blue-100/40 to-transparent rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-medium mb-5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Simple, transparent pricing
                </div>
                <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-gray-900">Choose Your Plan</h1>
                <p class="mt-4 text-lg text-gray-500 max-w-xl mx-auto">Start free, upgrade as your business grows. No hidden fees.</p>

                <!-- Billing toggle -->
                <div class="mt-8 inline-flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                    <button
                        type="button"
                        @click="billing = 'monthly'"
                        :class="['px-5 py-2.5 text-sm font-semibold rounded-lg transition', billing === 'monthly' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
                    >Monthly</button>
                    <button
                        type="button"
                        @click="billing = 'annual'"
                        :class="['px-5 py-2.5 text-sm font-semibold rounded-lg transition', billing === 'annual' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
                    >
                        Annual
                        <span class="text-[11px] text-green-600 font-bold ml-1">Save ~20%</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Plans -->
        <section class="py-12 sm:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="tier in sortedTiers"
                        :key="tier.key"
                        :class="[
                            'relative rounded-2xl border flex flex-col transition-all duration-300',
                            tier.popular
                                ? 'border-blue-300 bg-white shadow-xl shadow-blue-200/20 scale-[1.02] lg:scale-105'
                                : 'border-gray-200 bg-white hover:shadow-lg hover:border-gray-300'
                        ]"
                    >
                        <div v-if="tier.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 z-10">
                            <span class="text-xs font-bold text-white bg-blue-600 px-4 py-1 rounded-full shadow-lg">Most Popular</span>
                        </div>

                        <!-- Header -->
                        <div :class="['p-6 pb-4 rounded-t-2xl', tier.popular ? 'bg-gradient-to-br from-blue-600 to-blue-700 text-white' : 'bg-gray-50']">
                            <h3 :class="['text-lg font-bold', tier.popular ? 'text-white' : 'text-gray-900']">{{ tier.name }}</h3>
                            <p :class="['text-sm mt-1', tier.popular ? 'text-blue-200' : 'text-gray-500']">{{ tier.description }}</p>
                            <div class="mt-5">
                                <div class="flex items-baseline gap-1">
                                    <span :class="['text-4xl font-extrabold tracking-tight', tier.popular ? 'text-white' : 'text-gray-900']">
                                        {{ billing === 'monthly' ? formatPrice(tier.price_monthly) : formatPrice(annualPrice(tier)) }}
                                    </span>
                                    <span v-if="tier.price_monthly > 0" :class="['text-sm font-medium', tier.popular ? 'text-blue-200' : 'text-gray-400']">
                                        /{{ billing === 'annual' ? 'yr' : 'mo' }}
                                    </span>
                                </div>
                                <p v-if="billing === 'annual' && annualSavings(tier) > 0" :class="['text-xs mt-1.5 font-semibold', tier.popular ? 'text-blue-200' : 'text-green-600']">
                                    Save {{ formatPrice(annualSavings(tier)) }}/year
                                </p>
                            </div>
                        </div>

                        <!-- Price per month detail on annual -->
                        <div v-if="billing === 'annual' && tier.price_monthly > 0" class="px-6 pt-3 pb-1">
                            <p class="text-xs text-gray-400">
                                {{ formatPrice(tier.price_monthly) }}/mo billed annually
                            </p>
                        </div>

                        <!-- Features -->
                        <div class="p-6 pt-4 flex-1">
                            <ul class="space-y-3">
                                <li v-for="fi in featuresList" :key="fi.key" class="flex items-start gap-2.5 text-sm">
                                    <svg v-if="tierHasFeature(tier, fi.key)" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <svg v-else class="w-4 h-4 text-gray-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    <span :class="tierHasFeature(tier, fi.key) ? 'text-gray-700' : 'text-gray-400'">
                                        {{ fi.label }}
                                        <template v-if="['sites', 'storage_mb', 'products', 'ai_prompts'].includes(fi.key)">
                                            <span v-if="tierHasFeature(tier, fi.key) && tier.limits?.[fi.key] !== undefined" class="text-gray-500">— {{ tierLimit(tier, fi.key) }}</span>
                                        </template>
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <!-- CTA -->
                        <div class="px-6 pb-6">
                            <Link
                                v-if="tier.price_monthly === 0"
                                href="/register"
                                :class="['block w-full text-center py-3 text-sm font-semibold rounded-xl transition border', tier.popular ? 'border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100' : 'border-gray-200 text-gray-700 bg-gray-50 hover:bg-gray-100']"
                            >Get Started Free</Link>
                            <Link
                                v-else
                                :href="route('register', { plan: tier.key })"
                                :class="['block w-full text-center py-3 text-sm font-semibold rounded-xl transition', tier.popular ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-600/20' : 'bg-gray-900 text-white hover:bg-gray-800']"
                            >Choose {{ tier.name }}</Link>
                        </div>
                    </div>
                </div>

                <!-- Note -->
                <p class="text-center text-sm text-gray-400 mt-10 max-w-lg mx-auto">
                    All plans include a free subdomain. Need more? <Link href="/contact" class="text-blue-600 hover:underline">Contact us</Link> for custom enterprise plans.
                </p>
            </div>
        </section>

        <!-- FAQ teaser -->
        <section class="border-t border-gray-100 bg-gray-50/50 py-16">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Questions?</h2>
                <p class="text-gray-500 mb-6">See our <Link href="/faq" class="text-blue-600 hover:underline font-medium">FAQ</Link> or <Link href="/contact" class="text-blue-600 hover:underline font-medium">contact support</Link>.</p>
                <div class="grid sm:grid-cols-3 gap-4 text-left">
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="font-semibold text-sm text-gray-900 mb-1">Can I switch plans?</p>
                        <p class="text-xs text-gray-500">Yes. Upgrade or downgrade anytime. Changes apply next billing cycle.</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="font-semibold text-sm text-gray-900 mb-1">Is there a contract?</p>
                        <p class="text-xs text-gray-500">No. All plans are month-to-month or annual. Cancel anytime.</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="font-semibold text-sm text-gray-900 mb-1">What payment methods?</p>
                        <p class="text-xs text-gray-500">Mobile Money (Airtel, MTN) and bank transfers. Wallet top-up available.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 bg-gradient-to-br from-blue-600 to-blue-800">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white">Start Building for Free</h2>
                <p class="mt-3 text-lg text-blue-200">No credit card required. Upgrade anytime.</p>
                <Link href="/register" class="mt-8 inline-flex items-center gap-2 px-8 py-3.5 text-base font-semibold text-blue-700 bg-white hover:bg-blue-50 rounded-xl transition-all shadow-xl">
                    Create Your Free Site
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-md bg-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        </div>
                        <span class="text-sm font-semibold text-white">GrowBuilder</span>
                        <span class="text-xs text-gray-500">by MyGrowNet</span>
                    </div>
                    <div class="flex items-center gap-6 text-xs">
                        <a href="/privacy" class="hover:text-white transition-colors">Privacy</a>
                        <a href="/terms" class="hover:text-white transition-colors">Terms</a>
                        <a href="/contact" class="hover:text-white transition-colors">Contact</a>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-800 text-center text-xs">
                    &copy; {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
