<template>
    <AdminLayout>
        <Head :title="position.title" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link :href="route('admin.organization.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
                        ← Back to Organizational Chart
                    </Link>
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ position.title }}</h1>
                            <p class="mt-1 text-sm text-gray-600">{{ position.department.name }}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="levelBadgeClass">
                            {{ formatLevel(position.organizational_level) }}
                        </span>
                    </div>
                </div>

                <!-- Overview Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Current Employees</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ position.employees.length }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">KPIs Defined</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ position.kpis.length }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Responsibilities</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ position.responsibilities.length }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Position Details -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Position Details</h3>
                                <dl class="space-y-3">
                                    <div v-if="position.description">
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ position.description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ position.department.name }}</dd>
                                    </div>
                                    <div v-if="position.reports_to">
                                        <dt class="text-sm font-medium text-gray-500">Reports To</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ position.reports_to.title }}</dd>
                                    </div>
                                    <div v-if="position.min_salary && position.max_salary">
                                        <dt class="text-sm font-medium text-gray-500">Salary Range</dt>
                                        <dd class="mt-1 text-sm text-gray-900">K{{ position.min_salary }} - K{{ position.max_salary }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Level</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ position.level }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Current Employees -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Employees</h3>
                                <div v-if="position.employees.length > 0" class="space-y-3">
                                    <div v-for="employee in position.employees" :key="employee.id" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 font-medium text-sm">{{ getInitials(employee.full_name) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ employee.full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ employee.email }}</p>
                                            </div>
                                        </div>
                                        <Link :href="route('admin.organization.employees.kpis', employee.id)" class="text-sm text-blue-600 hover:text-blue-800">
                                            View KPIs →
                                        </Link>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">No employees in this position</p>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Reports -->
                        <div v-if="position.direct_reports && position.direct_reports.length > 0" class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Direct Report Positions</h3>
                                <div class="space-y-2">
                                    <Link 
                                        v-for="report in position.direct_reports" 
                                        :key="report.id"
                                        :href="route('admin.organization.positions.show', report.id)"
                                        class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                                    >
                                        <p class="text-sm font-medium text-gray-900">{{ report.title }}</p>
                                        <p class="text-xs text-gray-500">{{ report.department.name }}</p>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Responsibilities -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Key Responsibilities</h3>
                                    <button
                                        @click="showAddResponsibility = true"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                                    >
                                        + Add
                                    </button>
                                </div>
                                <div v-if="position.responsibilities.length > 0" class="space-y-3">
                                    <div v-for="resp in position.responsibilities" :key="resp.id" class="border-l-4 pl-3 py-2" :class="responsibilityBorderClass(resp.priority)">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <h4 class="text-sm font-medium text-gray-900">{{ resp.responsibility_title }}</h4>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="priorityBadgeClass(resp.priority)">
                                                        {{ resp.priority }}
                                                    </span>
                                                </div>
                                                <p v-if="resp.responsibility_description" class="text-xs text-gray-600 mt-1">{{ resp.responsibility_description }}</p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                                    {{ resp.category }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6">
                                    <p class="text-sm text-gray-500">No responsibilities defined</p>
                                </div>
                            </div>
                        </div>

                        <!-- KPIs -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Key Performance Indicators</h3>
                                    <Link :href="route('admin.organization.kpis.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        Manage KPIs →
                                    </Link>
                                </div>
                                <div v-if="position.kpis.length > 0" class="space-y-3">
                                    <div v-for="kpi in position.kpis" :key="kpi.id" class="p-3 border border-gray-200 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-900">{{ kpi.kpi_name }}</h4>
                                        <p v-if="kpi.kpi_description" class="text-xs text-gray-600 mt-1">{{ kpi.kpi_description }}</p>
                                        <div class="mt-2 flex items-center space-x-3 text-xs text-gray-500">
                                            <span>Target: <strong class="text-gray-900">{{ kpi.target_value }} {{ kpi.measurement_unit }}</strong></span>
                                            <span>{{ formatFrequency(kpi.measurement_frequency) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6">
                                    <p class="text-sm text-gray-500">No KPIs defined</p>
                                </div>
                            </div>
                        </div>

                        <!-- Hiring Plans -->
                        <div v-if="position.hiring_roadmap && position.hiring_roadmap.length > 0" class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Hiring Plans</h3>
                                    <Link :href="route('admin.organization.hiring.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        View Roadmap →
                                    </Link>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="plan in position.hiring_roadmap" :key="plan.id" class="p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="statusBadgeClass(plan.status)">
                                                        {{ formatStatus(plan.status) }}
                                                    </span>
                                                    <span class="ml-2 text-xs text-gray-500">{{ formatPhase(plan.phase) }}</span>
                                                </div>
                                                <p class="text-xs text-gray-600 mt-1">Headcount: {{ plan.headcount }}</p>
                                                <p v-if="plan.target_hire_date" class="text-xs text-gray-600">Target: {{ formatDate(plan.target_hire_date) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Responsibility Modal (placeholder) -->
        <div v-if="showAddResponsibility" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Responsibility</h3>
                <p class="text-sm text-gray-500">Responsibility management coming soon...</p>
                <button @click="showAddResponsibility = false" class="mt-4 w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    position: {
        type: Object,
        required: true
    }
});

const showAddResponsibility = ref(false);

const levelBadgeClass = computed(() => {
    const level = props.position.organizational_level;
    const classes = {
        'c_level': 'bg-purple-100 text-purple-800',
        'director': 'bg-blue-100 text-blue-800',
        'manager': 'bg-green-100 text-green-800',
        'team_lead': 'bg-yellow-100 text-yellow-800',
        'individual': 'bg-gray-100 text-gray-800'
    };
    return classes[level] || 'bg-gray-100 text-gray-800';
});

const formatLevel = (level) => {
    const labels = {
        'c_level': 'C-Level',
        'director': 'Director',
        'manager': 'Manager',
        'team_lead': 'Team Lead',
        'individual': 'Individual'
    };
    return labels[level] || level;
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const responsibilityBorderClass = (priority) => {
    const classes = {
        'critical': 'border-red-500',
        'high': 'border-orange-500',
        'medium': 'border-yellow-500',
        'low': 'border-gray-300'
    };
    return classes[priority] || 'border-gray-300';
};

const priorityBadgeClass = (priority) => {
    const classes = {
        'critical': 'bg-red-100 text-red-800',
        'high': 'bg-orange-100 text-orange-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-gray-100 text-gray-800'
    };
    return classes[priority] || 'bg-gray-100 text-gray-800';
};

const statusBadgeClass = (status) => {
    const classes = {
        'planned': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'hired': 'bg-green-100 text-green-800',
        'cancelled': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status) => {
    const labels = {
        'planned': 'Planned',
        'in_progress': 'In Progress',
        'hired': 'Hired',
        'cancelled': 'Cancelled'
    };
    return labels[status] || status;
};

const formatPhase = (phase) => {
    const labels = {
        'phase_1': 'Phase 1',
        'phase_2': 'Phase 2',
        'phase_3': 'Phase 3'
    };
    return labels[phase] || phase;
};

const formatFrequency = (frequency) => {
    const labels = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'monthly': 'Monthly',
        'quarterly': 'Quarterly',
        'annual': 'Annual'
    };
    return labels[frequency] || frequency;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>
