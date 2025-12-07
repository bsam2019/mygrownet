<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { SparklesIcon, LockClosedIcon, MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline';

interface Template {
    id: number;
    name: string;
    description: string;
    category: string;
    industry: string | null;
    preview_path: string | null;
    is_premium: boolean;
    usage_count: number;
    template_data: Record<string, unknown>;
}

interface Props {
    templates: {
        data: Template[];
        current_page: number;
        last_page: number;
        total: number;
    };
    categories: string[];
    industries: Record<string, unknown>;
    canAccessPremium: boolean;
    filters: {
        category?: string;
        industry?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedIndustry = ref(props.filters.industry || '');

const applyFilters = () => {
    router.get('/bizboost/templates', {
        search: searchQuery.value,
        category: selectedCategory.value,
        industry: selectedIndustry.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const useTemplate = (template: Template) => {
    if (template.is_premium && !props.canAccessPremium) {
        router.visit('/bizboost/upgrade');
        return;
    }
    // Navigate directly to create post with template_id
    router.visit(`/bizboost/posts/create?template_id=${template.id}`);
};
</script>

<template>
    <Head title="Templates - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Content Templates</h1>
                        <p class="mt-1 text-sm text-gray-600">Professional templates for your social media posts</p>
                    </div>
                    <Link
                        href="/bizboost/templates/my"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                    >
                        My Templates
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <div class="relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search templates..."
                                    class="w-full pl-10 rounded-md border-gray-300"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select v-model="selectedCategory" class="w-full rounded-md border-gray-300" @change="applyFilters">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                            <select v-model="selectedIndustry" class="w-full rounded-md border-gray-300" @change="applyFilters">
                                <option value="">All Industries</option>
                                <option v-for="(ind, key) in industries" :key="key" :value="key">{{ key }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button @click="applyFilters" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <FunnelIcon class="inline h-5 w-5 mr-1" aria-hidden="true" />
                                Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div v-if="templates.data.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                    <SparklesIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No templates found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="template in templates.data"
                        :key="template.id"
                        class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow group"
                    >
                        <!-- Preview (clickable) -->
                        <Link :href="`/bizboost/templates/${template.id}`" class="block">
                            <div class="aspect-square bg-gray-100 relative">
                                <img
                                    v-if="template.preview_path"
                                    :src="`/storage/${template.preview_path}`"
                                    :alt="template.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <SparklesIcon class="h-16 w-16 text-gray-300" aria-hidden="true" />
                                </div>
                                <div v-if="template.is_premium" class="absolute top-2 right-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-600 text-white text-xs rounded-full">
                                        <LockClosedIcon class="h-3 w-3" aria-hidden="true" />
                                        Premium
                                    </span>
                                </div>
                                <!-- Hover overlay -->
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <span class="opacity-0 group-hover:opacity-100 transition-opacity text-white font-medium bg-black/50 px-3 py-1 rounded-full text-sm">
                                        View Details
                                    </span>
                                </div>
                            </div>
                        </Link>
                        
                        <!-- Info -->
                        <div class="p-4">
                            <Link :href="`/bizboost/templates/${template.id}`" class="block">
                                <h3 class="font-semibold text-gray-900 hover:text-blue-600">{{ template.name }}</h3>
                            </Link>
                            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ template.description }}</p>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-xs text-gray-400">{{ template.usage_count }} uses</span>
                                <button
                                    @click.stop="useTemplate(template)"
                                    :class="[
                                        'px-3 py-1 text-sm rounded-md',
                                        template.is_premium && !canAccessPremium
                                            ? 'bg-purple-100 text-purple-700'
                                            : 'bg-blue-600 text-white hover:bg-blue-700'
                                    ]"
                                >
                                    {{ template.is_premium && !canAccessPremium ? 'Upgrade' : 'Use' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
