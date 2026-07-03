<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    UserGroupIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    ShareIcon,
    CheckIcon,
    ArrowRightIcon,
    GlobeAltIcon,
    StarIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';

const features = [
    {
        icon: UserGroupIcon,
        title: '3x7 Forced Matrix',
        description: 'Build your team with our proven matrix system. Earn commissions from your entire downline, not just direct referrals.',
    },
    {
        icon: CurrencyDollarIcon,
        title: 'Multi-Level Commissions',
        description: 'Earn from multiple levels of referrals with transparent, automated commission payouts directly to your wallet.',
    },
    {
        icon: ChartBarIcon,
        title: 'Real-Time Analytics',
        description: 'Track your network growth, commission earnings, and team performance with live dashboards and reports.',
    },
    {
        icon: ShareIcon,
        title: 'Referral Tracking',
        description: 'Unique referral codes and links for every member. Track exactly where your referrals come from.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Sponsor Management',
        description: 'Manage your sponsorship tree, view downline activity, and support your team members as they grow.',
    },
    {
        icon: StarIcon,
        title: 'Rewards & Recognition',
        description: 'Unlock achievements, bonuses, and recognition as you hit milestones in your network growth journey.',
    },
];

const benefits = [
    'Automated commission calculations',
    'Real-time network visualization',
    'Customizable referral links & codes',
    'Mobile-friendly dashboard',
    'Secure wallet integration',
    '24/7 support team',
];
</script>

<template>
    <Head>
        <title>GrowNet - Network Marketing & Referral Platform | MyGrowNet</title>
        <meta name="description" content="Build your network marketing business with GrowNet. 3x7 forced matrix, multi-level commissions, real-time analytics, and referral tracking." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-indigo-50 via-white to-white">
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <UsersIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">GrowNet</span>
                    </div>
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Features</a>
                        <a href="#benefits" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Benefits</a>
                        <Link href="/contact" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Contact</Link>
                    </nav>
                    <div class="flex items-center gap-3">
                        <Link href="/login" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Log in</Link>
                        <Link href="/register" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-lg shadow-indigo-500/30">Get Started</Link>
                    </div>
                </div>
            </div>
        </header>

        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-indigo-400/20 to-violet-400/20 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mb-6">
                            <UsersIcon class="h-4 w-4" />
                            Network Marketing
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Build Your
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Network</span>
                            <br />Earn Together
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            GrowNet is MyGrowNet's community referral and network marketing platform. Build your team, earn commissions, and grow your income with our proven 3x7 forced matrix system.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-lg shadow-indigo-500/30">
                                Join GrowNet
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-xl transition-colors">Learn More</a>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-indigo-500" />
                                Free to join
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-indigo-500" />
                                Instant commissions
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-violet-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="font-semibold text-slate-900">Network Overview</h3>
                                    <span class="text-xs text-slate-500">Your Team</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-indigo-50 rounded-xl p-4">
                                        <p class="text-xs text-indigo-600 font-medium">Team Members</p>
                                        <p class="text-2xl font-bold text-indigo-700">156</p>
                                        <p class="text-xs text-indigo-600">+12 this week</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-xl p-4">
                                        <p class="text-xs text-emerald-600 font-medium">Commissions</p>
                                        <p class="text-2xl font-bold text-emerald-700">K12,450</p>
                                        <p class="text-xs text-emerald-600">This month</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg" v-for="(item, i) in [{n:'Direct Referrals',v:24,c:'indigo'},{n:'Matrix Level 2',v:89,c:'violet'},{n:'Matrix Level 3',v:43,c:'purple'}]" :key="i">
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
                        Powerful Tools for
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Network Building</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">Everything you need to build, manage, and grow your referral network.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title" class="group relative p-6 bg-slate-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                            <component :is="feature.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ feature.title }}</h3>
                        <p class="text-slate-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="benefits" class="py-16 sm:py-24 bg-gradient-to-b from-indigo-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                            Why Choose
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">GrowNet?</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">Built specifically for network marketers who want transparency, automation, and real results.</p>
                        <ul class="mt-8 space-y-4">
                            <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-indigo-600" />
                                </div>
                                <span class="text-slate-700">{{ benefit }}</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-lg shadow-indigo-500/30">
                                Join GrowNet Today
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <UsersIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">5,000+</p>
                                <p class="text-sm text-slate-600">Active Members</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <CurrencyDollarIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">K2M+</p>
                                <p class="text-sm text-slate-600">Commissions Paid</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ChartBarIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">12</p>
                                <p class="text-sm text-slate-600">Commission Levels</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <StarIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">99%</p>
                                <p class="text-sm text-slate-600">Member Satisfaction</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-700 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Grow Your Network?</h2>
                        <p class="mt-4 text-lg text-indigo-100 max-w-2xl mx-auto">Join thousands of members building their income through GrowNet's proven network marketing system.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-indigo-700 bg-white hover:bg-indigo-50 rounded-xl transition-colors">
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
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                            <UsersIcon class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-white">GrowNet</span>
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
