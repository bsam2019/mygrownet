<template>
    <AdminLayout title="Performance Management">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-800">Performance Management</h2>
                <Link
                    v-if="$page.props.auth.user.permissions?.includes('create-performance-reviews')"
                    :href="route('performance.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <TargetIcon class="w-4 h-4 mr-2" />
                    New Review
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <TargetIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Reviews</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ performance.total || performance.length }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Completed</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ completedReviewsCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <ClockIcon class="w-6 h-6 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pending</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ pendingReviewsCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <TrendingUp class="w-6 h-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Avg Rating</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ averageRating }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search employees..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @input="handleSearch"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select
                                v-model="selectedDepartment"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                            <select
                                v-model="selectedPeriod"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Periods</option>
                                <option value="Q1">Q1 2024</option>
                                <option value="Q2">Q2 2024</option>
                                <option value="Q3">Q3 2024</option>
                                <option value="Q4">Q4 2024</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Performance Reviews List -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Performance Reviews</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employee
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Period
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rating
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Goals Met
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Due Date
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="review in performance.data || performance" :key="review.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ review.employee?.first_name }} {{ review.employee?.last_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ review.employee?.position?.title }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ review.review_period }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-10 h-10">
                                                <div
                                                    :class="[
                                                        'w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium text-white',
                                                        getRatingColor(review.overall_rating)
                                                    ]"
                                                >
                                                    {{ review.overall_rating || 'N/A' }}
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm text-gray-900">
                                                    {{ getRatingLabel(review.overall_rating) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ review.goals_met || 0 }}/{{ review.total_goals || 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                getStatusColor(review.status)
                                            ]"
                                        >
                                            {{ review.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(review.due_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('performance.show', review.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            v-if="$page.props.auth.user.permissions?.includes('edit-performance-reviews')"
                                            :href="route('performance.edit', review.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Target, CheckCircle, Clock, TrendingUp } from 'lucide-vue-next';

interface PerformanceReview {
    id: number;
    employee?: {
        first_name: string;
        last_name: string;
        position?: {
            title: string;
        };
    };
    review_period: string;
    overall_rating?: number;
    goals_met?: number;
    total_goals?: number;
    status: string;
    due_date: string;
}

interface Department {
    id: number;
    name: string;
}

interface Props {
    performance: {
        data: PerformanceReview[];
        total?: number;
    } | PerformanceReview[];
    departments: Department[];
    filters: {
        search?: string;
        department_id?: number;
        status?: string;
        period?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedDepartment = ref(props.filters.department_id || '');
const selectedStatus = ref(props.filters.status || '');
const selectedPeriod = ref(props.filters.period || '');

const completedReviewsCount = computed(() => {
    const reviewsList = Array.isArray(props.performance) ? props.performance : props.performance.data;
    return reviewsList.filter(r => r.status === 'completed').length;
});

const pendingReviewsCount = computed(() => {
    const reviewsList = Array.isArray(props.performance) ? props.performance : props.performance.data;
    return reviewsList.filter(r => r.status === 'pending').length;
});

const averageRating = computed(() => {
    const reviewsList = Array.isArray(props.performance) ? props.performance : props.performance.data;
    const ratingsWithValues = reviewsList.filter(r => r.overall_rating);
    if (ratingsWithValues.length === 0) return 'N/A';
    const sum = ratingsWithValues.reduce((acc, r) => acc + (r.overall_rating || 0), 0);
    return (sum / ratingsWithValues.length).toFixed(1);
});

const getRatingColor = (rating?: number) => {
    if (!rating) return 'bg-gray-400';
    if (rating >= 4.5) return 'bg-green-500';
    if (rating >= 3.5) return 'bg-blue-500';
    if (rating >= 2.5) return 'bg-yellow-500';
    return 'bg-red-500';
};

const getRatingLabel = (rating?: number) => {
    if (!rating) return 'Not Rated';
    if (rating >= 4.5) return 'Excellent';
    if (rating >= 3.5) return 'Good';
    if (rating >= 2.5) return 'Average';
    return 'Needs Improvement';
};

const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'overdue':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const handleSearch = () => {
    router.get(route('admin.performance.index'), {
        search: searchQuery.value,
        department_id: selectedDepartment.value,
        status: selectedStatus.value,
        period: selectedPeriod.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.performance.index'), {
        search: searchQuery.value,
        department_id: selectedDepartment.value,
        status: selectedStatus.value,
        period: selectedPeriod.value,
    }, {
        preserveState: true,
        replace: true,
    });
};
</script>
