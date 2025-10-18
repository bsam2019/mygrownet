<template>
    <AdminLayout title="Employee Details">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ employee.first_name }} {{ employee.last_name }}
                    </h2>
                    <p class="text-sm text-gray-600">#{{ employee.employee_number }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('admin.employees.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to List
                    </Link>
                    <Link
                        v-if="$page.props.auth.user.permissions.includes('edit-employees')"
                        :href="route('admin.employees.edit', employee.id)"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                    >
                        <PencilIcon class="w-4 h-4 mr-2" />
                        Edit Employee
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Employee Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ employee.first_name }} {{ employee.last_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Employee Number</label>
                                    <p class="mt-1 text-sm text-gray-900">#{{ employee.employee_number }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ employee.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ employee.phone || 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ employee.department?.name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Position</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ employee.position?.title || 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Employment Status</label>
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800': employee.employment_status === 'active',
                                            'bg-yellow-100 text-yellow-800': employee.employment_status === 'inactive',
                                            'bg-red-100 text-red-800': employee.employment_status === 'terminated'
                                        }"
                                        class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ employee.employment_status }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hire Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(employee.hire_date) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Metrics</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <p class="text-2xl font-semibold text-blue-600">{{ performanceMetrics.average_score.toFixed(1) }}</p>
                                    <p class="text-sm text-gray-600">Average Score</p>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <p class="text-2xl font-semibold text-green-600">{{ formatCurrency(performanceMetrics.total_commissions_ytd) }}</p>
                                    <p class="text-sm text-gray-600">YTD Commissions</p>
                                </div>
                                <div class="text-center p-4 bg-purple-50 rounded-lg">
                                    <p class="text-2xl font-semibold text-purple-600">{{ performanceMetrics.active_client_assignments }}</p>
                                    <p class="text-sm text-gray-600">Active Clients</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Performance Reviews -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Performance Reviews</h3>
                            <div class="space-y-4">
                                <div v-for="review in employee.performance_reviews" :key="review.id" class="border-l-4 border-blue-500 pl-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ review.review_type }} Review</p>
                                            <p class="text-sm text-gray-600">{{ formatDate(review.evaluation_period_start) }} - {{ formatDate(review.evaluation_period_end) }}</p>
                                        </div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Score: {{ review.overall_score }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-700">{{ review.summary || 'No summary available' }}</p>
                                </div>
                                <div v-if="!employee.performance_reviews.length" class="text-center py-4 text-gray-500">
                                    No performance reviews found
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <Link
                                    v-if="$page.props.auth.user.permissions.includes('edit-employees')"
                                    :href="route('admin.employees.edit', employee.id)"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    <PencilIcon class="w-4 h-4 mr-2" />
                                    Edit Employee
                                </Link>
                                <Link
                                    v-if="$page.props.auth.user.permissions.includes('view-performance')"
                                    :href="route('admin.performance.index', { employee: employee.id })"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    <ChartBarIcon class="w-4 h-4 mr-2" />
                                    View Performance
                                </Link>
                                <Link
                                    v-if="$page.props.auth.user.permissions.includes('view-commissions')"
                                    :href="route('admin.commissions.index', { employee: employee.id })"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    <CurrencyDollarIcon class="w-4 h-4 mr-2" />
                                    View Commissions
                                </Link>
                            </div>
                        </div>

                        <!-- Commission Summary -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Commission Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total Earned</span>
                                    <span class="text-sm font-medium text-gray-900">{{ formatCurrency(commissionSummary.total_earned) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Pending</span>
                                    <span class="text-sm font-medium text-yellow-600">{{ formatCurrency(commissionSummary.pending_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">YTD Earnings</span>
                                    <span class="text-sm font-medium text-green-600">{{ formatCurrency(commissionSummary.ytd_earnings) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Manager Information -->
                        <div v-if="employee.manager" class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reports To</h3>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ employee.manager.first_name.charAt(0) }}{{ employee.manager.last_name.charAt(0) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ employee.manager.first_name }} {{ employee.manager.last_name }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ employee.manager.position?.title }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Reports -->
                        <div v-if="employee.direct_reports && employee.direct_reports.length" class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Direct Reports</h3>
                            <div class="space-y-3">
                                <div v-for="report in employee.direct_reports" :key="report.id" class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-700">
                                                {{ report.first_name.charAt(0) }}{{ report.last_name.charAt(0) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <Link
                                            :href="route('admin.employees.show', report.id)"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800"
                                        >
                                            {{ report.first_name }} {{ report.last_name }}
                                        </Link>
                                        <p class="text-xs text-gray-500">{{ report.position?.title }}</p>
                                    </div>
                                </div>
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
    ArrowLeftIcon,
    PencilIcon,
    ChartBarIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline';
import { formatDate, formatCurrency } from '@/utils/formatting';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    employee: Object,
    performanceMetrics: Object,
    commissionSummary: Object,
});
</script>