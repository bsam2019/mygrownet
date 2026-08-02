<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-emerald-50">
        <!-- Header -->
        <header class="border-b border-gray-200 bg-white/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                            <ChartBarIcon class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">GrowFinance</h1>
                            <p class="text-xs text-gray-500">Financial Management</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <a
                            :href="route('growfinance.subdomain.login')"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 inline-block"
                        >
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                        Enterprise Financial Management
                    </h2>
                    <p class="mt-6 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
                        Comprehensive accounting, budgeting, and financial reporting for growing businesses.
                        Real-time insights, automated workflows, and powerful analytics.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a
                            :href="route('growfinance.subdomain.register')"
                            class="rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-700 inline-block"
                        >
                            Get Started
                        </a>
                        <a href="#features" class="text-base font-semibold leading-7 text-gray-900">
                            Learn more <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h3 class="text-3xl font-bold text-gray-900">Everything You Need</h3>
                    <p class="mt-4 text-lg text-gray-600">Powerful features to manage your finances</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 rounded-xl border border-gray-200 hover:border-blue-500 transition-colors">
                        <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                            <ChartBarIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Accounting</h4>
                        <p class="text-gray-600">Complete double-entry accounting with chart of accounts, journals, and financial statements.</p>
                    </div>

                    <div class="p-6 rounded-xl border border-gray-200 hover:border-blue-500 transition-colors">
                        <div class="h-12 w-12 rounded-lg bg-emerald-100 flex items-center justify-center mb-4">
                            <DocumentTextIcon class="h-6 w-6 text-emerald-600" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Invoicing</h4>
                        <p class="text-gray-600">Professional invoices with custom templates, recurring billing, and automated reminders.</p>
                    </div>

                    <div class="p-6 rounded-xl border border-gray-200 hover:border-blue-500 transition-colors">
                        <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center mb-4">
                            <CurrencyDollarIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Budgeting</h4>
                        <p class="text-gray-600">Create budgets, track spending, and get alerts when approaching limits.</p>
                    </div>

                    <div class="p-6 rounded-xl border border-gray-200 hover:border-blue-500 transition-colors">
                        <div class="h-12 w-12 rounded-lg bg-amber-100 flex items-center justify-center mb-4">
                            <DocumentChartBarIcon class="h-6 w-6 text-amber-600" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Reporting</h4>
                        <p class="text-gray-600">Comprehensive financial reports with real-time data and customizable dashboards.</p>
                    </div>

                    <div class="p-6 rounded-xl border border-gray-200 hover:border-blue-500 transition-colors">
                        <div class="h-12 w-12 rounded-lg bg-rose-100 flex items-center justify-center mb-4">
                            <BanknotesIcon class="h-6 w-6 text-rose-600" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Cash Flow</h4>
                        <p class="text-gray-600">Monitor cash flow, forecast future trends, and optimize working capital.</p>
                    </div>

                    <div class="p-6 rounded-xl border border-gray-200 hover:border-blue-500 transition-colors">
                        <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center mb-4">
                            <ShieldCheckIcon class="h-6 w-6 text-indigo-600" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Tax Management</h4>
                        <p class="text-gray-600">Track tax obligations, generate tax reports, and prepare for filing with ease.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-blue-600">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h3 class="text-3xl font-bold text-white">Ready to Transform Your Finances?</h3>
                <p class="mt-4 text-lg text-blue-100">Start your free trial today. No credit card required.</p>
                <div class="mt-10">
                    <a
                        :href="route('growfinance.subdomain.register')"
                        class="rounded-lg bg-white px-8 py-4 text-base font-semibold text-blue-600 shadow-sm hover:bg-gray-50 inline-block"
                    >
                        Get Started Free
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">
                    © {{ new Date().getFullYear() }} GrowFinance by MyGrowNet. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import {
    ChartBarIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    DocumentChartBarIcon,
    BanknotesIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

defineProps<{
    routePrefix?: string;
}>();
</script>
