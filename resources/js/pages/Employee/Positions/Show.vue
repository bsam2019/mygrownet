<template>
    <AdminLayout title="Position Details">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ position.title }}</h1>
                        <p v-if="position.description" class="text-gray-600 mt-1">{{ position.description }}</p>
                    </div>
                    <Link
                        :href="route('admin.positions.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to Positions
                    </Link>
                </div>

                <!-- Position Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
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
                                <CurrencyDollarIcon class="w-6 h-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Avg. Salary</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ formatKwacha(statistics.average_salary) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Position Details -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Position Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <p class="mt-1 text-sm text-gray-900">{{ position.department?.name || 'No department assigned' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Salary Range</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatKwacha(position.min_salary) }} - {{ formatKwacha(position.max_salary) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Commission Eligible</label>
                            <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                  :class="position.commission_eligible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                {{ position.commission_eligible ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ position.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Created</label>
                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(position.created_at) }}</p>
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
import { ArrowLeftIcon, UsersIcon, UserIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';
import { formatDate, formatKwacha } from '@/utils/formatting.ts';

interface Position {
    id: number;
    title: string;
    description?: string;
    min_salary: number;
    max_salary: number;
    commission_eligible: boolean;
    is_active: boolean;
    created_at: string;
    department?: {
        name: string;
    };
}

interface Statistics {
    total_employees: number;
    active_employees: number;
    average_salary: number;
}

interface Props {
    position: Position;
    statistics: Statistics;
}

const props = defineProps<Props>();
</script>
