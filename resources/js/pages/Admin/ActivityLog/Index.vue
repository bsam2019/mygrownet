<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Activity Log</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700">Action Type</label>
                            <select v-model="filters.action" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">All Actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700">Date Range</label>
                            <select v-model="filters.dateRange" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Activity List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="activity in activities.data" :key="activity.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ activity.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getActionClass(activity.action)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ activity.action }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ activity.description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(activity.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="activities.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    activities: Object
});

const filters = ref({
    action: '',
    dateRange: ''
});

watch(filters, (value) => {
    router.get(route('admin.activity.index'), value, {
        preserveState: true,
        preserveScroll: true
    });
}, { deep: true });

const getActionClass = (action) => {
    const classes = {
        created: 'bg-green-100 text-green-800',
        updated: 'bg-blue-100 text-blue-800',
        deleted: 'bg-red-100 text-red-800'
    };
    return classes[action] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
