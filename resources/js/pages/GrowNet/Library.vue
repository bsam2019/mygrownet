<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { BookOpenIcon, ExternalLinkIcon, ClockIcon, TrendingUpIcon } from 'lucide-vue-next';

interface Resource {
    id: number;
    title: string;
    description: string | null;
    type: string;
    category: string;
    resource_url: string;
    author: string | null;
    duration_formatted: string | null;
    difficulty_label: string;
    type_label: string;
    category_label: string;
    view_count: number;
}

interface Props {
    resources: Record<string, Resource[]>;
    featured: Resource[];
    recentlyAccessed: Resource[];
    stats: {
        total_resources: number;
        accessed_count: number;
        completed_count: number;
    };
    filters: {
        category: string | null;
        type: string | null;
        search: string | null;
    };
}

const props = defineProps<Props>();

const getTypeIcon = (type: string) => {
    const icons: Record<string, string> = {
        pdf: '📄',
        video: '🎥',
        article: '📰',
        course: '🎓',
        tool: '🛠️',
        template: '📋',
    };
    return icons[type] || '📄';
};

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        business: '💼',
        marketing: '📈',
        finance: '💰',
        leadership: '👔',
        personal_development: '🌱',
        network_building: '🤝',
    };
    return icons[category] || '💼';
};
</script>

<template>
    <Head title="Resource Library" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Resource Library</h1>
                    <p class="mt-2 text-gray-600">Curated collection of free business resources to help you succeed</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <BookOpenIcon class="w-8 h-8 text-blue-600 mr-3" />
                            <div>
                                <p class="text-sm text-gray-600">Total Resources</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total_resources }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <TrendingUpIcon class="w-8 h-8 text-green-600 mr-3" />
                            <div>
                                <p class="text-sm text-gray-600">Accessed</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.accessed_count }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <ClockIcon class="w-8 h-8 text-purple-600 mr-3" />
                            <div>
                                <p class="text-sm text-gray-600">Completed</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.completed_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Resources -->
                <div v-if="featured.length > 0" class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">⭐ Featured Resources</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a
                            v-for="resource in featured"
                            :key="resource.id"
                            :href="resource.resource_url"
                            target="_blank"
                            class="bg-gradient-to-br from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4 hover:shadow-lg transition-shadow"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-3xl">{{ getTypeIcon(resource.type) }}</span>
                                <ExternalLinkIcon class="w-4 h-4 text-gray-400" />
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-1">{{ resource.title }}</h3>
                            <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ resource.description }}</p>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ resource.type_label }}</span>
                                <span v-if="resource.duration_formatted">• {{ resource.duration_formatted }}</span>
                                <span>• {{ resource.difficulty_label }}</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Resources by Category -->
                <div class="space-y-6">
                    <div v-for="(categoryResources, category) in resources" :key="category" class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">{{ getCategoryIcon(category) }}</span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ categoryResources[0]?.category_label }}</h3>
                                    <p class="text-sm text-gray-600">{{ categoryResources.length }} resources</p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200">
                            <a
                                v-for="resource in categoryResources"
                                :key="resource.id"
                                :href="resource.resource_url"
                                target="_blank"
                                class="px-6 py-4 hover:bg-gray-50 flex items-start justify-between group"
                            >
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xl">{{ getTypeIcon(resource.type) }}</span>
                                        <h4 class="font-medium text-gray-900 group-hover:text-blue-600">{{ resource.title }}</h4>
                                    </div>
                                    <p v-if="resource.description" class="text-sm text-gray-600 mb-2">{{ resource.description }}</p>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span>{{ resource.type_label }}</span>
                                        <span v-if="resource.author">• {{ resource.author }}</span>
                                        <span v-if="resource.duration_formatted">• {{ resource.duration_formatted }}</span>
                                        <span>• {{ resource.difficulty_label }}</span>
                                    </div>
                                </div>
                                <ExternalLinkIcon class="w-5 h-5 text-gray-400 group-hover:text-blue-600 ml-4 flex-shrink-0" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
