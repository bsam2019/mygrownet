<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArchiveBoxIcon,
    ClipboardDocumentCheckIcon,
    BanknotesIcon,
    ChartBarIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
    CheckIcon,
    UsersIcon,
    Cog6ToothIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

const features = [
    {
        icon: ArchiveBoxIcon,
        title: 'Inventory Management',
        description: 'Track stock levels in real-time across multiple locations, bins, and departments. Never run out of stock again.',
    },
    {
        icon: ClipboardDocumentCheckIcon,
        title: 'Stock Audits',
        description: 'Run physical counts, reconcile variances, and generate audit reports with full traceability.',
    },
    {
        icon: BanknotesIcon,
        title: 'Cash Register',
        description: 'Manage daily cash, track expenses, record banking, and reconcile with expected closing balances.',
    },
    {
        icon: ChartBarIcon,
        title: 'Sales & Purchases',
        description: 'Record sales with auto-stock-deduction, manage purchase orders, and track supplier relationships.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Expiry Tracking',
        description: 'Monitor expiry dates across your inventory, get alerts, and reduce wastage.',
    },
    {
        icon: UsersIcon,
        title: 'Multi-User Access',
        description: 'Assign roles and permissions to employees. Each user sees only what they need.',
    },
];

const benefits = [
    'Real-time stock visibility across all locations',
    'Automated stock deductions on sales',
    'Physical count and audit reconciliation',
    'Cash register with expense and banking tracking',
    'Expiry date monitoring and alerts',
    'Role-based employee access control',
];
</script>

<template>
    <Head>
        <title>StockFlow - Inventory & Audit Management | MyGrowNet</title>
        <meta name="description" content="StockFlow is a complete inventory management, stock audit, and business analytics solution for modern businesses." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-emerald-50 via-white to-white">
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <ArchiveBoxIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">StockFlow</span>
                    </div>
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Features</a>
                        <a href="#benefits" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Benefits</a>
                        <a href="/contact" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Contact</a>
                    </nav>
                    <div class="flex items-center gap-3">
                        <a href="/login" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Log in</a>
                        <a href="/register" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-lg shadow-emerald-500/30">Get Started</a>
                    </div>
                </div>
            </div>
        </header>

        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-emerald-400/20 to-teal-400/20 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-sm font-medium mb-6">
                            <ArchiveBoxIcon class="h-4 w-4" />
                            Inventory & Audit Management
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Complete Stock
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Control</span>
                            <br />for Your Business
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            Track inventory, run audits, manage cash registers, and keep your business running smoothly with StockFlow&mdash;the all-in-one stock management solution.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-lg shadow-emerald-500/30">
                                Get Started Free
                                <ArrowRightIcon class="h-5 w-5" />
                            </a>
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-xl transition-colors">Explore Features</a>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" />
                                Free to start
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" />
                                No setup fees
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" />
                                Multi-branch
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="font-semibold text-slate-900">Dashboard at a Glance</h3>
                                    <span class="text-xs text-slate-500">Live Demo</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-emerald-50 rounded-xl p-4">
                                        <p class="text-xs text-emerald-600 font-medium">Total Items</p>
                                        <p class="text-2xl font-bold text-emerald-700">1,284</p>
                                        <p class="text-xs text-emerald-600">Across 6 bins</p>
                                    </div>
                                    <div class="bg-amber-50 rounded-xl p-4">
                                        <p class="text-xs text-amber-600 font-medium">Low Stock</p>
                                        <p class="text-2xl font-bold text-amber-700">23</p>
                                        <p class="text-xs text-amber-600">Need reorder</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                            <span class="text-sm text-slate-600">Today's Sales</span>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900">K3,450</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                            <span class="text-sm text-slate-600">Pending Orders</span>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900">8</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                            <span class="text-sm text-slate-600">Register Balance</span>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900">K12,780</span>
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
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Manage Stock</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">A complete ecosystem of inventory tools designed to keep your business running efficiently.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title" class="group relative p-6 bg-slate-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                            <component :is="feature.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ feature.title }}</h3>
                        <p class="text-slate-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="benefits" class="py-16 sm:py-24 bg-gradient-to-b from-emerald-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                            Why Choose
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">StockFlow?</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">Built for businesses that need reliable inventory control and audit-ready stock records.</p>
                        <ul class="mt-8 space-y-4">
                            <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-emerald-600" />
                                </div>
                                <span class="text-slate-700">{{ benefit }}</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <a href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-lg shadow-emerald-500/30">
                                Start Using StockFlow
                                <ArrowRightIcon class="h-5 w-5" />
                            </a>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <DocumentTextIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">10,000+</p>
                                <p class="text-sm text-slate-600">Items Tracked</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ClipboardDocumentCheckIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">500+</p>
                                <p class="text-sm text-slate-600">Audits Completed</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <BanknotesIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">99.9%</p>
                                <p class="text-sm text-slate-600">Uptime Guarantee</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <Cog6ToothIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">24/7</p>
                                <p class="text-sm text-slate-600">Support Available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-emerald-600 to-teal-700 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Take Control of Your Inventory?</h2>
                        <p class="mt-4 text-lg text-emerald-100 max-w-2xl mx-auto">Join businesses using StockFlow to manage stock, run audits, and keep accurate records.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-emerald-700 bg-white hover:bg-emerald-50 rounded-xl transition-colors">
                                Create Your Account
                                <ArrowRightIcon class="h-5 w-5" />
                            </a>
                            <Link href="/contact" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-white/20 hover:bg-white/30 rounded-xl transition-colors">Contact Sales</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-slate-900 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                            <ArchiveBoxIcon class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-white">StockFlow</span>
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
