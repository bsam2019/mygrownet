<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    BriefcaseIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    DocumentTextIcon,
    CheckIcon,
    ArrowRightIcon,
    BanknotesIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    PresentationChartLineIcon,
} from '@heroicons/vue/24/outline';

const features = [
    {
        icon: BriefcaseIcon,
        title: 'Browse Ventures',
        description: 'Explore investment opportunities across Zambia. View business plans, growth potential, and funding goals for each venture.',
    },
    {
        icon: CurrencyDollarIcon,
        title: 'Invest & Co-own',
        description: 'Co-invest in promising businesses and projects. Start with small amounts and build your investment portfolio over time.',
    },
    {
        icon: PresentationChartLineIcon,
        title: 'Portfolio Tracking',
        description: 'Monitor your investments, track returns, and view performance metrics in real time from your personal dashboard.',
    },
    {
        icon: BanknotesIcon,
        title: 'Dividend Payouts',
        description: 'Earn dividends from successful ventures. Track payouts and reinvest or withdraw your earnings.',
    },
    {
        icon: DocumentTextIcon,
        title: 'Investment Documents',
        description: 'Access all investment agreements, contracts, and supporting documents securely in one place.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Secure & Transparent',
        description: 'Every investment is backed by proper documentation and compliance. Full transparency on venture progress and milestones.',
    },
];

const benefits = [
    'Browse vetted investment opportunities',
    'Start investing with low minimum amounts',
    'Track portfolio performance in real time',
    'Receive regular dividend payouts',
    'Access investment documents anytime',
    'Diversify across multiple ventures',
    'Transparent reporting on venture progress',
    'Dedicated support team',
];
</script>

<template>
    <Head>
        <title>Venture Builder - Investment Platform | MyGrowNet</title>
        <meta name="description" content="Venture Builder lets you co-invest in Zambian business ventures. Browse opportunities, track your portfolio, and earn returns." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-orange-50 via-white to-white">
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/30">
                            <BriefcaseIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">Venture Builder</span>
                    </div>
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors">Products</a>
                        <a href="#benefits" class="text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors">Benefits</a>
                        <Link href="/contact" class="text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors">Contact</Link>
                    </nav>
                    <div class="flex items-center gap-3">
                        <Link href="/login" class="text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors">Log in</Link>
                        <Link href="/register" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors shadow-lg shadow-orange-500/30">Get Started</Link>
                    </div>
                </div>
            </div>
        </header>

        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-amber-400/20 to-orange-400/20 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-sm font-medium mb-6">
                            <BanknotesIcon class="h-4 w-4" />
                            Investment Platform
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Invest in
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">Zambian</span>
                            <br />Ventures
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            Venture Builder lets you co-invest in promising Zambian business opportunities. Browse ventures, track your portfolio, and earn returns as these businesses grow.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-colors shadow-lg shadow-orange-500/30">
                                Join Venture Builder
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-orange-700 bg-orange-100 hover:bg-orange-200 rounded-xl transition-colors">Explore Products</a>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-orange-500" />
                                Free to join
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-orange-500" />
                                Start small
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="font-semibold text-slate-900">Your Portfolio</h3>
                                    <span class="text-xs text-slate-500">Overview</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-orange-50 rounded-xl p-4">
                                        <p class="text-xs text-orange-600 font-medium">Active Investments</p>
                                        <p class="text-2xl font-bold text-orange-700">5</p>
                                        <p class="text-xs text-orange-600">Across 3 ventures</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-xl p-4">
                                        <p class="text-xs text-emerald-600 font-medium">Total Returns</p>
                                        <p class="text-2xl font-bold text-emerald-700">K8,450</p>
                                        <p class="text-xs text-emerald-600">This year</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg" v-for="(item, i) in [{n:'AgriTech Fund',v:'K5,000',c:'amber'},{n:'Green Energy',v:'K3,200',c:'orange'},{n:'Real Estate',v:'K2,500',c:'emerald'}]" :key="i">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full" :class="`bg-${item.c}-500`"></div>
                                            <span class="text-sm text-slate-600">{{ item.n }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900">{{ item.v }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-16 sm:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                        Everything You Need to
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">Invest & Grow</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">A complete investment platform designed for Zambian businesses and investors.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title" class="group relative p-6 bg-slate-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-orange-500/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mb-4 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                            <component :is="feature.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ feature.title }}</h3>
                        <p class="text-slate-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="benefits" class="py-16 sm:py-24 bg-gradient-to-b from-orange-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                            Why Choose
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">Venture Builder?</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">A transparent investment platform connecting Zambian businesses with local investors.</p>
                        <ul class="mt-8 space-y-4">
                            <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-orange-600" />
                                </div>
                                <span class="text-slate-700">{{ benefit }}</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-colors shadow-lg shadow-orange-500/30">
                                Join Venture Builder Today
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <BriefcaseIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">20+</p>
                                <p class="text-sm text-slate-600">Active Ventures</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <CurrencyDollarIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">K2M+</p>
                                <p class="text-sm text-slate-600">Funds Invested</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <UserGroupIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">500+</p>
                                <p class="text-sm text-slate-600">Active Investors</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ShieldCheckIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">100%</p>
                                <p class="text-sm text-slate-600">Documented Deals</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-600 via-orange-600 to-orange-700 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Start Investing?</h2>
                        <p class="mt-4 text-lg text-orange-100 max-w-2xl mx-auto">Join hundreds of investors backing Zambian businesses and earning returns through Venture Builder.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-orange-700 bg-white hover:bg-orange-50 rounded-xl transition-colors">
                                Create Your Account
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <Link href="/contact" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-white/20 hover:bg-white/30 rounded-xl transition-colors">Contact Support</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-slate-900 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-orange-600 flex items-center justify-center">
                            <BriefcaseIcon class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-white">Venture Builder</span>
                        <span class="text-slate-500 text-sm ml-2">by MyGrowNet</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-slate-400">
                        <Link href="/privacy" class="hover:text-white transition-colors">Privacy</Link>
                        <Link href="/terms" class="hover:text-white transition-colors">Terms</Link>
                        <Link href="/contact" class="hover:text-white transition-colors">Contact</Link>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-slate-800 text-center text-sm text-slate-500">
                    &copy; {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
