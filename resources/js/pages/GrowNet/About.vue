<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GrowNetGuestLayout from '@/layouts/GrowNetGuestLayout.vue';
import { BookOpenIcon, SparklesIcon, UsersIcon, GlobeAltIcon, ShieldCheckIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';

const values = [
    { icon: BookOpenIcon, title: 'Education First', description: 'We believe knowledge is the foundation of growth. Every feature is built with learning in mind.' },
    { icon: UsersIcon, title: 'Community Driven', description: 'Our platform thrives on the contributions and engagement of our members across Zambia.' },
    { icon: GlobeAltIcon, title: 'Local Content', description: 'We champion Zambian creators, local movies, music, and stories that reflect our culture.' },
    { icon: ShieldCheckIcon, title: 'Accessible to All', description: 'Quality education and entertainment should be affordable and available to everyone.' },
];

const team = [
    { name: 'Content Team', role: 'Curating the best lessons, videos, and e-books for you.' },
    { name: 'Tech Team', role: 'Building and maintaining the platform to ensure a smooth experience.' },
    { name: 'Support Team', role: 'Here to help you with any questions or issues along the way.' },
];
</script>

<template>
    <GrowNetGuestLayout>
        <Head title="About - GrowNet" />
        <section class="relative overflow-hidden py-16 sm:py-24">
            <div class="absolute inset-0 -z-10 pointer-events-none">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-r from-violet-400/15 to-fuchsia-400/10 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-sm font-medium mb-6">
                        <SparklesIcon class="h-4 w-4" />
                        About GrowNet
                    </div>
                    <h1 class="text-4xl sm:text-5xl font-bold text-slate-900">
                        Empowering Zambians Through
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 via-fuchsia-600 to-amber-500">Digital Content</span>
                    </h1>
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">
                        GrowNet is a digital content platform built by MyGrowNet to provide accessible online lessons, local video streaming, e-books, and cloud storage to everyone in Zambia. We believe in the power of knowledge, entertainment, and community to drive personal and professional growth.
                    </p>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-slate-900">Our Values</h2>
                    <p class="mt-3 text-lg text-slate-600">The principles that guide everything we build.</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="value in values" :key="value.title" class="bg-violet-50/50 rounded-2xl p-6 text-center hover:bg-violet-50 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <component :is="value.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ value.title }}</h3>
                        <p class="text-sm text-slate-600">{{ value.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-gradient-to-b from-violet-50/30 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center mb-12">
                    <h2 class="text-3xl font-bold text-slate-900">Our Mission</h2>
                    <p class="mt-3 text-lg text-slate-600">To make quality digital content accessible, affordable, and rewarding for every Zambian.</p>
                </div>
                <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                    <p class="text-slate-700 leading-relaxed mb-6">
                        GrowNet started as part of the MyGrowNet ecosystem to provide members with valuable educational resources, entertainment, and tools for personal development. Today, we serve thousands of active members with content ranging from business skills to local Zambian entertainment.
                    </p>
                    <p class="text-slate-700 leading-relaxed">
                        We're committed to continuously expanding our library, improving our platform, and creating opportunities for Zambian creators to share their work with a wider audience.
                    </p>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-slate-900">Meet the Team</h2>
                    <p class="mt-3 text-lg text-slate-600">The people working to make GrowNet better every day.</p>
                </div>
                <div class="grid sm:grid-cols-3 gap-6 max-w-4xl mx-auto">
                    <div v-for="member in team" :key="member.name" class="text-center p-6 bg-violet-50/50 rounded-2xl">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <span class="text-xl font-bold text-white">{{ member.name.charAt(0) }}</span>
                        </div>
                        <h3 class="font-semibold text-slate-900">{{ member.name }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ member.role }}</p>
                    </div>
                </div>
            </div>
        </section>
    </GrowNetGuestLayout>
</template>
