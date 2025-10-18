<template>
    <AdminLayout title="Department Management">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header with Add Button -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Department Management</h1>
                    <Link
                        :href="route('admin.departments.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Add Department
                    </Link>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <Building2Icon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Departments</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ meta.total_count }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ meta.active_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search departments..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @input="handleSearch"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="activeOnly"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option :value="true">Active Only</option>
                                <option :value="false">All Departments</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">View</label>
                            <select
                                v-model="hierarchical"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option :value="false">List View</option>
                                <option :value="true">Hierarchical View</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Departments List -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Departments</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Head
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employees
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Positions
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="department in departments.data || departments" :key="department.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ department.name }}
                                                </div>
                                                <div v-if="department.description" class="text-sm text-gray-500">
                                                    {{ department.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="department.head_employee" class="text-sm text-gray-900">
                                            {{ department.head_employee.first_name }} {{ department.head_employee.last_name }}
                                        </div>
                                        <div v-else class="text-sm text-gray-500">No head assigned</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ department.employees_count || 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ department.positions_count || 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                department.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ department.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.departments.show', department.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            v-if="$page.props.auth.user.permissions?.includes('edit-departments')"
                                            :href="route('admin.departments.edit', department.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="departments?.links" class="mt-6">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="departments?.prev_page_url"
                                :href="departments.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="departments?.next_page_url"
                                :href="departments.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ departments?.from || 0 }} to {{ departments?.to || 0 }} of {{ departments?.total || 0 }} results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in departments?.links || []"
                                        :key="link.label"
                                        :href="link.url || '#'"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            link.active
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            link.url ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'
                                        ]"
                                        v-html="link.label"
                                    />
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    BuildingOfficeIcon,
    UsersIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    EyeIcon,
    PencilIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

interface Department {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    head_employee?: {
        first_name: string;
        last_name: string;
    };
    employees_count: number;
    positions_count: number;
}

interface Props {
    departments: {
        data: Department[];
        links?: any[];
        from?: number;
        to?: number;
        total?: number;
        prev_page_url?: string;
        next_page_url?: string;
    } | Department[];
    filters: {
        search?: string;
        active_only: boolean;
        hierarchical: boolean;
    };
    meta: {
        total_count: number;
        active_count: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const activeOnly = ref(props.filters.active_only);
const hierarchical = ref(props.filters.hierarchical);

const handleSearch = () => {
    router.get(route('admin.departments.index'), {
        search: searchQuery.value,
        active_only: activeOnly.value,
        hierarchical: hierarchical.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.departments.index'), {
        search: searchQuery.value,
        active_only: activeOnly.value,
        hierarchical: hierarchical.value,
    }, {
        preserveState: true,
        replace: true,
    });
};
</script>
