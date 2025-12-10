<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalculatorIcon,
    DocumentTextIcon,
    ChartBarIcon,
    BanknotesIcon,
    CurrencyDollarIcon,
    ClipboardDocumentListIcon,
    CheckIcon,
    ArrowRightIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { CurrencyDollarIcon as CurrencyDollarSolidIcon } from '@heroicons/vue/24/solid';

const features = [
    {
        icon: DocumentTextIcon,
        title: 'Professional Invoicing',
        description: 'Create and send beautiful invoices in seconds. Track payments and send automatic reminders.',
    },
    {
        icon: CalculatorIcon,
        title: 'Expense Tracking',
        description: 'Capture receipts, categorize expenses, and stay on top of your business spending.',
    },
    {
        icon: ChartBarIcon,
        title: 'Financial Reports',
        description: 'Get instant insights with profit & loss, balance sheets, and cash flow reports.',
    },
    {
        icon: BanknotesIcon,
        title: 'Bank Reconciliation',
        description: 'Connect your bank accounts and reconcile transactions automatically.',
    },
    {
        icon: UserGroupIcon,
        title: 'Customer Management',
        description: 'Keep track of all your customers, their invoices, and payment history in one place.',
    },
    {
        icon: ClipboardDocumentListIcon,
        title: 'Tax Ready',
        description: 'Generate tax reports and stay compliant with ZRA requirements effortlessly.',
    },
];

const benefits = [
    'No accounting experience needed',
    'Mobile-friendly - manage finances anywhere',
    'Automatic payment reminders',
    'Multi-currency support',
    'Secure cloud backup',
    'Export to Excel & PDF',
];
</script>

<template>
    <Head>
        <title>GrowFinance - Simple Accounting for SMEs | MyGrowNet</title>
        <meta name="description" content="Simple, powerful accounting software for small businesses in Zambia. Create invoices, track expenses, and manage your finances with ease." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-emerald-50 via-white to-white">
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <CurrencyDollarSolidIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">GrowFinance</span>
                    </div>

                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Features</a>
                        <a href="#benefits" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Benefits</a>
                        <Link href="/contact" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Contact</Link>
                    </nav>

                    <!-- Auth Buttons -->
                    <div class="flex items-center gap-3">
                        <Link
                            href="/login?redirect=/growfinance/dashboard"
                            class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors"
                        >
                            Log in
                        </Link>
                        <Link
                            href="/register?redirect=/growfinance/dashboard"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-lg shadow-emerald-500/30"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-emerald-400/20 to-teal-400/20 rounded-full blur-3xl"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-sm font-medium mb-6">
                            <CurrencyDollarSolidIcon class="h-4 w-4" aria-hidden="true" />
                            Simple Accounting
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Manage Your
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
                                Finances
                            </span>
                            <br />With Ease
                        </h1>

                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            GrowFinance makes accounting simple for small businesses. Create invoices, track expenses, and get financial insights — no accounting degree required.
                        </p>

                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link
                                href="/register?redirect=/growfinance/dashboard"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-lg shadow-emerald-500/30"
                            >
                                Start Free Trial
                                <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>
                            <a
                                href="#features"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-xl transition-colors"
                            >
                                See Features
                            </a>
                        </div>

                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                                14-day free trial
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                                No credit card
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard Preview -->
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="font-semibold text-slate-900">Financial Overview</h3>
                                    <span class="text-xs text-slate-500">This Month</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-emerald-50 rounded-xl p-4">
                                        <p class="text-xs text-emerald-600 font-medium">Revenue</p>
                                        <p class="text-2xl font-bold text-emerald-700">K45,230</p>
                                        <p class="text-xs text-emerald-600">+12% from last month</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-4">
                                        <p class="text-xs text-slate-600 font-medium">Expenses</p>
                                        <p class="text-2xl font-bold text-slate-700">K18,450</p>
                                        <p class="text-xs text-slate-500">-5% from last month</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                                <DocumentTextIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">Invoice #1234</p>
                                                <p class="text-xs text-slate-500">ABC Company</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-semibold text-emerald-600">K5,000</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                                <ClipboardDocumentListIcon class="h-4 w-4 text-amber-600" aria-hidden="true" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">Office Supplies</p>
                                                <p class="text-xs text-slate-500">Expense</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-600">-K450</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-16 sm:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                        Everything You Need to
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Manage Money</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
                        Powerful accounting tools designed for small businesses, without the complexity.
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group relative p-6 bg-slate-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/10"
                    >
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                            <component :is="feature.icon" class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">
                            {{ feature.title }}
                        </h3>
                        <p class="text-slate-600">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section id="benefits" class="py-16 sm:py-24 bg-gradient-to-b from-emerald-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                            Why Choose
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">GrowFinance?</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">
                            Built specifically for Zambian SMEs, GrowFinance understands your business needs.
                        </p>

                        <ul class="mt-8 space-y-4">
                            <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                                </div>
                                <span class="text-slate-700">{{ benefit }}</span>
                            </li>
                        </ul>

                        <div class="mt-8">
                            <Link
                                href="/register?redirect=/growfinance/dashboard"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-lg shadow-emerald-500/30"
                            >
                                Get Started Today
                                <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>

                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <BuildingOfficeIcon class="h-8 w-8 text-emerald-600 mb-3" aria-hidden="true" />
                                <p class="text-3xl font-bold text-slate-900">500+</p>
                                <p class="text-sm text-slate-600">Businesses Trust Us</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <DocumentTextIcon class="h-8 w-8 text-emerald-600 mb-3" aria-hidden="true" />
                                <p class="text-3xl font-bold text-slate-900">10K+</p>
                                <p class="text-sm text-slate-600">Invoices Created</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <BanknotesIcon class="h-8 w-8 text-emerald-600 mb-3" aria-hidden="true" />
                                <p class="text-3xl font-bold text-slate-900">K50M+</p>
                                <p class="text-sm text-slate-600">Tracked Monthly</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ShieldCheckIcon class="h-8 w-8 text-emerald-600 mb-3" aria-hidden="true" />
                                <p class="text-3xl font-bold text-slate-900">100%</p>
                                <p class="text-sm text-slate-600">Secure & Private</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-emerald-600 to-teal-700 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>

                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">
                            Ready to Take Control of Your Finances?
                        </h2>
                        <p class="mt-4 text-lg text-emerald-100 max-w-2xl mx-auto">
                            Join hundreds of Zambian businesses already using GrowFinance to simplify their accounting.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                href="/register?redirect=/growfinance/dashboard"
                                class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-emerald-700 bg-white hover:bg-emerald-50 rounded-xl transition-colors"
                            >
                                Start Your Free Trial
                                <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>
                            <Link
                                href="/contact"
                                class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-white/20 hover:bg-white/30 rounded-xl transition-colors"
                            >
                                Contact Sales
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                            <CurrencyDollarSolidIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <span class="text-lg font-bold text-white">GrowFinance</span>
                        <span class="text-slate-500 text-sm ml-2">by MyGrowNet</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-slate-400">
                        <Link href="/privacy" class="hover:text-white transition-colors">Privacy</Link>
                        <Link href="/terms" class="hover:text-white transition-colors">Terms</Link>
                        <Link href="/contact" class="hover:text-white transition-colors">Contact</Link>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-slate-800 text-center text-sm text-slate-500">
                    © {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
