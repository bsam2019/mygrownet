<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { FolderIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    parent_id?: number;
    parent?: { name: string } | null;
    sort_order: number;
    is_active: boolean;
    products_count: number;
}

interface Props {
    categories: {
        data: Category[];
        meta: any;
    };
}

const props = defineProps<Props>();

const deleteCategory = (category: Category) => {
    if (!confirm(`Delete category "${category.name}"?`)) return;
    router.delete(route('admin.growmart.categories.destroy', category.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="GrowMart Categories - Admin" />

    <AdminLayout title="GrowMart Categories">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">Manage grocery product categories</p>
            <Link :href="route('admin.growmart.categories.create')" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <PlusIcon class="h-5 w-5" />
                Add Category
            </Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Products</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sort</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <FolderIcon class="h-6 w-6 text-gray-400" />
                                    <div>
                                        <p class="font-medium text-gray-900">{{ category.name }}</p>
                                        <p v-if="category.description" class="text-sm text-gray-500">{{ category.description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ category.parent?.name || '—' }}</td>
                            <td class="px-6 py-4 text-gray-900">{{ category.products_count }}</td>
                            <td class="px-6 py-4 text-gray-900">{{ category.sort_order }}</td>
                            <td class="px-6 py-4">
                                <span :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.growmart.categories.edit', category.id)" class="text-emerald-600 hover:text-emerald-700">
                                        <PencilIcon class="h-4 w-4" />
                                    </Link>
                                    <button @click="deleteCategory(category)" class="text-red-600 hover:text-red-700">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new category</p>
                                <Link :href="route('admin.growmart.categories.create')" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                                    <PlusIcon class="h-5 w-5" />
                                    Add Category
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
