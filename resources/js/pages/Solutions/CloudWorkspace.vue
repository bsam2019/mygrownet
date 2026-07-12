<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const apps = [
    { name: 'GrowStorage', desc: 'Cloud storage & backup', url: '/growbackup' },
    { name: 'BizDocs', desc: 'Document management', url: '/bizdocs' },
];
</script>

<template>
    <Head title="Cloud & Digital Workspace Solutions - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />

        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 relative overflow-hidden">
                    <div class="absolute top-10 left-20 w-48 h-48 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="absolute bottom-16 right-16 w-32 h-32 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="relative z-10">
                        <Link href="/solutions" class="text-blue-200 hover:text-white text-sm inline-block mb-3">← Back to Solutions</Link>
                        <h1 class="text-4xl font-bold text-white leading-tight">Cloud & Digital Workspace</h1>
                        <p class="mt-4 text-lg text-white text-opacity-90 max-w-3xl">Secure cloud storage, file backup, and document collaboration tools to keep your business data safe and accessible.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="bg-white rounded-lg shadow-lg p-8 md:p-12 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">What You Can Achieve</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div v-for="item in ['Secure cloud storage', 'File backup & restore', 'Document sharing & collaboration']" :key="item" class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                        <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                        <span class="text-gray-700">{{ item }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Supporting Apps</h2>
                <div class="grid sm:grid-cols-2 gap-4 max-w-lg">
                    <Link v-for="app in apps" :key="app.name" :href="app.url" class="block p-5 bg-gray-50 rounded-lg hover:bg-blue-50 transition group">
                        <h3 class="font-bold text-gray-800 group-hover:text-blue-600 transition">{{ app.name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ app.desc }}</p>
                    </Link>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
