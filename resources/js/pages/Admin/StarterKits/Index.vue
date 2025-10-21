<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { PackageIcon, UsersIcon, TrendingUpIcon } from 'lucide-vue-next';

interface Package {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: string;
    billing_cycle: string;
    features: string[];
}

interface Assignment {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
        phone?: string;
    };
    package: Package;
    amount: string;
    status: string;
    start_date: string;
    created_at: string;
}

interface Props {
    starterKit: Package | null;
    assignments: {
        data: Assignment[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        total_assigned: number;
        total_members: number;
        assignment_rate: number;
    };
}

const props = defineProps<Props>();

const formatCurrency = (amount: string | number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(typeof amount === 'string' ? parseFloat(amount) : amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        expired: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Starter Kit Management" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Starter Kit Management</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Monitor and manage starter kit assignments for new members
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <PackageIcon class="h-8 w-8 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Assigned</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total_assigned }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <UsersIcon class="h-8 w-8 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Members</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total_members }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <TrendingUpIcon class="h-8 w-8 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Assignment Rate</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.assignment_rate }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Starter Kit Details -->
                <div v-if="starterKit" class="bg-white rounded-lg shadow mb-6 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Starter Kit Package</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ starterKit.name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ starterKit.description }}</p>
                            <div class="mt-4">
                                <span class="text-2xl font-bold text-blue-600">
                                    {{ formatCurrency(starterKit.price) }}
                                </span>
                                <span class="text-sm text-gray-600 ml-2">{{ starterKit.billing_cycle }}</span>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Features:</h4>
                            <ul class="space-y-1">
                                <li v-for="(feature, index) in starterKit.features" :key="index" class="text-sm text-gray-600 flex items-start">
                                    <span class="text-green-600 mr-2">✓</span>
                                    {{ feature }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Assignments Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Assignments</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Member
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Start Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Assigned
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="assignment in assignments.data" :key="assignment.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ assignment.user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ assignment.user.email || assignment.user.phone }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(assignment.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusColor(assignment.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ assignment.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(assignment.start_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(assignment.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="assignments.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ (assignments.current_page - 1) * assignments.per_page + 1 }} to 
                            {{ Math.min(assignments.current_page * assignments.per_page, assignments.total) }} of 
                            {{ assignments.total }} results
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-if="assignments.current_page > 1"
                                :href="`/admin/starter-kits?page=${assignments.current_page - 1}`"
                                class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="assignments.current_page < assignments.last_page"
                                :href="`/admin/starter-kits?page=${assignments.current_page + 1}`"
                                class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
