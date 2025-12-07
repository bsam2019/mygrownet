<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    SparklesIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface Template {
    id: number;
    name: string;
    description: string | null;
    category: string;
    industry: string | null;
    preview_path: string | null;
    is_premium: boolean;
    is_active: boolean;
    usage_count: number;
    sort_order: number;
}

interface Props {
    templates: {
        data: Template[];
        links: any;
        current_page: number;
        last_page: number;
    };
    categories: string[];
    filters: {
        search?: string;
        category?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');

const applyFilters = () => {
    router.get('/admin/bizboost/templates', {
        search: search.value || undefined,
        category: selectedCategory.value || undefined,
    }, { preserveState: true });
};

const deleteTemplate = (id: number, name: string) => {
    if (confirm(`Delete template "${name}"? This cannot be undone.`)) {
        router.delete(`/admin/bizboost/templates/${id}`);
    }
};

const toggleActive = (id: number) => {
    router.post(`/admin/bizboost/templates/${id}/toggle-active`);
};
</script>

<template>
    <Head title="BizBoost Templates - Admin" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">BizBoost Templates</h1>
                        <p class="mt-1 text-sm text-gray-600">Manage content templates for BizBoost users</p>
                    </div>
                    <Link
                        href="/admin/bizboost/templates/create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Add Template
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search templates..."
                                class="w-full pl-10 rounded-lg border-gray-300"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <select
                            v-model="selectedCategory"
                            class="rounded-lg border-gray-300"
                            @change="applyFilters"
                        >
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                        >
                            Filter
                        </button>
                    </div>
                </div>

                <!-- Templates Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Industry</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Premium</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Uses</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="template in templates.data" :key="template.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                                            <img
                                                v-if="template.preview_path"
                                                :src="`/storage/${template.preview_path}`"
                                                :alt="template.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <SparklesIcon v-else class="h-6 w-6 text-gray-400" aria-hidden="true" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ template.name }}</div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ template.description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ template.category }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ template.industry || '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            template.is_premium ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'
                                        ]"
                                    >
                                        {{ template.is_premium ? 'Premium' : 'Free' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="toggleActive(template.id)"
                                        :class="[
                                            'inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full',
                                            template.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                        ]"
                                    >
                                        <CheckCircleIcon v-if="template.is_active" class="h-4 w-4" aria-hidden="true" />
                                        <XCircleIcon v-else class="h-4 w-4" aria-hidden="true" />
                                        {{ template.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500">{{ template.usage_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="`/admin/bizboost/templates/${template.id}/edit`"
                                            class="p-2 text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg"
                                            aria-label="Edit template"
                                        >
                                            <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                        <button
                                            @click="deleteTemplate(template.id, template.name)"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                            aria-label="Delete template"
                                        >
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="templates.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <SparklesIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                                    <p class="mt-2">No templates found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
