<template>
    <AdminLayout title="Performance Management">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-800">Performance Management</h2>
                <Link
                    v-if="$page.props.auth.user.permissions.includes('create-performance-reviews')"
                    :href="route('admin.performance.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    New Review
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <ChartBarIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Reviews</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_reviews }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <TrophyIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Average Score</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.average_score.toFixed(1) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <ClockIcon class="w-6 h-6 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pending Reviews</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.pending_reviews }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <CalendarIcon class="w-6 h-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">This Quarter</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.quarterly_reviews }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Employee</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search employees..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                @input="debouncedSearch"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select
                                v-model="filters.department"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                @change="applyFilters"
                            >
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Review Type</label>
                            <select
                                v-model="filters.review_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                @change="applyFilters"
                            >
                                <option value="">All Types</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annual">Annual</option>
                                <option value="probationary">Probationary</option>
                                <option value="special">Special</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="filters.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                @change="applyFilters"
                            >
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Performance Reviews Table -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employee
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Review Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Period
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Score
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reviewer
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="review in reviews.data" :key="review.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ review.employee.first_name.charAt(0) }}{{ review.employee.last_name.charAt(0) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ review.employee.first_name }} {{ review.employee.last_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ review.employee.position?.title }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ review.review_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(review.evaluation_period_start) }} - {{ formatDate(review.evaluation_period_end) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900">{{ review.overall_score || 'N/A' }}</span>
                                            <div v-if="review.overall_score" class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                <div
                                                    :class="{
                                                        'bg-red-500': review.overall_score < 60,
                                                        'bg-yellow-500': review.overall_score >= 60 && review.overall_score < 80,
                                                        'bg-green-500': review.overall_score >= 80
                                                    }"
                                                    class="h-2 rounded-full"
                                                    :style="{ width: `${review.overall_score}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="{
                                                'bg-gray-100 text-gray-800': review.status === 'draft',
                                                'bg-yellow-100 text-yellow-800': review.status === 'pending',
                                                'bg-blue-100 text-blue-800': review.status === 'completed',
                                                'bg-green-100 text-green-800': review.status === 'approved'
                                            }"
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ review.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ review.reviewer?.first_name }} {{ review.reviewer?.last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link
                                                :href="route('performance.show', review.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                <EyeIcon class="w-4 h-4" />
                                            </Link>
                                            <Link
                                                v-if="$page.props.auth.user.permissions.includes('edit-performance-reviews')"
                                                :href="route('performance.edit', review.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                <PencilIcon class="w-4 h-4" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link
                                    v-if="reviews.prev_page_url"
                                    :href="reviews.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="reviews.next_page_url"
                                    :href="reviews.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing {{ reviews.from }} to {{ reviews.to }} of {{ reviews.total }} results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <Link
                                            v-if="reviews.prev_page_url"
                                            :href="reviews.prev_page_url"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                        >
                                            Previous
                                        </Link>
                                        <Link
                                            v-if="reviews.next_page_url"
                                            :href="reviews.next_page_url"
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
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import {
    ChartBarIcon,
    TrophyIcon,
    ClockIcon,
    CalendarIcon,
    PlusIcon,
    EyeIcon,
    PencilIcon
} from '@heroicons/vue/24/outline';
import { formatDate } from '@/utils/formatting';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    reviews: Object,
    departments: Array,
    filters: Object,
    stats: Object,
});

const filters = reactive({
    search: props.filters.search || '',
    department: props.filters.department || '',
    review_type: props.filters.review_type || '',
    status: props.filters.status || '',
});

const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    router.get(route('performance.index'), filters, {
        preserveState: true,
        replace: true,
    });
};
</script>
