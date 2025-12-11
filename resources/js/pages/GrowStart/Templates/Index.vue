<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    DocumentTextIcon,
    ArrowDownTrayIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    LockClosedIcon
} from '@heroicons/vue/24/outline';
import type { Template, TemplateCategory } from '@/types/growstart';

interface Props {
    templates: Template[];
    categories: { value: TemplateCategory; label: string }[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedCategory = ref<TemplateCategory | 'all'>('all');

const filteredTemplates = computed(() => {
    return props.templates.filter(template => {
        const matchesSearch = template.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            template.description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategory.value === 'all' || template.category === selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
});

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        business_plan: '📋',
        financial: '💰',
        marketing: '📢',
        legal: '⚖️',
        operations: '⚙️'
    };
    return icons[category] || '📄';
};

const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
        business_plan: 'bg-blue-100 text-blue-700',
        financial: 'bg-emerald-100 text-emerald-700',
        marketing: 'bg-purple-100 text-purple-700',
        legal: 'bg-amber-100 text-amber-700',
        operations: 'bg-gray-100 text-gray-700'
    };
    return colors[category] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="GrowStart - Templates" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Document Templates</h1>
                    <p class="text-gray-600">Download ready-to-use templates for your business</p>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search templates..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <!-- Category Filter -->
                        <div class="flex items-center gap-2">
                            <FunnelIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            <select
                                v-model="selectedCategory"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="all">All Categories</option>
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="template in filteredTemplates"
                        :key="template.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:border-blue-200 transition"
                    >
                        <div class="p-5">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-gray-100 rounded-lg">
                                    <DocumentTextIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium text-gray-900 truncate">{{ template.name }}</h3>
                                        <LockClosedIcon 
                                            v-if="template.is_premium"
                                            class="h-4 w-4 text-amber-500" 
                                            aria-hidden="true" 
                                        />
                                    </div>
                                    <span 
                                        :class="[
                                            'inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded',
                                            getCategoryColor(template.category)
                                        ]"
                                    >
                                        {{ getCategoryIcon(template.category) }} {{ template.category.replace('_', ' ') }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-gray-500 line-clamp-2">
                                {{ template.description }}
                            </p>
                        </div>
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500">
                                {{ template.download_count }} downloads
                            </span>
                            <a
                                :href="route('growstart.templates.download', template.id)"
                                :class="[
                                    'inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg transition',
                                    template.is_premium 
                                        ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' 
                                        : 'bg-blue-600 text-white hover:bg-blue-700'
                                ]"
                            >
                                <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                                {{ template.is_premium ? 'Premium' : 'Download' }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredTemplates.length === 0" class="text-center py-12">
                    <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No templates found</h3>
                    <p class="mt-2 text-gray-500">Try adjusting your search or filter</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
