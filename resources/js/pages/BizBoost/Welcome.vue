<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePWA } from '@/composables/usePWA';
import {
    SparklesIcon,
    UsersIcon,
    ShareIcon,
    ChartBarIcon,
    ShoppingBagIcon,
    CurrencyDollarIcon,
    CheckIcon,
    ArrowDownTrayIcon,
    DevicePhoneMobileIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';
import { SparklesIcon as SparklesSolidIcon } from '@heroicons/vue/24/solid';

interface Feature {
    icon: string;
    title: string;
    description: string;
}

interface PricingTier {
    id: string;
    name: string;
    price: number;
    price_annual: number;
    currency: string;
    period: string;
    description: string;
    features: string[];
    cta: string;
    popular: boolean;
}

interface Props {
    features: Feature[];
    pricingTiers: PricingTier[];
}

const props = defineProps<Props>();

// PWA functionality
const {
    isInstallable,
    isInstalled,
    isStandalone,
    promptInstall,
    getIOSInstallInstructions,
} = usePWA();

const isInstalling = ref(false);
const showIOSInstructions = ref(false);

// iOS instructions
const iosInstructions = computed(() => getIOSInstallInstructions());

// Icon mapping for features
const iconMap: Record<string, any> = {
    'sparkles': SparklesIcon,
    'users': UsersIcon,
    'share': ShareIcon,
    'chart-bar': ChartBarIcon,
    'shopping-bag': ShoppingBagIcon,
    'currency-dollar': CurrencyDollarIcon,
};

const getIcon = (iconName: string) => {
    return iconMap[iconName] || SparklesIcon;
};

// Handle install button click
const handleInstall = async () => {
    if (iosInstructions.value.show) {
        showIOSInstructions.value = true;
        return;
    }

    isInstalling.value = true;
    await promptInstall();
    isInstalling.value = false;
};

// Check if we should show install button
const showInstallButton = computed(() => {
    return (isInstallable.value || iosInstructions.value.show) && !isInstalled.value && !isStandalone.value;
});

// Billing cycle toggle
const billingCycle = ref<'monthly' | 'annual'>('monthly');

// Get display price based on billing cycle
const getDisplayPrice = (tier: PricingTier) => {
    if (tier.id === 'free') return 0;
    return billingCycle.value === 'annual' ? tier.price_annual : tier.price;
};

// Get period text based on billing cycle
const getPeriodText = (tier: PricingTier) => {
    if (tier.id === 'free') return 'forever';
    return billingCycle.value === 'annual' ? 'year' : 'month';
};
</script>

<template>
    <Head>
        <!-- Primary Meta Tags -->
        <title>BizBoost - AI-Powered Marketing for SMEs | MyGrowNet</title>
        <meta name="title" content="BizBoost - AI-Powered Marketing for SMEs | MyGrowNet" />
        <meta name="description" content="Grow your business with AI-powered content creation, customer management, and social media tools. BizBoost helps SMEs in Zambia market smarter and grow faster." />
        <meta name="keywords" content="BizBoost, SME marketing, AI content creation, customer management, social media tools, business growth, Zambia, MyGrowNet" />
        <meta name="author" content="MyGrowNet" />
        <meta name="robots" content="index, follow" />
        <meta name="language" content="English" />
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" content="/bizboost/welcome" />
        <meta property="og:site_name" content="MyGrowNet" />
        <meta property="og:title" content="BizBoost - AI-Powered Marketing for SMEs" />
        <meta property="og:description" content="Grow your business with AI-powered content creation, customer management, and social media tools. BizBoost helps SMEs market smarter." />
        <meta property="og:image" content="/images/bizboost/icon-512x512.png" />
        <meta property="og:image:width" content="512" />
        <meta property="og:image:height" content="512" />
        <meta property="og:image:alt" content="BizBoost - AI-Powered Marketing Platform" />
        <meta property="og:locale" content="en_ZM" />
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:url" content="/bizboost/welcome" />
        <meta name="twitter:title" content="BizBoost - AI-Powered Marketing for SMEs" />
        <meta name="twitter:description" content="Grow your business with AI-powered content creation, customer management, and social media tools. BizBoost helps SMEs market smarter." />
        <meta name="twitter:image" content="/images/bizboost/icon-512x512.png" />
        <meta name="twitter:image:alt" content="BizBoost - AI-Powered Marketing Platform" />
        
        <!-- Favicons - BizBoost specific -->
        <link rel="icon" type="image/png" sizes="32x32" href="/images/bizboost/icon-96x96.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="/images/bizboost/icon-72x72.png" />
        <link rel="apple-touch-icon" sizes="180x180" href="/images/bizboost/icon-192x192.png" />
        
        <!-- PWA -->
        <link rel="manifest" href="/bizboost-manifest.json" />
        <meta name="theme-color" content="#7c3aed" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <meta name="apple-mobile-web-app-title" content="BizBoost" />
        
        <!-- Mobile Optimization -->
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="format-detection" content="telephone=no" />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-violet-50 via-white to-white dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                            <SparklesSolidIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <span class="text-xl font-bold text-slate-900 dark:text-white">BizBoost</span>
                    </div>

                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">Features</a>
                        <a href="#pricing" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">Pricing</a>
                        <a href="#install" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">Install</a>
                    </nav>

                    <!-- Auth Buttons -->
                    <div class="flex items-center gap-3">
                        <Link
                            href="/login?redirect=/bizboost"
                            class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                        >
                            Log in
                        </Link>
                        <Link
                            href="/register?redirect=/bizboost"
                            class="px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors shadow-lg shadow-violet-500/30"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <!-- Background decoration -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-violet-400/20 to-purple-400/20 rounded-full blur-3xl"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <!-- Content -->
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-sm font-medium mb-6">
                            <SparklesSolidIcon class="h-4 w-4" aria-hidden="true" />
                            AI-Powered Marketing
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 dark:text-white leading-tight">
                            Grow Your Business
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-purple-600">
                                Smarter
                            </span>
                        </h1>

                        <p class="mt-6 text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-xl mx-auto lg:mx-0">
                            BizBoost helps SMEs create engaging content, manage customers, and track sales — all powered by AI. Start growing today.
                        </p>

                        <!-- CTA Buttons -->
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link
                                href="/register?redirect=/bizboost"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-violet-600 hover:bg-violet-700 rounded-xl transition-colors shadow-lg shadow-violet-500/30"
                            >
                                Get Started Free
                                <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>

                            <button
                                v-if="showInstallButton"
                                @click="handleInstall"
                                :disabled="isInstalling"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-violet-700 dark:text-violet-300 bg-violet-100 dark:bg-violet-900/30 hover:bg-violet-200 dark:hover:bg-violet-900/50 rounded-xl transition-colors disabled:opacity-50"
                            >
                                <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                                {{ isInstalling ? 'Installing...' : 'Install App' }}
                            </button>
                        </div>

                        <!-- Trust badges -->
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500 dark:text-slate-400">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                                Free to start
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                                No credit card
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                                Cancel anytime
                            </div>
                        </div>
                    </div>

                    <!-- App Preview -->
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-md lg:max-w-none">
                            <!-- Phone mockup -->
                            <div class="relative mx-auto w-64 sm:w-72">
                                <div class="absolute inset-0 bg-gradient-to-r from-violet-500 to-purple-500 rounded-[3rem] blur-2xl opacity-30"></div>
                                <div class="relative bg-slate-900 rounded-[2.5rem] p-2 shadow-2xl">
                                    <div class="bg-white dark:bg-slate-800 rounded-[2rem] overflow-hidden aspect-[9/19]">
                                        <!-- App screenshot placeholder -->
                                        <div class="h-full bg-gradient-to-b from-violet-50 to-white dark:from-slate-800 dark:to-slate-900 p-4">
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-8 h-8 rounded-lg bg-violet-500 flex items-center justify-center">
                                                    <SparklesSolidIcon class="h-4 w-4 text-white" aria-hidden="true" />
                                                </div>
                                                <span class="font-semibold text-slate-900 dark:text-white text-sm">BizBoost</span>
                                            </div>
                                            <div class="space-y-3">
                                                <div class="h-20 bg-gradient-to-r from-violet-100 to-purple-100 dark:from-violet-900/30 dark:to-purple-900/30 rounded-xl"></div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div class="h-16 bg-slate-100 dark:bg-slate-700 rounded-lg"></div>
                                                    <div class="h-16 bg-slate-100 dark:bg-slate-700 rounded-lg"></div>
                                                </div>
                                                <div class="h-24 bg-slate-100 dark:bg-slate-700 rounded-xl"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-16 sm:py-24 bg-white dark:bg-slate-800/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white">
                        Everything You Need to
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-purple-600">Grow</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Powerful tools designed specifically for small and medium businesses in Zambia.
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group relative p-6 bg-slate-50 dark:bg-slate-800 rounded-2xl hover:bg-white dark:hover:bg-slate-700 transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/10"
                    >
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center mb-4 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                            <component :is="getIcon(feature.icon)" class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                            {{ feature.title }}
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white">
                        Simple, Transparent
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-purple-600">Pricing</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Start free and upgrade as you grow. No hidden fees.
                    </p>

                    <!-- Billing Cycle Toggle -->
                    <div class="mt-8 inline-flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-1">
                        <button
                            @click="billingCycle = 'monthly'"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                                billingCycle === 'monthly'
                                    ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Monthly
                        </button>
                        <button
                            @click="billingCycle = 'annual'"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2',
                                billingCycle === 'annual'
                                    ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Annual
                            <span class="px-2 py-0.5 text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full">
                                Save 20%
                            </span>
                        </button>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-4">
                    <div
                        v-for="tier in pricingTiers"
                        :key="tier.id"
                        :class="[
                            'relative p-6 rounded-2xl transition-all duration-300',
                            tier.popular
                                ? 'bg-gradient-to-b from-violet-600 to-violet-700 text-white shadow-xl shadow-violet-500/30 scale-105 lg:scale-110 z-10'
                                : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-violet-300 dark:hover:border-violet-600'
                        ]"
                    >
                        <!-- Popular badge -->
                        <div
                            v-if="tier.popular"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-amber-400 text-amber-900 text-xs font-bold rounded-full"
                        >
                            Most Popular
                        </div>

                        <div class="text-center mb-6">
                            <h3 :class="['text-lg font-semibold', tier.popular ? 'text-white' : 'text-slate-900 dark:text-white']">
                                {{ tier.name }}
                            </h3>
                            <p :class="['text-sm mt-1', tier.popular ? 'text-violet-200' : 'text-slate-500 dark:text-slate-400']">
                                {{ tier.description }}
                            </p>
                            <div class="mt-4">
                                <span :class="['text-4xl font-bold', tier.popular ? 'text-white' : 'text-slate-900 dark:text-white']">
                                    {{ tier.currency }}{{ getDisplayPrice(tier).toLocaleString() }}
                                </span>
                                <span :class="['text-sm', tier.popular ? 'text-violet-200' : 'text-slate-500 dark:text-slate-400']">
                                    /{{ getPeriodText(tier) }}
                                </span>
                            </div>
                            <!-- Monthly equivalent for annual -->
                            <div
                                v-if="billingCycle === 'annual' && tier.id !== 'free' && tier.price_annual > 0"
                                :class="['text-xs mt-1', tier.popular ? 'text-violet-200' : 'text-slate-400 dark:text-slate-500']"
                            >
                                K{{ Math.round(tier.price_annual / 12).toLocaleString() }}/month
                            </div>
                        </div>

                        <ul class="space-y-3 mb-6">
                            <li
                                v-for="feature in tier.features"
                                :key="feature"
                                class="flex items-start gap-2"
                            >
                                <CheckIcon
                                    :class="['h-5 w-5 flex-shrink-0 mt-0.5', tier.popular ? 'text-violet-200' : 'text-emerald-500']"
                                    aria-hidden="true"
                                />
                                <span :class="['text-sm', tier.popular ? 'text-violet-100' : 'text-slate-600 dark:text-slate-400']">
                                    {{ feature }}
                                </span>
                            </li>
                        </ul>

                        <Link
                            :href="tier.id === 'business' ? '/contact' : `/register?redirect=/bizboost&plan=${tier.id}&billing=${billingCycle}`"
                            :class="[
                                'block w-full py-3 px-4 text-center text-sm font-semibold rounded-xl transition-colors',
                                tier.popular
                                    ? 'bg-white text-violet-700 hover:bg-violet-50'
                                    : 'bg-violet-600 text-white hover:bg-violet-700'
                            ]"
                        >
                            {{ tier.cta }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- PWA Install Section -->
        <section id="install" class="py-16 sm:py-24 bg-gradient-to-b from-violet-50 to-white dark:from-slate-800 dark:to-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-violet-600 via-violet-600 to-purple-700 p-8 sm:p-12 lg:p-16">
                    <!-- Background pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>

                    <div class="relative lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                        <div class="text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-sm font-medium mb-4">
                                <DevicePhoneMobileIcon class="h-4 w-4" aria-hidden="true" />
                                Install as App
                            </div>

                            <h2 class="text-3xl sm:text-4xl font-bold text-white">
                                Take BizBoost Everywhere
                            </h2>

                            <p class="mt-4 text-lg text-violet-100 max-w-xl">
                                Install BizBoost on your phone for quick access. Works offline and feels like a native app.
                            </p>

                            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                <button
                                    v-if="showInstallButton && !iosInstructions.show"
                                    @click="handleInstall"
                                    :disabled="isInstalling"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-violet-700 bg-white hover:bg-violet-50 rounded-xl transition-colors disabled:opacity-50"
                                >
                                    <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                                    {{ isInstalling ? 'Installing...' : 'Install Now' }}
                                </button>

                                <button
                                    v-else-if="iosInstructions.show"
                                    @click="showIOSInstructions = true"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-violet-700 bg-white hover:bg-violet-50 rounded-xl transition-colors"
                                >
                                    <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                                    How to Install
                                </button>

                                <div
                                    v-else-if="isInstalled || isStandalone"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-white/20 rounded-xl"
                                >
                                    <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                    Already Installed
                                </div>

                                <Link
                                    href="/register?redirect=/bizboost"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-white/20 hover:bg-white/30 rounded-xl transition-colors"
                                >
                                    Get Started First
                                </Link>
                            </div>
                        </div>

                        <!-- Phone illustration -->
                        <div class="hidden lg:block">
                            <div class="relative mx-auto w-48">
                                <div class="bg-white/10 rounded-[2rem] p-2">
                                    <div class="bg-white dark:bg-slate-800 rounded-[1.5rem] overflow-hidden aspect-[9/16]">
                                        <div class="h-full bg-gradient-to-b from-violet-100 to-white dark:from-slate-700 dark:to-slate-800 p-3 flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg mb-3">
                                                <SparklesSolidIcon class="h-8 w-8 text-white" aria-hidden="true" />
                                            </div>
                                            <span class="text-sm font-semibold text-slate-900 dark:text-white">BizBoost</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- iOS Instructions Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showIOSInstructions"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50"
                    @click.self="showIOSInstructions = false"
                >
                    <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Install BizBoost</h3>
                                <button
                                    @click="showIOSInstructions = false"
                                    class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                    aria-label="Close instructions"
                                >
                                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <p class="text-slate-600 dark:text-slate-400 mb-6">
                                To install BizBoost on your iPhone or iPad:
                            </p>

                            <ol class="space-y-4">
                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 font-semibold text-sm">
                                        1
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-slate-900 dark:text-white font-medium">Tap the Share button</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                            <ShareIcon class="h-4 w-4" aria-hidden="true" />
                                            at the bottom of Safari
                                        </p>
                                    </div>
                                </li>

                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 font-semibold text-sm">
                                        2
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-slate-900 dark:text-white font-medium">Tap "Add to Home Screen"</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                            in the share menu
                                        </p>
                                    </div>
                                </li>

                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 font-semibold text-sm">
                                        3
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-slate-900 dark:text-white font-medium">Tap "Add" to confirm</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                            BizBoost will appear on your home screen
                                        </p>
                                    </div>
                                </li>
                            </ol>

                            <button
                                @click="showIOSInstructions = false"
                                class="w-full mt-6 px-4 py-3 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-xl transition-colors"
                            >
                                Got it
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Footer -->
        <footer class="py-12 bg-slate-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Brand -->
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center">
                                <SparklesSolidIcon class="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <span class="text-xl font-bold">BizBoost</span>
                        </div>
                        <p class="text-slate-400 max-w-md">
                            AI-powered marketing tools for small and medium businesses. Part of the MyGrowNet platform.
                        </p>
                    </div>

                    <!-- Links -->
                    <div>
                        <h4 class="font-semibold mb-4">Product</h4>
                        <ul class="space-y-2 text-slate-400">
                            <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                            <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
                            <li><Link href="/login?redirect=/bizboost" class="hover:text-white transition-colors">Log in</Link></li>
                            <li><Link href="/register?redirect=/bizboost" class="hover:text-white transition-colors">Sign up</Link></li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h4 class="font-semibold mb-4">Legal</h4>
                        <ul class="space-y-2 text-slate-400">
                            <li><Link href="/terms" class="hover:text-white transition-colors">Terms of Service</Link></li>
                            <li><Link href="/privacy" class="hover:text-white transition-colors">Privacy Policy</Link></li>
                            <li><Link href="/" class="hover:text-white transition-colors">MyGrowNet</Link></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-slate-400 text-sm">
                        © {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                    </p>
                    <div class="flex items-center gap-2 text-slate-400 text-sm">
                        <span>A product of</span>
                        <Link href="/" class="text-white hover:text-violet-400 transition-colors font-medium">MyGrowNet</Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
