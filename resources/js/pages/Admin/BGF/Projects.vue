<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import CustomAppSidebarLayout from '@/layouts/admin/CustomAppSidebarLayout.vue';
import { Briefcase } from 'lucide-vue-next';
import { ref } from 'vue';

interface Project {
    id: number;
    project_number: string;
    approved_amount: number;
    total_disbursed: number;
    total_repaid: number;
    status: string;
    expected_completion_date: string;
    user: {
        name: string;
    };
    application: {
        business_name: string;
    };
}

const props = defineProps<{
    projects: {
        data: Project[];
    };
    filters: {
        status?: string;
    };
}>();

const status = ref(props.filters.status || '');

const filterProjects = () => {
    router.get(route('admin.bgf.projects'), {
        status: status.value,
    }, {
        preserveState: true,
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending_contract: 'bg-yellow-100 text-yellow-800',
        active: 'bg-emerald-100 text-emerald-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        defaulted: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="BGF Projects" />

    <CustomAppSidebarLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">BGF Projects</h1>
                <p class="mt-2 text-gray-600">Manage funded projects</p>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                        v-model="status"
                        @change="filterProjects"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending_contract">Pending Contract</option>
                        <option value="active">Active</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="defaulted">Defaulted</option>
                    </select>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disbursed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Repaid</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="projects.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No projects found
                            </td>
                        </tr>
                        <tr
                            v-for="project in projects.data"
                            :key="project.id"
                            class="hover:bg-gray-50 cursor-pointer"
                            @click="router.visit(route('admin.bgf.projects.show', project.id))"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ project.project_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ project.application.business_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ project.user.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency(project.approved_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency(project.total_disbursed) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency(project.total_repaid) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(project.status)]">
                                    {{ project.status.replace('_', ' ') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CustomAppSidebarLayout>
</template>
