<template>
    <AdminLayout title="Department Management">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-800">Department Management</h2>
                <Link
                    v-if="$page.props.auth.user.permissions.includes('create-departments')"
                    :href="route('admin.departments.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Add Department
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Departments</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_departments }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active Departments</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.active_departments }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <UsersIcon class="w-6 h-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Employees</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_employees }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departments Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="department in departments.data" :key="department.id" class="bg-white rounded-lg shadow-sm border p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-gray-900">{{ department.name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ department.description || 'No description available' }}</p>
                                
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <UsersIcon class="w-4 h-4 mr-2" />
                                        {{ department.employees_count }} employees
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <BriefcaseIcon class="w-4 h-4 mr-2" />
                                        {{ department.positions_count }} positions
                                    </div>
                                    <div v-if="department.head" class="flex items-center text-sm text-gray-600">
                                        <UserIcon class="w-4 h-4 mr-2" />
                                        Head: {{ department.head.first_name }} {{ department.head.last_name }}
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800': department.is_active,
                                            'bg-red-100 text-red-800': !department.is_active
                                        }"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ department.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 ml-4">
                                <Link
                                    :href="route('departments.show', department.id)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    <EyeIcon class="w-4 h-4" />
                                </Link>
                                <Link
                                    v-if="$page.props.auth.user.permissions.includes('edit-departments')"
                                    :href="route('admin.departments.edit', department.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="departments.last_page > 1" class="mt-8">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="departments.prev_page_url"
                                :href="departments.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="departments.next_page_url"
                                :href="departments.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ departments.from }} to {{ departments.to }} of {{ departments.total }} results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-if="departments.prev_page_url"
                                        :href="departments.prev_page_url"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                    <Link
                                        v-if="departments.next_page_url"
                                        :href="departments.next_page_url"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        Next
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import {
    BuildingOfficeIcon,
    CheckCircleIcon,
    UsersIcon,
    BriefcaseIcon,
    UserIcon,
    PlusIcon,
    EyeIcon,
    PencilIcon
} from '@heroicons/vue/24/outline';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    departments: Object,
    stats: Object,
});
</script>