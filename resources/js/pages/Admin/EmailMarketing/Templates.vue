<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Template {
    id: number;
    name: string;
    category: string;
    subject: string;
    html_content: string;
    created_at: string;
}

interface Props {
    templates: {
        data: Template[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

const categoryColors: Record<string, string> = {
    onboarding: 'bg-blue-100 text-blue-800',
    engagement: 'bg-green-100 text-green-800',
    reactivation: 'bg-yellow-100 text-yellow-800',
    upgrade: 'bg-purple-100 text-purple-800',
    system: 'bg-gray-100 text-gray-800',
};
</script>

<template>
    <Head title="Email Templates" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Email Templates</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage email templates for your campaigns
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a
                            :href="route('admin.email-templates.create')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                        >
                            Create Template
                        </a>
                        <a
                            :href="route('admin.email-campaigns.index')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Back to Campaigns
                        </a>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="template in templates.data"
                        :key="template.id"
                        class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ template.name }}
                                </h3>
                                <span
                                    :class="categoryColors[template.category] || categoryColors.system"
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ template.category }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">
                                <strong>Subject:</strong> {{ template.subject }}
                            </p>
                            <p v-if="template.html_content" class="text-sm text-gray-500 line-clamp-3">
                                {{ template.html_content.replace(/<[^>]*>/g, '').substring(0, 150) }}...
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="templates.data.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No templates</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Email templates will appear here once created.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
