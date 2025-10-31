<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CustomAppSidebarLayout from '@/layouts/admin/CustomAppSidebarLayout.vue';
import { TrendingUp, FileText, Briefcase, DollarSign, Activity, AlertCircle } from 'lucide-vue-next';

interface Stats {
    total_applications: number;
    pending_review: number;
    approved: number;
    active_projects: number;
    total_funded: number;
    total_disbursed: number;
    total_repaid: number;
    pending_disbursements: number;
}

interface Application {
    id: number;
    reference_number: string;
    business_name: string;
    amount_requested: number;
    status: string;
    created_at: string;
    user: {
        name: string;
    };
}

interface Project {
    id: number;
    project_number: string;
    approved_amount: number;
    total_disbursed: number;
    status: string;
    user: {
        name: string;
    };
}

const props = defineProps<{
    stats: Stats;
    recentApplications: Application[];
    activeProjects: Project[];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        submitted: 'bg-blue-100 text-blue-800',
        under_review: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        active: 'bg-emerald-100 text-emerald-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Business Growth Fund Dashboard" />

    <CustomAppSidebarLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Business Growth Fund</h1>
                <p class="mt-2 text-gray-600">Manage applications, projects, and funding</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Applications -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Applications</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_applications }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <FileText class="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <!-- Pending Review -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending Review</p>
                            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ stats.pending_review }}</p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <AlertCircle class="h-6 w-6 text-yellow-600" />
                        </div>
                    </div>
                </div>

                <!-- Active Projects -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Active Projects</p>
                            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ stats.active_projects }}</p>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-lg">
                            <Briefcase class="h-6 w-6 text-emerald-600" />
                        </div>
                    </div>
                </div>

                <!-- Total Funded -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Funded</p>
                            <p class="text-2xl font-bold text-blue-600 mt-2">{{ formatCurrency(stats.total_funded) }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <DollarSign class="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <TrendingUp class="h-5 w-5 text-blue-600" />
                        <h3 class="font-semibold text-gray-900">Total Disbursed</h3>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_disbursed) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <Activity class="h-5 w-5 text-green-600" />
                        <h3 class="font-semibold text-gray-900">Total Repaid</h3>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_repaid) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <AlertCircle class="h-5 w-5 text-yellow-600" />
                        <h3 class="font-semibold text-gray-900">Pending Disbursements</h3>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ stats.pending_disbursements }}</p>
                </div>
            </div>

            <!-- Recent Applications & Active Projects -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Applications -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Applications</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="recentApplications.length === 0" class="p-6 text-center text-gray-500">
                            No applications yet
                        </div>
                        <a
                            v-for="app in recentApplications"
                            :key="app.id"
                            :href="route('admin.bgf.applications.show', app.id)"
                            class="p-4 hover:bg-gray-50 block transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ app.business_name }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ app.user.name }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ app.reference_number }}</p>
                                </div>
                                <div class="ml-4 flex flex-col items-end gap-2">
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(app.status)]">
                                        {{ app.status.replace('_', ' ') }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(app.amount_requested) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Active Projects -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Active Projects</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="activeProjects.length === 0" class="p-6 text-center text-gray-500">
                            No active projects
                        </div>
                        <a
                            v-for="project in activeProjects"
                            :key="project.id"
                            :href="route('admin.bgf.projects.show', project.id)"
                            class="p-4 hover:bg-gray-50 block transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ project.project_number }}</p>
                                    <p class="text-sm text-gray-500">{{ project.user.name }}</p>
                                </div>
                                <div class="ml-4 flex flex-col items-end gap-2">
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(project.status)]">
                                        {{ project.status.replace('_', ' ') }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(project.approved_amount) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </CustomAppSidebarLayout>
</template>
