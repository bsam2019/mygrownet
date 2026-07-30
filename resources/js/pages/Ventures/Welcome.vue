<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import VenturesGuestLayout from '@/layouts/VenturesGuestLayout.vue';
import { ArrowRightIcon, CheckIcon, BriefcaseIcon, CurrencyDollarIcon, ChartBarIcon, DocumentTextIcon, BanknotesIcon, ShieldCheckIcon, UserGroupIcon, PresentationChartLineIcon, ClockIcon } from '@heroicons/vue/24/outline';

defineProps<{ featuredVentures?: any[] }>();

const stats = [
    { value: 'K2M+', label: 'Funds Invested' },
    { value: '500+', label: 'Active Investors' },
    { value: '20+', label: 'Active Ventures' },
    { value: '100%', label: 'Documented Deals' },
];

const features = [
    { icon: BriefcaseIcon, title: 'Browse Ventures', desc: 'Explore investment opportunities across Zambia. View business plans, growth potential, and funding goals for each venture.' },
    { icon: CurrencyDollarIcon, title: 'Invest & Co-own', desc: 'Co-invest in promising businesses and projects. Start with small amounts and build your investment portfolio over time.' },
    { icon: PresentationChartLineIcon, title: 'Portfolio Tracking', desc: 'Monitor your investments, track returns, and view performance metrics in real time from your personal dashboard.' },
    { icon: BanknotesIcon, title: 'Dividend Payouts', desc: 'Earn dividends from successful ventures. Track payouts and reinvest or withdraw your earnings.' },
    { icon: DocumentTextIcon, title: 'Investment Documents', desc: 'Access all investment agreements, contracts, and supporting documents securely in one place.' },
    { icon: ShieldCheckIcon, title: 'Secure & Transparent', desc: 'Every investment is backed by proper documentation and compliance. Full transparency on venture progress and milestones.' },
];

const steps = [
    { num: '01', title: 'Browse', desc: 'Explore vetted investment opportunities across Zambian industries' },
    { num: '02', title: 'Review', desc: 'Study business plans, financials, and team backgrounds in detail' },
    { num: '03', title: 'Invest', desc: 'Commit from K500 and become a legal shareholder in a registered company' },
    { num: '04', title: 'Earn', desc: 'Receive dividends and track your portfolio as ventures grow' },
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
    <VenturesGuestLayout>
        <Head>
            <title>Venture Builder - Investment Platform | MyGrowNet</title>
            <meta name="description" content="Venture Builder lets you co-invest in Zambian business ventures. Browse opportunities, track your portfolio, and earn returns." />
        </Head>

        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-orange-900">
            <div class="absolute inset-0">
                <div class="absolute top-20 -right-20 w-[500px] h-[500px] bg-orange-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] bg-amber-500/10 rounded-full blur-3xl"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-36">
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-6 border border-amber-500/20">
                            <BriefcaseIcon class="h-4 w-4" />
                            Zambian Investment Platform
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight">
                            Invest in
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-orange-400">Zambian</span>
                            <br />Ventures
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-amber-100/70 max-w-xl mx-auto lg:mx-0">
                            Co-invest in promising Zambian business opportunities. Browse ventures, track your portfolio, and earn returns as these businesses grow.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl hover:from-amber-400 hover:to-orange-500 transition-all shadow-xl shadow-orange-500/30">
                                Start Investing
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <Link href="#features" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-amber-100 bg-white/10 hover:bg-white/20 rounded-xl border border-white/10 transition-all">
                                Learn More
                            </Link>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-amber-200/60">
                            <span class="flex items-center gap-2"><CheckIcon class="h-4 w-4 text-amber-400" /> Free to join</span>
                            <span class="flex items-center gap-2"><CheckIcon class="h-4 w-4 text-amber-400" /> Start from K500</span>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="(stat, i) in stats" :key="stat.label" class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center hover:bg-white/10 transition-colors" :style="{ animationDelay: `${i * 100}ms` }">
                                <p class="text-3xl sm:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-orange-400">{{ stat.value }}</p>
                                <p class="text-sm text-amber-200/60 mt-1">{{ stat.label }}</p>
                            </div>
                        </div>
                        <div class="mt-4 bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center">
                            <p class="text-sm text-amber-200/70">Trusted by investors across all 10 provinces of Zambia</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Bar -->
        <section class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <p class="text-2xl sm:text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">{{ stat.value }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Current Opportunities -->
        <section v-if="featuredVentures && featuredVentures.length" class="py-16 sm:py-20 bg-gradient-to-b from-orange-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                        Current <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">Investment Opportunities</span>
                    </h2>
                    <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">Browse ventures currently seeking investors. Start building your portfolio today.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="v in featuredVentures" :key="v.id" class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-orange-500/10 hover:border-orange-100 transition-all">
                        <div class="h-40 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                            <BriefcaseIcon v-if="!v.featured_image" class="h-12 w-12 text-amber-300" />
                            <img v-else :src="v.featured_image" :alt="v.title" class="w-full h-full object-cover" />
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ v.status }}</span>
                                <span v-if="v.expected_roi_months" class="text-xs text-gray-400 flex items-center gap-1"><ClockIcon class="h-3 w-3" /> {{ v.expected_roi_months }}mo ROI</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-orange-600 transition-colors">{{ v.title }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ v.description }}</p>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Raised</span>
                                    <span class="font-semibold text-gray-900">K{{ Number(v.total_raised || 0).toLocaleString() }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-amber-400 to-orange-500 rounded-full transition-all" :style="{ width: Math.min(100, ((v.total_raised || 0) / (v.funding_target || 1)) * 100) + '%' }"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-400">
                                    <span>Target: K{{ Number(v.funding_target || 0).toLocaleString() }}</span>
                                    <span>{{ v.investor_count || 0 }} investors</span>
                                </div>
                            </div>
                            <Link :href="route('ventures.show', v.slug)" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl hover:from-amber-400 hover:to-orange-500 transition-all shadow-sm">
                                View Details <ArrowRightIcon class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-10">
                    <Link :href="route('ventures.index')" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-orange-700 bg-orange-100 hover:bg-orange-200 rounded-xl transition-colors">
                        View All Opportunities <ArrowRightIcon class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-16 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                        Everything You Need to
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">Invest & Grow</span>
                    </h2>
                    <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">A complete investment platform designed for Zambian businesses and investors.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="f in features" :key="f.title" class="group p-6 bg-gray-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-orange-500/10 border border-transparent hover:border-orange-100">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mb-4 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                            <component :is="f.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ f.title }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-16 sm:py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">How It Works</h2>
                    <p class="mt-4 text-lg text-gray-500">Four simple steps to start building your investment portfolio</p>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(step, i) in steps" :key="step.num" class="relative text-center">
                        <div v-if="i < steps.length - 1" class="hidden lg:block absolute top-8 left-[calc(50%+40px)] right-[-32px] h-0.5 bg-gradient-to-r from-amber-300 to-orange-300 z-0"></div>
                        <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white mx-auto mb-4 text-lg font-bold shadow-lg shadow-orange-500/30 relative z-10">{{ step.num }}</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-gray-500 max-w-[220px] mx-auto">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits -->
        <section class="py-16 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                            Why Choose
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">Venture Builder?</span>
                        </h2>
                        <p class="mt-4 text-lg text-gray-500">A transparent investment platform connecting Zambian businesses with local investors.</p>
                        <ul class="mt-8 space-y-3">
                            <li v-for="b in benefits" :key="b" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-amber-600" />
                                </div>
                                <span class="text-gray-600">{{ b }}</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link href="/register" class="inline-flex items-center gap-2 px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl hover:from-amber-400 hover:to-orange-500 transition-all shadow-lg shadow-orange-500/30">
                                Join Venture Builder Today <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <BriefcaseIcon class="h-8 w-8 text-orange-600 mb-3" />
                            <p class="text-3xl font-bold text-gray-900">20+</p>
                            <p class="text-sm text-gray-500">Active Ventures</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <CurrencyDollarIcon class="h-8 w-8 text-orange-600 mb-3" />
                            <p class="text-3xl font-bold text-gray-900">K2M+</p>
                            <p class="text-sm text-gray-500">Funds Invested</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <UserGroupIcon class="h-8 w-8 text-orange-600 mb-3" />
                            <p class="text-3xl font-bold text-gray-900">500+</p>
                            <p class="text-sm text-gray-500">Active Investors</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <ChartBarIcon class="h-8 w-8 text-orange-600 mb-3" />
                            <p class="text-3xl font-bold text-gray-900">100%</p>
                            <p class="text-sm text-gray-500">Documented Deals</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 sm:py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-orange-900 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Start Investing?</h2>
                        <p class="mt-4 text-lg text-amber-200/70 max-w-2xl mx-auto">Join hundreds of investors backing Zambian businesses and earning returns through Venture Builder.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-gray-900 bg-white hover:bg-amber-50 rounded-xl transition-all shadow-lg">
                                Create Your Account <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <Link href="/contact" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white bg-white/10 hover:bg-white/20 rounded-xl border border-white/10 transition-all">Contact Support</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </VenturesGuestLayout>
</template>
