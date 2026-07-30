<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GrowNetGuestLayout from '@/layouts/GrowNetGuestLayout.vue';
import {
    BookOpenIcon,
    PlayCircleIcon,
    DocumentTextIcon,
    CloudArrowUpIcon,
    CheckIcon,
    ArrowRightIcon,
    AcademicCapIcon,
    StarIcon,
    UsersIcon,
    SparklesIcon,
    RocketLaunchIcon,
    BriefcaseIcon,
    CpuChipIcon,
} from '@heroicons/vue/24/outline';

const products = [
    {
        icon: PlayCircleIcon,
        title: 'GrowStream',
        tagline: 'Film & Comedy',
        description: 'Zambian movies, comedy shows, and drama series from local creators. Subscribe to your favourite creators or pay-per-view individual titles.',
        color: 'from-fuchsia-500 to-pink-600',
        shadow: 'shadow-fuchsia-500/25',
        status: 'Coming Soon',
    },
    {
        icon: AcademicCapIcon,
        title: 'GrowLearn',
        tagline: 'Practical Skills',
        description: 'Narrow, locally relevant skills training — from poultry farming and agribusiness to CV writing and small business finance. Live workshops included.',
        color: 'from-violet-500 to-purple-600',
        shadow: 'shadow-violet-500/25',
        status: 'Coming Soon',
    },
    {
        icon: DocumentTextIcon,
        title: 'GrowTemplates',
        tagline: 'Documents & Templates',
        description: 'Ready-to-use business plans, CV templates, invoice forms, and organisational documents. One-time purchase or unlimited-download subscription.',
        color: 'from-amber-500 to-orange-600',
        shadow: 'shadow-amber-500/25',
        status: 'Coming Soon',
    },
    {
        icon: CloudArrowUpIcon,
        title: 'GrowBackup',
        tagline: 'Cloud Storage',
        description: 'Secure cloud storage for your files, documents, and backups. Access from anywhere, share with your team.',
        color: 'from-sky-500 to-blue-600',
        shadow: 'shadow-sky-500/25',
        status: 'Coming Soon',
    },
    {
        icon: CpuChipIcon,
        title: 'GrowAI',
        tagline: 'AI-Powered Tools',
        description: 'AI-assisted content creation, document generation, and productivity tools built for the Zambian market.',
        color: 'from-emerald-500 to-teal-600',
        shadow: 'shadow-emerald-500/25',
        status: 'Coming Soon',
    },
];

const benefits = [
    'Real products with real value — no gimmicks',
    'Pay for what you use, skip what you don\'t',
    'Mobile money payments (MTN, Airtel, Zamtel)',
    'Content built for the Zambian market',
    'Creator-friendly revenue share (70/30)',
    'Download templates for offline use',
];

const stats = [
    { icon: BriefcaseIcon, value: '5', label: 'Products & Counting', color: 'text-violet-600', bg: 'bg-violet-50' },
    { icon: PlayCircleIcon, value: 'Coming', label: 'Streaming Library', color: 'text-fuchsia-600', bg: 'bg-fuchsia-50' },
    { icon: DocumentTextIcon, value: '100+', label: 'Templates Ready', color: 'text-amber-600', bg: 'bg-amber-50' },
    { icon: UsersIcon, value: '70/30', label: 'Creator Revenue Split', color: 'text-purple-600', bg: 'bg-purple-50' },
];
</script>

<template>
    <GrowNetGuestLayout>
        <Head>
            <title>GrowNet - Digital Product Platform | MyGrowNet</title>
            <meta name="description" content="GrowNet is a digital product platform offering GrowStream, GrowLearn, GrowTemplates, GrowBackup, and GrowAI — built for the Zambian market." />
        </Head>

        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <div class="absolute inset-0 -z-10 pointer-events-none">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[900px] bg-gradient-to-r from-violet-400/15 via-fuchsia-400/10 to-amber-400/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-gradient-to-l from-fuchsia-400/10 to-transparent rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-sm font-medium mb-6 shadow-sm">
                            <SparklesIcon class="h-4 w-4" />
                            Digital Product Platform
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            A Suite of Digital Products
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 via-fuchsia-600 to-amber-500">for Zambia</span>
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                            GrowNet brings together streaming, skills training, document templates, cloud storage, and AI tools&mdash;all built for the Zambian market. Each product stands on its own, with real value for real users.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 text-base font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 rounded-xl transition-all shadow-lg shadow-violet-500/30 hover:shadow-violet-500/40 hover:-translate-y-0.5 active:translate-y-0">
                                Join GrowNet
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <a href="#products" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 text-base font-semibold text-violet-700 bg-violet-100 hover:bg-violet-200 rounded-xl transition-colors">View Products</a>
                        </div>
                        <div class="mt-10 flex flex-wrap items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full bg-violet-100 flex items-center justify-center">
                                    <CheckIcon class="h-3.5 w-3.5 text-violet-600" />
                                </div>
                                Free to join
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full bg-fuchsia-100 flex items-center justify-center">
                                    <CheckIcon class="h-3.5 w-3.5 text-fuchsia-600" />
                                </div>
                                Pay per product
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center">
                                    <CheckIcon class="h-3.5 w-3.5 text-amber-600" />
                                </div>
                                Mobile money
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-500 via-fuchsia-500 to-amber-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="font-semibold text-slate-900">Product Lineup</h3>
                                    <span class="inline-flex items-center gap-1 text-xs text-violet-600 bg-violet-50 px-2 py-1 rounded-full font-medium">
                                        <SparklesIcon class="h-3 w-3" />
                                        v1 Launch
                                    </span>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-fuchsia-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <PlayCircleIcon class="w-5 h-5 text-fuchsia-600" />
                                            <span class="text-sm font-medium text-slate-700">GrowStream</span>
                                        </div>
                                        <span class="text-xs text-fuchsia-600 font-medium">Film & Comedy</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-violet-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <AcademicCapIcon class="w-5 h-5 text-violet-600" />
                                            <span class="text-sm font-medium text-slate-700">GrowLearn</span>
                                        </div>
                                        <span class="text-xs text-violet-600 font-medium">Skills Training</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <DocumentTextIcon class="w-5 h-5 text-amber-600" />
                                            <span class="text-sm font-medium text-slate-700">GrowTemplates</span>
                                        </div>
                                        <span class="text-xs text-amber-600 font-medium">Documents</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-sky-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <CloudArrowUpIcon class="w-5 h-5 text-sky-600" />
                                            <span class="text-sm font-medium text-slate-700">GrowBackup</span>
                                        </div>
                                        <span class="text-xs text-sky-600 font-medium">Cloud Storage</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <CpuChipIcon class="w-5 h-5 text-emerald-600" />
                                            <span class="text-sm font-medium text-slate-700">GrowAI</span>
                                        </div>
                                        <span class="text-xs text-emerald-600 font-medium">AI Tools</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="products" class="py-16 sm:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-sm font-medium mb-4">
                        <RocketLaunchIcon class="h-4 w-4" />
                        Product Suite
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                        Five Products,
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 via-fuchsia-600 to-amber-500">One Platform</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">Each product is built to generate real revenue from real users on its own merits. No gimmicks, no recruitment — just valuable digital products.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="product in products" :key="product.title"
                        class="group relative p-6 bg-slate-50/80 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5"
                        :class="`hover:${product.shadow}`">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center mb-4 shadow-lg transition-transform group-hover:scale-110"
                            :class="`${product.color} ${product.shadow}`">
                            <component :is="product.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ product.title }}</h3>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">{{ product.tagline }}</p>
                        <p class="text-slate-600 leading-relaxed text-sm">{{ product.description }}</p>
                        <span class="inline-block mt-3 text-xs font-semibold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full">{{ product.status }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 sm:py-24 bg-gradient-to-b from-violet-50/50 via-white to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-sm font-medium mb-4">
                            <StarIcon class="h-4 w-4" />
                            Why GrowNet?
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                            Digital Products Built
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-fuchsia-600">for Zambia</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">Every product addresses a real gap in the Zambian digital market — from film monetisation to locally relevant skills training.</p>
                        <ul class="mt-8 space-y-4">
                            <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-violet-600" />
                                </div>
                                <span class="text-slate-700">{{ benefit }}</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 rounded-xl transition-all shadow-lg shadow-violet-500/30 hover:shadow-violet-500/40">
                                Join GrowNet Today
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="stat in stats" :key="stat.label"
                                class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                                <component :is="stat.icon" class="h-8 w-8 mb-3" :class="stat.color" />
                                <p class="text-3xl font-bold text-slate-900">{{ stat.value }}</p>
                                <p class="text-sm" :class="stat.color">{{ stat.label }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-violet-600 via-fuchsia-600 to-amber-600 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Be First in Line</h2>
                        <p class="mt-4 text-lg text-violet-100 max-w-2xl mx-auto">GrowTemplates ships first, followed by GrowStream and GrowLearn. Join now to get early access and updates as each product launches.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-violet-700 bg-white hover:bg-violet-50 rounded-xl transition-colors shadow-lg">
                                Create Your Account
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <Link href="/contact" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-white/20 hover:bg-white/30 rounded-xl transition-colors">Contact Support</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </GrowNetGuestLayout>
</template>
