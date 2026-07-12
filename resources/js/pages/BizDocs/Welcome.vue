<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    DocumentTextIcon,
    ReceiptPercentIcon,
    BuildingLibraryIcon,
    PencilSquareIcon,
    CheckIcon,
    ArrowRightIcon,
    DocumentArrowUpIcon,
    EyeIcon,
    ClockIcon,
    Cog6ToothIcon,
    UserGroupIcon,
    ShieldCheckIcon,
    DocumentDuplicateIcon,
    TruckIcon,
    ScaleIcon,
} from '@heroicons/vue/24/outline';
import {
    DocumentTextIcon as DocumentTextSolid,
} from '@heroicons/vue/24/solid';

const features = [
    {
        icon: DocumentTextIcon,
        title: 'Professional Invoices',
        description: 'Create and send professional invoices in minutes. Choose from multiple templates with your branding.',
    },
    {
        icon: ReceiptPercentIcon,
        title: 'Receipts & Credit Notes',
        description: 'Generate receipts, credit notes, debit notes, and proforma invoices for complete financial documentation.',
    },
    {
        icon: TruckIcon,
        title: 'Purchase Orders',
        description: 'Create and manage purchase orders to streamline your procurement process with suppliers.',
    },
    {
        icon: ScaleIcon,
        title: 'Contracts & Reports',
        description: 'Generate contracts and business reports with professional formatting and your company branding.',
    },
    {
        icon: PencilSquareIcon,
        title: 'Business Profile',
        description: 'Set up your business profile with company details, logo, and branding for all your documents.',
    },
    {
        icon: UserGroupIcon,
        title: 'Customer Management',
        description: 'Maintain a customer directory for quick document creation and easy reference.',
    },
];

const benefits = [
    'Professional invoices, receipts & purchase orders',
    'Customizable templates with your branding',
    'Automatic document numbering',
    'PDF download & sharing',
    'Customer management directory',
    'Stationery & letterhead generator',
    'Payment recording & tracking',
    'Status history & document lifecycle',
];

const docTypes = [
    { name: 'Invoice', icon: DocumentTextIcon, color: 'from-emerald-500 to-teal-600' },
    { name: 'Receipt', icon: ReceiptPercentIcon, color: 'from-blue-500 to-indigo-600' },
    { name: 'Purchase Order', icon: TruckIcon, color: 'from-amber-500 to-orange-600' },
    { name: 'Credit Note', icon: DocumentDuplicateIcon, color: 'from-rose-500 to-pink-600' },
    { name: 'Contract', icon: ScaleIcon, color: 'from-violet-500 to-purple-600' },
    { name: 'Report', icon: BuildingLibraryIcon, color: 'from-cyan-500 to-sky-600' },
];
</script>

<template>
    <Head>
        <title>BizDocs - Invoices, Receipts & Business Documents | MyGrowNet</title>
        <meta name="description" content="Create professional invoices, receipts, purchase orders, and business documents for your Zambian business. Free templates, PDF export, and customer management." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-sky-50 via-white to-white">
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <DocumentTextIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">BizDocs</span>
                    </div>
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Features</a>
                        <a href="#benefits" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Benefits</a>
                        <Link href="/contact" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Contact</Link>
                    </nav>
                    <div class="flex items-center gap-3">
                        <Link href="/login" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Log in</Link>
                        <Link href="/register" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-lg shadow-emerald-500/30">Get Started</Link>
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
                            <DocumentTextIcon class="h-4 w-4" />
                            Document Generation
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Create Professional
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Invoices & Documents</span>
                            <br />In Minutes
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            Generate invoices, receipts, purchase orders, contracts, and reports with professional templates. Add your branding and export to PDF — no design skills needed.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-lg shadow-emerald-500/30">
                                Start Creating Free
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-xl transition-colors">Explore Features</a>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" />
                                Free starter plan
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" />
                                No design skills needed
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-emerald-500" />
                                PDF export included
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-2">
                                        <DocumentTextSolid class="h-5 w-5 text-emerald-500" />
                                        <h3 class="font-semibold text-slate-900">Recent Documents</h3>
                                    </div>
                                    <span class="text-xs text-slate-500">6 this month</span>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                                <span class="text-xs font-bold text-emerald-600">INV</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">Invoice #0042</p>
                                                <p class="text-xs text-slate-500">ZMW 12,500.00</p>
                                            </div>
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Paid</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <span class="text-xs font-bold text-blue-600">RCP</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">Receipt #0018</p>
                                                <p class="text-xs text-slate-500">ZMW 3,200.00</p>
                                            </div>
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">Final</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                                <span class="text-xs font-bold text-amber-600">PO</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">PO #0051</p>
                                                <p class="text-xs text-slate-500">Supplier: Prime Distributors</p>
                                            </div>
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="doc-types" class="py-16 sm:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                        Documents You Can
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Create</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">From invoices to contracts — generate any business document with ease.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="doc in docTypes" :key="doc.name" class="group relative p-6 bg-slate-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform" :class="doc.color">
                            <component :is="doc.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ doc.name }}</h3>
                        <p class="text-slate-600">Create professional {{ doc.name.toLowerCase() }}s with customizable templates and your business branding.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-16 sm:py-24 bg-gradient-to-b from-white to-sky-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                        Powerful Features for
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Every Document</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">Everything you need to create, manage, and deliver professional business documents.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title" class="group relative p-6 bg-white rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/10 border border-slate-100">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                            <component :is="feature.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ feature.title }}</h3>
                        <p class="text-slate-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="benefits" class="py-16 sm:py-24 bg-gradient-to-b from-sky-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                            Why Choose
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">BizDocs?</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">Built for Zambian businesses that need professional document generation without the complexity.</p>
                        <ul class="mt-8 space-y-4">
                            <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-emerald-600" />
                                </div>
                                <span class="text-slate-700">{{ benefit }}</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-lg shadow-emerald-500/30">
                                Start Using BizDocs
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <DocumentTextIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">10K+</p>
                                <p class="text-sm text-slate-600">Documents Generated</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <UserGroupIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">500+</p>
                                <p class="text-sm text-slate-600">Business Users</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ClockIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">99.9%</p>
                                <p class="text-sm text-slate-600">Uptime Guarantee</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ShieldCheckIcon class="h-8 w-8 text-emerald-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">100%</p>
                                <p class="text-sm text-slate-600">Data Encrypted</p>
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
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Create Professional Documents?</h2>
                        <p class="mt-4 text-lg text-emerald-100 max-w-2xl mx-auto">Join hundreds of businesses using BizDocs to generate invoices, receipts, and business documents.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-emerald-700 bg-white hover:bg-emerald-50 rounded-xl transition-colors">
                                Get Started Free
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
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
                            <DocumentTextIcon class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-white">BizDocs</span>
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
