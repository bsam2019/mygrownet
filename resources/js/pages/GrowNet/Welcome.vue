<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    BookOpenIcon,
    PlayCircleIcon,
    DocumentTextIcon,
    CloudArrowUpIcon,
    CheckIcon,
    ArrowRightIcon,
    AcademicCapIcon,
    GlobeAltIcon,
    StarIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';

const features = [
    {
        icon: BookOpenIcon,
        title: 'Online Lessons',
        description: 'Access a growing library of interactive lessons and courses. Learn at your own pace with structured educational content.',
    },
    {
        icon: PlayCircleIcon,
        title: 'Video Streaming',
        description: 'Watch local movies, comedy shows, and content from Zambian creators. Entertainment at your fingertips.',
    },
    {
        icon: DocumentTextIcon,
        title: 'E-books & Resources',
        description: 'Download and read digital books, guides, and reference materials covering business, technology, and personal development.',
    },
    {
        icon: CloudArrowUpIcon,
        title: 'Cloud Storage',
        description: 'Store, organize, and access your files securely from anywhere. Share documents with your network seamlessly.',
    },
    {
        icon: AcademicCapIcon,
        title: 'Skill Development',
        description: 'Track your learning progress, earn certificates, and build valuable skills through curated educational paths.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Community Network',
        description: 'Connect with fellow members, share content, and grow together in a supportive digital community.',
    },
];

const benefits = [
    'Unlimited access to lessons and courses',
    'Watch local movies, comedy, and content',
    'Download e-books for offline reading',
    'Secure cloud file storage included',
    'Track progress across all content',
    'Earn rewards as you learn and participate',
];
</script>

<template>
    <Head>
        <title>GrowNet - Digital Content Platform | MyGrowNet</title>
        <meta name="description" content="GrowNet is a digital content platform offering online lessons, local video streaming, e-books, and cloud storage. Learn, watch, and earn rewards." />
    </Head>

    <div class="min-h-screen bg-gradient-to-b from-indigo-50 via-white to-white">
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <BookOpenIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900">GrowNet</span>
                    </div>
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Products</a>
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
                            <BookOpenIcon class="h-4 w-4" />
                            Digital Content Platform
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Learn, Watch,
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Grow</span>
                            <br />with GrowNet
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0">
                            GrowNet is a digital content platform offering online lessons, local video streaming, e-books, and cloud storage. Learn, watch, and earn rewards along the way.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-lg shadow-indigo-500/30">
                                Join GrowNet
                                <ArrowRightIcon class="h-5 w-5" />
                            </Link>
                            <a href="#features" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-xl transition-colors">Explore Products</a>
                        </div>
                        <div class="mt-10 flex items-center gap-6 justify-center lg:justify-start text-sm text-slate-500">
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-indigo-500" />
                                Free to join
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckIcon class="h-5 w-5 text-indigo-500" />
                                Instant access
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 relative">
                        <div class="relative mx-auto max-w-lg">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-violet-500 rounded-2xl blur-2xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="font-semibold text-slate-900">Your Content Hub</h3>
                                    <span class="text-xs text-slate-500">Activity</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-indigo-50 rounded-xl p-4">
                                        <p class="text-xs text-indigo-600 font-medium">Lessons Completed</p>
                                        <p class="text-2xl font-bold text-indigo-700">24</p>
                                        <p class="text-xs text-indigo-600">+5 this week</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-xl p-4">
                                        <p class="text-xs text-emerald-600 font-medium">E-books Downloaded</p>
                                        <p class="text-2xl font-bold text-emerald-700">12</p>
                                        <p class="text-xs text-emerald-600">This month</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg" v-for="(item, i) in [{n:'Videos Watched',v:18,c:'indigo'},{n:'Files Stored',v:47,c:'violet'},{n:'Rewards Earned',v:K350,c:'purple'}]" :key="i">
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
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Learn & Grow</span>
                    </h2>
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">A complete ecosystem of digital content designed to educate, entertain, and empower you.</p>
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
                        <p class="mt-4 text-lg text-slate-600">A complete digital learning platform with content, storage, and rewards built in.</p>
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
                                <BookOpenIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">100+</p>
                                <p class="text-sm text-slate-600">Lessons & Courses</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <PlayCircleIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">500+</p>
                                <p class="text-sm text-slate-600">Local Videos & Movies</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <DocumentTextIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">50+</p>
                                <p class="text-sm text-slate-600">E-books Available</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100">
                                <UsersIcon class="h-8 w-8 text-indigo-600 mb-3" />
                                <p class="text-3xl font-bold text-slate-900">5,000+</p>
                                <p class="text-sm text-slate-600">Active Members</p>
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
                        <h2 class="text-3xl sm:text-4xl font-bold text-white">Ready to Get Started?</h2>
                        <p class="mt-4 text-lg text-indigo-100 max-w-2xl mx-auto">Join thousands of members learning, watching, and earning rewards through GrowNet.</p>
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
                            <BookOpenIcon class="h-5 w-5 text-white" />
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
