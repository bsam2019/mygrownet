<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CloudArrowUpIcon, ShieldCheckIcon, FolderIcon, ShareIcon } from '@heroicons/vue/24/outline';

const features = [
    {
        name: 'Secure Cloud Storage',
        description: 'Your files are encrypted and stored securely in the cloud with enterprise-grade security.',
        icon: ShieldCheckIcon,
    },
    {
        name: 'Easy File Management',
        description: 'Organize your files with folders, rename, move, and delete with an intuitive interface.',
        icon: FolderIcon,
    },
    {
        name: 'Direct Upload',
        description: 'Upload files directly to cloud storage for faster transfers and better reliability.',
        icon: CloudArrowUpIcon,
    },
    {
        name: 'File Sharing',
        description: 'Share files securely with expiring links and password protection (coming soon).',
        icon: ShareIcon,
    },
];

const plans = [
    {
        name: 'Starter',
        price: 'K50',
        storage: '2 GB',
        maxFile: '25 MB',
        features: ['Basic file management', 'Folder organization', 'Mobile access'],
    },
    {
        name: 'Basic',
        price: 'K150',
        storage: '20 GB',
        maxFile: '100 MB',
        popular: true,
        features: ['Everything in Starter', 'File sharing', 'Priority support'],
    },
    {
        name: 'Growth',
        price: 'K500',
        storage: '100 GB',
        maxFile: '500 MB',
        features: ['Everything in Basic', 'File versioning', 'Team folders'],
    },
    {
        name: 'Pro',
        price: 'K1,500',
        storage: '500 GB',
        maxFile: '2 GB',
        features: ['Everything in Growth', 'Unlimited sharing', 'Premium support'],
    },
];
</script>

<template>
    <Head title="GrowBackup - Secure Cloud Storage" />

    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <span class="text-2xl">☁️</span>
                        <span class="ml-2 text-xl font-bold text-gray-900">GrowBackup</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            href="/login"
                            class="text-gray-700 hover:text-gray-900"
                        >
                            Sign In
                        </Link>
                        <Link
                            href="/register"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-5xl font-bold text-gray-900 mb-6">
                    Secure Cloud Storage<br />for Your Files
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    Store, organize, and access your files from anywhere. Built for MyGrowNet members with enterprise-grade security.
                </p>
                <div class="flex justify-center gap-4">
                    <Link
                        href="/register"
                        class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                    >
                        Start Free Trial
                    </Link>
                    <Link
                        href="/growbackup/dashboard"
                        class="px-8 py-3 bg-white text-blue-600 border-2 border-blue-600 rounded-lg hover:bg-blue-50 font-semibold"
                    >
                        View Dashboard
                    </Link>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">
                Everything You Need
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div
                    v-for="feature in features"
                    :key="feature.name"
                    class="text-center"
                >
                    <div class="flex justify-center mb-4">
                        <component
                            :is="feature.icon"
                            class="h-12 w-12 text-blue-600"
                        />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ feature.name }}
                    </h3>
                    <p class="text-gray-600">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="bg-gray-50 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">
                    Simple, Transparent Pricing
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        v-for="plan in plans"
                        :key="plan.name"
                        class="bg-white rounded-lg shadow-lg p-8 relative"
                        :class="{ 'ring-2 ring-blue-600': plan.popular }"
                    >
                        <div
                            v-if="plan.popular"
                            class="absolute top-0 right-0 bg-blue-600 text-white px-3 py-1 text-sm font-semibold rounded-bl-lg rounded-tr-lg"
                        >
                            Popular
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ plan.name }}
                        </h3>
                        <div class="mb-4">
                            <span class="text-4xl font-bold text-gray-900">{{ plan.price }}</span>
                            <span class="text-gray-600">/month</span>
                        </div>
                        <div class="mb-6">
                            <div class="text-lg font-semibold text-gray-900">{{ plan.storage }}</div>
                            <div class="text-sm text-gray-600">Max file: {{ plan.maxFile }}</div>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-start"
                            >
                                <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-600">{{ feature }}</span>
                            </li>
                        </ul>
                        <Link
                            href="/register"
                            class="block w-full text-center px-4 py-2 rounded-lg font-semibold"
                            :class="plan.popular ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200'"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Ready to Get Started?
            </h2>
            <p class="text-xl text-gray-600 mb-8">
                Join thousands of MyGrowNet members using GrowBackup
            </p>
            <Link
                href="/register"
                class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
            >
                Start Your Free Trial
            </Link>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p>&copy; 2026 MyGrowNet. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
