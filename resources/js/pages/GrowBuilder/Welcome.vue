<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    GlobeAltIcon,
    DevicePhoneMobileIcon,
    PaintBrushIcon,
    ShoppingCartIcon,
    CheckIcon,
    ArrowRightIcon,
    CommandLineIcon,
    PhotoIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

const features = [
    {
        icon: PaintBrushIcon,
        title: 'Drag & Drop Builder',
        description: 'Build beautiful websites without coding. Our intuitive drag-and-drop editor makes website creation effortless.',
    },
    {
        icon: DevicePhoneMobileIcon,
        title: 'Mobile Responsive',
        description: 'Every site looks stunning on all devices — desktops, tablets, and phones. Mobile-first design built in.',
    },
    {
        icon: ShoppingCartIcon,
        title: 'E-Commerce Ready',
        description: 'Add an online store to your website with products, payments, and order management built right in.',
    },
    {
        icon: PhotoIcon,
        title: 'Professional Templates',
        description: 'Start with beautifully designed templates for every industry. Customize colors, fonts, and layouts.',
    },
    {
        icon: MagnifyingGlassIcon,
        title: 'SEO Optimized',
        description: 'Built-in SEO tools help your website rank higher on Google. Meta tags, sitemaps, and analytics included.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Custom Domain',
        description: 'Connect your own domain name for a professional online presence. Free subdomain included to get started.',
    },
];

const benefits = [
    'No coding or technical skills needed',
    'Custom domain & hosting included',
    'Blog & content management system',
    'Contact forms & lead capture',
    'Social media integration',
    '24/7 support & maintenance',
];
</script>

<template>
    <Head>
        <title>GrowBuilder - Professional Website Builder | MyGrowNet</title>
        <meta name="description" content="Build professional websites for your Zambian business with GrowBuilder. Drag-and-drop builder, e-commerce, custom domains, and SEO tools." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-orange-50 via-white to-white">
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-lg shadow-orange-500/30">
                            <GlobeAltIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">GrowBuilder</span>
                    </div>
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors">Features</a>
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
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-orange-400/20 to-amber-400/20 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-sm font-medium mb-6">
                            <GlobeAltIcon class="h-4 w-4" />
                            Website Builder
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Build a Stunning
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Website</span>
                            <br />In Minutes
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            GrowBuilder makes it easy to create professional websites for your business. No coding required — just drag, drop, and publish.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-colors shadow-lg shadow-orange-500/30">
                                Build Your Site
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-orange-700 bg-orange-100 hover:bg-orange-200 rounded-xl transition-colors">See Templates</a>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-orange-500" />
                                Free to start
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-orange-500" />
                                No coding needed
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                    </div>
                                    <span class="text-xs text-slate-400 font-mono">mybusiness.growbuilder.site</span>
                                </div>
                                <div class="space-y-4">
                                    <div class="h-6 bg-slate-100 rounded w-3/4"></div>
                                    <div class="h-4 bg-slate-50 rounded w-full"></div>
                                    <div class="h-4 bg-slate-50 rounded w-5/6"></div>
                                    <div class="grid grid-cols-3 gap-3 mt-4">
                                        <div class="aspect-video bg-orange-100 rounded-lg"></div>
                                        <div class="aspect-video bg-amber-100 rounded-lg"></div>
                                        <div class="aspect-video bg-orange-100 rounded-lg"></div>
                                    </div>
                                    <div class="flex items-center gap-3 mt-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-100"></div>
                                        <div>
                                            <div class="h-3 bg-slate-100 rounded w-24"></div>
                                            <div class="h-2 bg-slate-50 rounded w-32 mt-1"></div>
                                        </div>
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
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Build Online</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">Powerful website building tools designed for Zambian businesses of all sizes.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title" class="group relative p-6 bg-slate-50 rounded-2xl hover:bg-white transition-all duration-300 hover:shadow-xl hover:shadow-orange-500/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center mb-4 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
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
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">GrowBuilder?</span>
                        </h2>
                        <p class="mt-4 text-lg text-slate-600">Built specifically for Zambian businesses that need a professional online presence without the complexity.</p>
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
                                Start Building
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <GlobeAltIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">1,000+</p>
                                <p class="text-sm text-slate-600">Websites Built</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <PaintBrushIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">50+</p>
                                <p class="text-sm text-slate-600">Templates</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <ShoppingCartIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">200+</p>
                                <p class="text-sm text-slate-600">Online Stores</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <CommandLineIcon class="h-8 w-8 text-orange-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">100%</p>
                                <p class="text-sm text-slate-600">No Code Required</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-orange-600 via-orange-600 to-amber-700 p-8 sm:p-12 lg:p-16 text-center">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                    </div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Build Your Website?</h2>
                        <p class="mt-4 text-lg text-orange-100 max-w-2xl mx-auto">Join hundreds of Zambian businesses online with a professional website built in minutes.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-orange-700 bg-white hover:bg-orange-50 rounded-xl transition-colors">
                                Create Your Website
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
                            <GlobeAltIcon class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-white">GrowBuilder</span>
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
