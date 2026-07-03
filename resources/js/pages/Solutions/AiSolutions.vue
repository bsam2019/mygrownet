<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const apps = [
    { name: 'BizBoost', desc: 'AI marketing platform', url: '/bizboost/welcome' },
];
</script>

<template>
    <Head title="AI Solutions - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />

        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 relative overflow-hidden">
                    <div class="absolute top-10 left-20 w-48 h-48 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="absolute bottom-16 right-16 w-32 h-32 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="relative z-10">
                        <Link href="/solutions" class="text-blue-200 hover:text-white text-sm inline-block mb-3">← Back to Solutions</Link>
                        <h1 class="text-4xl font-bold text-white leading-tight">AI Solutions</h1>
                        <p class="mt-4 text-lg text-white text-opacity-90 max-w-3xl">Use artificial intelligence to automate marketing, engage customers, and boost productivity across your business.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="bg-white rounded-lg shadow-lg p-8 md:p-12 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">What You Can Achieve</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div v-for="item in ['AI-powered content generation', 'Social media automation', 'Customer engagement tools', 'Business process automation']" :key="item" class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                        <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                        <span class="text-gray-700">{{ item }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Supporting App</h2>
                <div class="max-w-sm">
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
