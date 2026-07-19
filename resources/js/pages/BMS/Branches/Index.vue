<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { BuildingOffice2Icon, PlusIcon, MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';

interface Branch {
    id: number;
    branch_code: string;
    branch_name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    province: string | null;
    is_head_office: boolean;
    is_active: boolean;
    manager: { id: number; user: { name: string } } | null;
    departments_count: number;
}

interface Props {
    branches: { data: Branch[] };
    filters: { search?: string; status?: string };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('cms.branches.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    router.get(route('cms.branches.index'), {}, { preserveState: true });
};

const confirmDelete = (branch: Branch) => {
    if (confirm(`Delete branch "${branch.branch_name}"?`)) {
        router.delete(route('cms.branches.destroy', branch.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Deleted', 'Branch deleted'),
            onError: (e) => toast.error('Error', e?.response?.data?.message || 'Could not delete branch'),
        });
    }
};
</script>

<template>
    <Head title="Branches" />

    <CMSLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Branches</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage business locations and branches</p>
                </div>
                <Link :href="route('cms.branches.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <PlusIcon class="h-5 w-5" />
                    New Branch
                </Link>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative flex-1 max-w-xs">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <input v-model="search" type="text" placeholder="Search branches..." @keyup.enter="applyFilters"
                                class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <select v-model="statusFilter" @change="applyFilters"
                            class="border border-gray-300 rounded-lg text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button @click="applyFilters" class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Filter
                        </button>
                        <button @click="clearFilters" class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 bg-white hover:bg-gray-50">
                            Clear
                        </button>
                    </div>
                </div>

                <div v-if="branches.data.length === 0" class="p-12 text-center">
                    <BuildingOffice2Icon class="mx-auto h-12 w-12 text-gray-300" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No branches found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new branch.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Depts</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="branch in branches.data" :key="branch.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <span class="inline-flex items-center gap-1">
                                        <BuildingOffice2Icon class="h-4 w-4 text-gray-400" />
                                        {{ branch.branch_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ branch.branch_name }}
                                    <span v-if="branch.is_head_office" class="ml-2 px-1.5 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">HQ</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ [branch.city, branch.province].filter(Boolean).join(', ') || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ branch.manager?.user?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                    {{ branch.departments_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full" :class="branch.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">
                                        {{ branch.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('cms.branches.edit', branch.id)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</Link>
                                    <button @click="confirmDelete(branch)" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
