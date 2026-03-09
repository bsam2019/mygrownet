<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    ArrowDownTrayIcon,
    InformationCircleIcon,
    CheckCircleIcon,
    XCircleIcon,
    RocketLaunchIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
    };
    canExport: boolean;
}

const props = defineProps<Props>();

const exporting = ref(false);
const error = ref<string | null>(null);

const handleExport = () => {
    if (!props.canExport) {
        return;
    }
    
    exporting.value = true;
    error.value = null;
    
    router.post(
        route('growbuilder.sites.export.download', props.site.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                exporting.value = false;
            },
            onError: (errors) => {
                exporting.value = false;
                error.value = errors.export || 'Failed to export site';
            },
        }
    );
};

const goBack = () => {
    router.visit(route('growbuilder.sites.settings', props.site.id));
};

</script>

<template>
    <Head :title="`Export ${site.name}`" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <button
                    @click="goBack"
                    class="text-sm text-gray-600 hover:text-gray-900 mb-4"
                >
                    ← Back to Settings
                </button>
                <h1 class="text-3xl font-bold text-gray-900">Export Site</h1>
                <p class="mt-2 text-gray-600">
                    Download your site as a static HTML package
                </p>
            </div>

            <!-- Upgrade Notice (if can't export) -->
            <div
                v-if="!canExport"
                class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6"
            >
                <div class="flex items-start">
                    <InformationCircleIcon class="h-6 w-6 text-blue-600 mt-0.5 mr-3" />
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">
                            Upgrade Required
                        </h3>
                        <p class="text-blue-800 mb-4">
                            Static export is available for Business and Agency tier subscribers.
                            Upgrade your subscription to download your site as a standalone package.
                        </p>
                        <button
                            @click="router.visit(route('growbuilder.subscription.index'))"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
                        >
                            View Plans
                        </button>
                    </div>
                </div>
            </div>

            <!-- Export Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-start mb-6">
                    <ArrowDownTrayIcon class="h-8 w-8 text-blue-600 mr-4" />
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">
                            Static HTML Export
                        </h2>
                        <p class="text-gray-600">
                            Download your site as a complete, self-contained package that can be
                            hosted anywhere.
                        </p>
                    </div>
                </div>

                <!-- Error Message -->
                <div
                    v-if="error"
                    class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-start"
                >
                    <XCircleIcon class="h-5 w-5 text-red-600 mt-0.5 mr-3" />
                    <p class="text-red-800">{{ error }}</p>
                </div>

                <!-- Export Button -->
                <button
                    @click="handleExport"
                    :disabled="!canExport || exporting"
                    class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                >
                    <ArrowDownTrayIcon v-if="!exporting" class="h-5 w-5 mr-2" />
                    <svg
                        v-else
                        class="animate-spin h-5 w-5 mr-2"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    {{ exporting ? 'Generating Export...' : 'Download Site Export' }}
                </button>
            </div>

            <!-- What's Included -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    What's Included
                </h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <CheckCircleIcon class="h-5 w-5 text-green-600 mt-0.5 mr-3" />
                        <div>
                            <p class="font-medium text-gray-900">All Pages</p>
                            <p class="text-sm text-gray-600">
                                Every page converted to static HTML
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <CheckCircleIcon class="h-5 w-5 text-green-600 mt-0.5 mr-3" />
                        <div>
                            <p class="font-medium text-gray-900">Compiled Styles</p>
                            <p class="text-sm text-gray-600">
                                Your theme and custom CSS in one file
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <CheckCircleIcon class="h-5 w-5 text-green-600 mt-0.5 mr-3" />
                        <div>
                            <p class="font-medium text-gray-900">All Media Files</p>
                            <p class="text-sm text-gray-600">
                                Images, logos, and other assets
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <CheckCircleIcon class="h-5 w-5 text-green-600 mt-0.5 mr-3" />
                        <div>
                            <p class="font-medium text-gray-900">JavaScript</p>
                            <p class="text-sm text-gray-600">
                                Navigation and interactive features
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <CheckCircleIcon class="h-5 w-5 text-green-600 mt-0.5 mr-3" />
                        <div>
                            <p class="font-medium text-gray-900">README Guide</p>
                            <p class="text-sm text-gray-600">
                                Instructions for hosting and customization
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hosting Options -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-start mb-4">
                    <RocketLaunchIcon class="h-6 w-6 text-blue-600 mr-3" />
                    <h3 class="text-lg font-semibold text-gray-900">
                        Where to Host
                    </h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Your exported site can be hosted on any web hosting platform:
                </p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-1">Netlify</h4>
                        <p class="text-sm text-gray-600">
                            Free hosting with drag-and-drop deployment
                        </p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-1">Vercel</h4>
                        <p class="text-sm text-gray-600">
                            Fast, free hosting with CLI deployment
                        </p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-1">GitHub Pages</h4>
                        <p class="text-sm text-gray-600">
                            Free hosting directly from your repository
                        </p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-1">Traditional Hosting</h4>
                        <p class="text-sm text-gray-600">
                            cPanel, FTP, or any web hosting provider
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
