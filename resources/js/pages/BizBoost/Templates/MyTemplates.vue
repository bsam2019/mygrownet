<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, PlusIcon, TrashIcon, SparklesIcon } from '@heroicons/vue/24/outline';

interface CustomTemplate {
    id: number;
    name: string;
    description: string | null;
    category: string;
    created_at: string;
    base_template: { name: string } | null;
}

interface Props {
    templates: {
        data: CustomTemplate[];
        current_page: number;
        last_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

const deleteTemplate = (id: number) => {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(route('bizboost.templates.delete-custom', id));
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head title="My Templates - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.templates.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Templates
                    </Link>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Templates</h1>
                        <p class="mt-1 text-sm text-gray-600">Your saved custom templates</p>
                    </div>
                </div>

                <div v-if="templates.data.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                    <SparklesIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No custom templates</h3>
                    <p class="mt-1 text-sm text-gray-500">Save templates while creating posts to reuse them later.</p>
                    <div class="mt-6">
                        <Link :href="route('bizboost.templates.index')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Browse Templates
                        </Link>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="template in templates.data"
                        :key="template.id"
                        class="bg-white rounded-lg shadow overflow-hidden"
                    >
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ template.name }}</h3>
                                    <p v-if="template.description" class="text-sm text-gray-500 mt-1">{{ template.description }}</p>
                                </div>
                                <button
                                    @click="deleteTemplate(template.id)"
                                    class="p-1 text-gray-400 hover:text-red-600"
                                    aria-label="Delete template"
                                >
                                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                                <span class="px-2 py-0.5 bg-gray-100 rounded">{{ template.category }}</span>
                                <span>{{ formatDate(template.created_at) }}</span>
                            </div>
                            <div v-if="template.base_template" class="mt-2 text-xs text-gray-400">
                                Based on: {{ template.base_template.name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
