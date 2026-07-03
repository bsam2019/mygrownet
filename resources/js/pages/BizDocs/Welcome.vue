<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    DocumentTextIcon,
    FolderIcon,
    ShieldCheckIcon,
    PencilSquareIcon,
    CheckIcon,
    ArrowRightIcon,
    DocumentArrowUpIcon,
    EyeIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

const features = [
    {
        icon: DocumentTextIcon,
        title: 'Document Management',
        description: 'Create, store, and organize all your business documents in one secure place. Easy search and retrieval.',
    },
    {
        icon: PencilSquareIcon,
        title: 'Business Profile',
        description: 'Build a professional business profile with company details, branding, and contact information.',
    },
    {
        icon: FolderIcon,
        title: 'Smart Organization',
        description: 'Organize documents by category, client, project, or date. Custom folders and tags for quick access.',
    },
    {
        icon: DocumentArrowUpIcon,
        title: 'Easy Upload & Sharing',
        description: 'Upload documents in any format. Share securely with clients, partners, or team members.',
    },
    {
        icon: EyeIcon,
        title: 'Document Preview',
        description: 'Preview documents directly in your browser. Support for PDFs, images, and common office formats.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Secure & Compliant',
        description: 'Enterprise-grade security with access controls, audit logs, and data encryption at rest and in transit.',
    },
];

const benefits = [
    'Unlimited document storage',
    'Advanced search & filtering',
    'Client portal for document sharing',
    'Version history & tracking',
    'Role-based access control',
    'Mobile-friendly interface',
];
</script>

<template>
    <Head>
        <title>BizDocs - Document Management for Businesses | MyGrowNet</title>
        <meta name="description" content="Professional document management system for Zambian businesses. Create, store, share, and manage all your business documents securely." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-sky-50 via-white to-white">
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/30">
                            <DocumentTextIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">BizDocs</span>
                    </div>
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-sky-600 transition-colors">Features</a>
                        <a href="#benefits" class="text-sm font-medium text-slate-600 hover:text-sky-600 transition-colors">Benefits</a>
                        <Link href="/contact" class="text-sm font-medium text-slate-600 hover:text-sky-600 transition-colors">Contact</Link>
                    </nav>
                    <div class="flex items-center gap-3">
                        <Link href="/login" class="text-sm font-medium text-slate-600 hover:text-sky-600 transition-colors">Log in</Link>
                        <Link href="/register" class="px-4 py-2 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-lg transition-colors shadow-lg shadow-sky-500/30">Get Started</Link>
                    </div>
                </div>
            </div>
        </header>

        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-sky-400/20 to-blue-400/20 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 text-sm font-medium mb-6">
                            <DocumentTextIcon class="h-4 w-4" />
                            Document Management
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Manage Your
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-blue-600">Documents</span>
                            <br />With Confidence
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            BizDocs is a professional document management system for Zambian businesses. Create, store, organize, and share your business documents securely.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition-colors shadow-lg shadow-sky-500/30">
                                Get Started Free
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-sky-700 bg-sky-100 hover:bg-sky-200 rounded-xl transition-colors">Explore Features</a>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-sky-500" />
                                Free starter plan
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-sky-500" />
                                Secure cloud storage
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-sky-500 to-blue-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-2">
                                        <FolderIcon class="h-5 w-5 text-sky-500" />
                                        <h3 class="font-semibold text-slate-900">My Documents</h3>
                                    </div>
                                    <span class="text-xs text-slate-500">12 documents</span>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="(doc, i) in [{n:'Business Registration',d:'PDF',s:'2.4 MB',c:'100'},{n:'Tax Returns 2025',d:'PDF',s:'1.8 MB',c:'bg-red-100 text-red-600'},{n:'Client Contracts',d:'DOCX',s:'3.1 MB',c:'100'},{n:'Company Profile',d:'PDF',s:'5.2 MB',c:'100'}]" :key="i" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg" :class="doc.d === 'PDF' ? 'bg-red-100' : 'bg-blue-100'" flex items-center justify-center>
                                                <span class="text-xs font-bold" :class="doc.d === 'PDF' ? 'text-red-600' : 'text-blue-600'">{{ doc.d }}</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">{{ doc.n }}</p>
                                                <p class="text-xs text-slate-500">{{ doc.s }}</p>
                                            </div>
                                        </div>
                                        <CheckIcon class="h-4 w-4 text-emerald-500" />
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
                        Everything You Need for
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-blue-600">Document Management</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">Powerful document tools designed for Zambian businesses of all sizes.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title" class="group relative p-6 bg-slate-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-sky-500/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center mb-4 shadow-lg shadow-sky-500/30 group-hover:scale-110 transition-transform">
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
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-blue-600">BizDocs?</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">Built for Zambian businesses that need professional document management without the complexity.</p>
                        <ul class="mt-8 space-y-4">
                            <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0">
                                    <CheckIcon class="h-4 w-4 text-sky-600" />
                                </div>
                                <span class="text-slate-700">{{ benefit }}</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition-colors shadow-lg shadow-sky-500/30">
                                Start Using BizDocs
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <DocumentTextIcon class="h-8 w-8 text-sky-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">50K+</p>
                                <p class="text-sm text-slate-600">Documents Stored</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <FolderIcon class="h-8 w-8 text-sky-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">2,000+</p>
                                <p class="text-sm text-slate-600">Business Users</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ClockIcon class="h-8 w-8 text-sky-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">99.9%</p>
                                <p class="text-sm text-slate-600">Uptime Guarantee</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ShieldCheckIcon class="h-8 w-8 text-sky-600 mb-3" />
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
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-sky-600 via-sky-600 to-blue-700 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Organize Your Documents?</h2>
                        <p class="mt-4 text-lg text-sky-100 max-w-2xl mx-auto">Join thousands of businesses using BizDocs to manage their documents professionally and securely.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-sky-700 bg-white hover:bg-sky-50 rounded-xl transition-colors">
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
                        <div class="w-8 h-8 rounded-lg bg-sky-600 flex items-center justify-center">
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
