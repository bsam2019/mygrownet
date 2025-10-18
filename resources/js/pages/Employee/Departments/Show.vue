<template>
    <AdminLayout title="Department Details">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ department.name }}</h1>
                        <p v-if="department.description" class="text-gray-600 mt-1">{{ department.description }}</p>
                    </div>
                    <Link
                        :href="route('admin.departments.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to Departments
                    </Link>
                </div>

                <!-- Department Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <UsersIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Employees</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_employees }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <UserIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active Employees</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ statistics.active_employees }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <BriefcaseIcon class="w-6 h-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Positions</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_positions }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <BuildingOfficeIcon class="w-6 h-6 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Sub-Departments</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ statistics.child_departments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Department Details -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Department Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department Head</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ department.head_employee ? `${department.head_employee.first_name} ${department.head_employee.last_name}` : 'No department head assigned' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Parent Department</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ department.parent_department ? department.parent_department.name : 'No parent department' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ department.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Created</label>
                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(department.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ArrowLeftIcon, UsersIcon, UserIcon, BriefcaseIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import { formatDate } from '@/utils/formatting.ts';

interface Department {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    created_at: string;
    head_employee?: {
        first_name: string;
        last_name: string;
    };
    parent_department?: {
        name: string;
    };
}

interface Statistics {
    total_employees: number;
    active_employees: number;
    total_positions: number;
    active_positions: number;
    child_departments: number;
}

interface Props {
    department: Department;
    statistics: Statistics;
}

const props = defineProps<Props>();
</script>
