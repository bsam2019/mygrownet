<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    RocketLaunchIcon,
    ShoppingCartIcon,
    CurrencyDollarIcon,
    BuildingLibraryIcon,
    CloudArrowUpIcon,
    CpuChipIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

const categories = [
    {
        icon: RocketLaunchIcon,
        title: 'Business Growth',
        description: 'Help businesses start, grow, and reach more customers.',
        items: ['Business websites', 'Digital marketing', 'Social media advertising', 'Business management', 'Document management'],
        apps: [
            { name: 'GrowBuilder', url: '/growbuilder' },
            { name: 'BizBoost', url: '/bizboost/welcome' },
            { name: 'CMS', url: '/cms' },
            { name: 'BizDocs', url: '/bizdocs' },
        ],
        gradient: 'from-blue-600 to-indigo-600',
        light: 'bg-blue-50',
        text: 'text-blue-600',
    },
    {
        icon: ShoppingCartIcon,
        title: 'Online Selling',
        description: 'Everything needed to sell products and services online.',
        items: ['Online stores', 'Digital marketplace', 'Product catalogues', 'Order management'],
        apps: [
            { name: 'GrowMarket', url: '/growmarket' },
            { name: 'GrowMart', url: '/growmart' },
        ],
        gradient: 'from-emerald-600 to-teal-600',
        light: 'bg-emerald-50',
        text: 'text-emerald-600',
    },
    {
        icon: CurrencyDollarIcon,
        title: 'Investment & Wealth Creation',
        description: 'Allow members to participate in business opportunities.',
        items: ['Venture investment', 'Business partnerships', 'Referral network', 'Member rewards'],
        apps: [
            { name: 'Venture Builder', url: '/ventures/about' },
            { name: 'GrowNet', url: '/grownet' },
        ],
        gradient: 'from-violet-600 to-purple-600',
        light: 'bg-violet-50',
        text: 'text-violet-600',
    },
    {
        icon: BuildingLibraryIcon,
        title: 'Hospitality & Travel',
        description: 'Help accommodation providers manage bookings.',
        items: ['Property listings', 'Online reservations', 'Guest management'],
        apps: [
            { name: 'ZamStay', url: '/zamstay' },
        ],
        gradient: 'from-amber-600 to-orange-600',
        light: 'bg-amber-50',
        text: 'text-amber-600',
    },
    {
        icon: CloudArrowUpIcon,
        title: 'Cloud & Digital Workspace',
        description: 'Secure storage and collaboration.',
        items: ['Cloud storage', 'File backup', 'Secure document sharing'],
        apps: [
            { name: 'GrowStorage', url: '/growbackup' },
            { name: 'BizDocs', url: '/bizdocs' },
        ],
        gradient: 'from-sky-600 to-blue-600',
        light: 'bg-sky-50',
        text: 'text-sky-600',
    },
    {
        icon: CpuChipIcon,
        title: 'AI Solutions',
        description: 'Use AI to automate work and improve productivity.',
        items: ['AI marketing', 'Content generation', 'Customer engagement', 'Business automation'],
        apps: [
            { name: 'BizBoost', url: '/bizboost/welcome' },
        ],
        gradient: 'from-fuchsia-600 to-pink-600',
        light: 'bg-fuchsia-50',
        text: 'text-fuchsia-600',
    },
];
</script>

<template>
    <Head>
        <title>Services - MyGrowNet</title>
        <meta name="description" content="Explore MyGrowNet's range of business services — from digital marketing and e-commerce to cloud storage and AI solutions." />
    </Head>

    <div class="min-h-screen bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="max-w-2xl mb-16">
                <div class="w-12 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full mb-6"></div>
                <h1 class="text-4xl sm:text-5xl font-bold text-slate-900 tracking-tight">Our Services</h1>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">From marketing to cloud storage, we provide everything your business needs to grow and succeed in the digital economy.</p>
            </div>

            <div class="space-y-12">
                <div v-for="(cat, i) in categories" :key="i" class="group rounded-3xl border border-slate-100 p-8 sm:p-10 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                    <div class="lg:flex lg:gap-12">
                        <div class="lg:w-2/5">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg" :class="cat.gradient">
                                    <component :is="cat.icon" class="h-7 w-7 text-white" />
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-widest" :class="cat.text">{{ cat.title.split(' ')[0] }}</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ cat.title }}</h2>
                            <p class="mt-3 text-base text-slate-500 leading-relaxed">{{ cat.description }}</p>
                            <ul class="mt-6 space-y-3">
                                <li v-for="item in cat.items" :key="item" class="flex items-center gap-3 text-sm text-slate-600">
                                    <svg class="w-4 h-4 flex-shrink-0" :class="cat.text" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ item }}
                                </li>
                            </ul>
                        </div>
                        <div class="mt-8 lg:mt-0 lg:w-3/5">
                            <div class="h-full rounded-2xl p-6" :class="cat.light">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4">Supporting apps</p>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <Link
                                        v-for="app in cat.apps"
                                        :key="app.name"
                                        :href="app.url"
                                        class="flex items-center justify-between p-4 bg-white rounded-xl hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 border border-slate-100 group/app"
                                    >
                                        <span class="font-medium text-slate-900 text-sm group-hover/app" :class="cat.text">{{ app.name }}</span>
                                        <ArrowRightIcon class="w-4 h-4 text-slate-300 group-hover/app:translate-x-0.5 transition-transform" :class="cat.text" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
