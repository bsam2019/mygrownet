<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { 
    DocumentTextIcon,
    WrenchScrewdriverIcon,
    ArrowLeftIcon,
    CheckIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline';

interface Props {
    isAdmin?: boolean;
    isManager?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isAdmin: false,
    isManager: false,
});

const tools = [
    {
        id: 'quick-invoice',
        name: 'Quick Invoice',
        description: 'Create professional invoices, quotations, receipts & delivery notes instantly.',
        icon: DocumentTextIcon,
        color: 'bg-amber-500',
        href: '/quick-invoice',
        badge: 'Free',
        features: [
            '5 professional templates',
            'Share via WhatsApp, Email, PDF',
            'Add logo & signature',
            'No account required'
        ]
    }
];
</script>

<template>
    <ClientLayout :is-admin="isAdmin" :is-manager="isManager">
        <Head title="Free Tools" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-6">
                <Link href="/dashboard" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4">
                    <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                    Back to Dashboard
                </Link>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <WrenchScrewdriverIcon class="w-5 h-5 text-amber-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Free Tools</h1>
                        <p class="text-gray-600">Helpful tools to run your business</p>
                    </div>
                </div>
            </div>

            <!-- Tools Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <a
                    v-for="tool in tools"
                    :key="tool.id"
                    :href="tool.href"
                    class="group bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all hover:-translate-y-1"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div 
                            class="w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110"
                            :class="tool.color"
                        >
                            <component :is="tool.icon" class="w-6 h-6 text-white" aria-hidden="true" />
                        </div>
                        <span 
                            v-if="tool.badge"
                            class="px-2 py-1 text-xs font-bold uppercase rounded"
                            :class="tool.badge === 'Free' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                        >
                            {{ tool.badge }}
                        </span>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ tool.name }}</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ tool.description }}</p>

                    <ul v-if="tool.features" class="space-y-2 mb-4">
                        <li 
                            v-for="feature in tool.features" 
                            :key="feature"
                            class="flex items-center gap-2 text-xs text-gray-500"
                        >
                            <CheckIcon class="w-4 h-4 text-green-500 flex-shrink-0" aria-hidden="true" />
                            {{ feature }}
                        </li>
                    </ul>

                    <div class="flex items-center text-sm font-medium text-amber-600 group-hover:text-amber-700">
                        Open Tool
                        <svg class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Coming Soon Placeholder -->
                <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 p-6 flex flex-col items-center justify-center text-center min-h-[280px]">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-4">
                        <SparklesIcon class="w-6 h-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-400 mb-2">More Coming Soon</h3>
                    <p class="text-sm text-gray-400">We're building more free tools to help your business grow</p>
                </div>
            </div>

            <!-- Info Section -->
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <WrenchScrewdriverIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-1">About Free Tools</h3>
                        <p class="text-sm text-blue-700">
                            These tools are available to everyone. Create professional business documents 
                            and share them instantly with your clients. No subscription required.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
