<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const products = [
    { name: 'BizBoost', tagline: 'AI Marketing Platform', description: 'Grow your business with AI-powered content creation, customer management, and social media advertising tools.', url: 'https://bizboost.mygrownet.com' },
    { name: 'GrowBuilder', tagline: 'Website Builder', description: 'Build professional websites with our drag-and-drop builder. No coding required, stunning results.', url: 'https://growbuilder.mygrownet.com' },
    { name: 'GrowMarket', tagline: 'Digital Marketplace', description: 'A vibrant online marketplace connecting buyers and sellers. List products, reach customers, and grow sales.', url: '/growmarket' },
    { name: 'GrowMart', tagline: 'E-Commerce Platform', description: 'Full online grocery and e-commerce platform with product management, payments, and order tracking.', url: 'https://growmart.mygrownet.com' },
    { name: 'GrowNet', tagline: 'Digital Learning Platform', description: 'Access online lessons, video streaming, e-books, and cloud storage. Learn, grow, and earn rewards.', url: 'https://grownet.mygrownet.com' },
    { name: 'Venture Builder', tagline: 'Investment Platform', description: 'Co-invest in promising business ventures and projects. Participate in Zambia\'s growing business ecosystem.', url: 'https://venture.mygrownet.com' },
    { name: 'CMS', tagline: 'Business Management', description: 'Company management system for invoicing, quotations, customer management, and business administration.', url: '/cms' },
    { name: 'GrowStorage', tagline: 'Cloud Storage', description: 'Secure cloud storage and file backup. Store, share, and access your files from anywhere.', url: 'https://growstorage.mygrownet.com' },
    { name: 'BizDocs', tagline: 'Document Management', description: 'Professional document management. Create, store, organize, and share business documents securely.', url: 'https://bizdocs.mygrownet.com' },
    { name: 'ZamStay', tagline: 'Booking Platform', description: 'Accommodation and booking management. List properties, manage reservations, and grow your hospitality business.', url: 'https://zamstay.mygrownet.com' },
];
</script>

<template>
    <Head title="Products - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />

        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 relative overflow-hidden">
                    <div class="absolute top-10 left-20 w-48 h-48 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="absolute bottom-16 right-16 w-32 h-32 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="relative z-10">
                        <h1 class="text-4xl font-bold text-white leading-tight">Our Products</h1>
                        <p class="mt-4 text-lg text-white text-opacity-90 max-w-3xl">A complete ecosystem of digital tools designed to help your business grow, sell, manage, and succeed.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="product in products" :key="product.name" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">{{ product.tagline }}</p>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">{{ product.name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ product.description }}</p>
                    <Link :href="product.url" class="text-blue-600 font-semibold text-sm hover:text-blue-700 transition">Learn more →</Link>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
