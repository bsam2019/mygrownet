<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import VenturesGuestLayout from '@/layouts/VenturesGuestLayout.vue';
import { Briefcase, TrendingUp, Shield, CheckCircle, Target, Users } from 'lucide-vue-next';
</script>

<template>
    <VenturesGuestLayout>
        <Head title="About - Venture Builder" />

        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-orange-900">
            <div class="absolute top-20 -left-20 w-[400px] h-[400px] bg-orange-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 right-20 w-[300px] h-[300px] bg-amber-500/10 rounded-full blur-3xl"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-6 border border-amber-500/20">
                    <Target class="h-4 w-4" /> Investment Platform
                </div>
                <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4">About Venture Builder</h1>
                <p class="text-xl text-amber-100/70 max-w-3xl mx-auto">Co-invest in vetted Zambian business projects and become a real shareholder</p>
            </div>
        </section>

        <!-- What is Venture Builder -->
        <section class="py-16 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">What is Venture Builder?</h2>
                    <p class="text-lg text-gray-500 max-w-3xl mx-auto leading-relaxed">
                        Venture Builder allows MyGrowNet members to co-invest in carefully vetted business projects.
                        Each funded project becomes a separate limited company, and investors become legal shareholders
                        with real ownership and dividend rights.
                    </p>
                </div>
                <div class="grid gap-8 sm:grid-cols-3">
                    <div v-for="card in [
                        { icon: Briefcase, title: 'Real Ownership', desc: 'Become a legal shareholder in a registered limited company. Your investment gives you real equity.' },
                        { icon: TrendingUp, title: 'Dividend Income', desc: 'Receive quarterly or annual dividends based on company profits. Build passive income streams.' },
                        { icon: Shield, title: 'Vetted Projects', desc: 'Every venture is thoroughly evaluated by our team. Only viable businesses with strong potential are listed.' },
                    ]" :key="card.title" class="group bg-gray-50 rounded-2xl p-6 hover:bg-white transition-all hover:shadow-xl hover:shadow-orange-500/10 border border-transparent hover:border-orange-100">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mb-4 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                            <component :is="card.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ card.title }}</h3>
                        <p class="text-gray-500">{{ card.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-16 sm:py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                    <p class="text-lg text-gray-500">Simple process from browsing to becoming a shareholder</p>
                </div>
                <div class="grid gap-8 sm:grid-cols-4">
                    <div v-for="(step, i) in [
                        { num: '01', title: 'Browse Ventures', desc: 'Explore available investment opportunities with detailed business plans' },
                        { num: '02', title: 'Review Details', desc: 'Study the business model, financials, and team behind each venture' },
                        { num: '03', title: 'Invest', desc: 'Choose your investment amount and complete the transaction securely' },
                        { num: '04', title: 'Earn Dividends', desc: 'Receive your share of profits as the business grows' },
                    ]" :key="step.num" class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white mx-auto mb-4 text-lg font-bold shadow-lg shadow-orange-500/30">{{ step.num }}</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-gray-500">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Invest -->
        <section class="py-16 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Why Invest Through Venture Builder?</h2>
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div v-for="b in [
                        { icon: CheckCircle, title: 'Legal Protection', desc: 'Each venture is a separate limited company with proper governance, shareholder agreements, and legal compliance.' },
                        { icon: CheckCircle, title: 'Diversification', desc: 'Invest in multiple ventures across different industries to spread risk and maximize returns.' },
                        { icon: CheckCircle, title: 'Professional Management', desc: 'MyGrowNet provides ongoing support, monitoring, and facilitation to ensure venture success.' },
                        { icon: CheckCircle, title: 'Transparent Reporting', desc: 'Regular updates, financial reports, and performance metrics keep you informed about your investments.' },
                    ]" :key="b.title" class="flex items-start gap-4 p-6 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-white hover:shadow-lg hover:shadow-orange-500/10 transition-all">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <component :is="b.icon" class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ b.title }}</h3>
                            <p class="text-gray-500 text-sm">{{ b.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Investment Details -->
        <section class="py-16 sm:py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 sm:p-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-8 text-center">Investment Details</h2>
                    <div class="grid gap-8 sm:grid-cols-3">
                        <div class="text-center p-6 bg-gradient-to-b from-amber-50 to-white rounded-xl border border-amber-100">
                            <h3 class="font-semibold text-gray-900 mb-2">Minimum Investment</h3>
                            <p class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">K500</p>
                            <p class="text-sm text-gray-500 mt-2">Per venture</p>
                        </div>
                        <div class="text-center p-6 bg-gradient-to-b from-amber-50 to-white rounded-xl border border-amber-100">
                            <h3 class="font-semibold text-gray-900 mb-2">MyGrowNet Stake</h3>
                            <p class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">10-20%</p>
                            <p class="text-sm text-gray-500 mt-2">Minority equity retained</p>
                        </div>
                        <div class="text-center p-6 bg-gradient-to-b from-amber-50 to-white rounded-xl border border-amber-100">
                            <h3 class="font-semibold text-gray-900 mb-2">Dividend Frequency</h3>
                            <p class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">Quarterly</p>
                            <p class="text-sm text-gray-500 mt-2">Or annual based on venture</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-orange-900 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Ready to Build Wealth?</h2>
                        <p class="text-xl text-amber-200/70 mb-8">Start investing in real businesses and earn dividends</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link :href="route('ventures.index')" class="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-900 font-semibold rounded-xl hover:bg-amber-50 transition-all shadow-lg">View Available Ventures</Link>
                            <Link href="/register" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white/30 text-white font-semibold rounded-xl hover:bg-white/10 transition-all">Create Account</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </VenturesGuestLayout>
</template>
