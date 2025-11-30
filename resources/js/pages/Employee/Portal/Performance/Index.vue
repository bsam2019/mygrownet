<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    StarIcon,
    ClockIcon,
    CheckCircleIcon,
    ChartBarIcon,
    ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

interface Review {
    id: number;
    review_period: string;
    review_type: string;
    status: string;
    overall_rating: number | null;
    due_date: string | null;
    completed_at: string | null;
    reviewer?: { full_name: string };
}

interface Props {
    reviews: Review[];
    stats: {
        total: number;
        completed: number;
        pending: number;
        average_rating: number | null;
        latest_rating: number | null;
    };
    ratingTrends: {
        labels: string[];
        data: number[];
    };
    filters: { status?: string; type?: string };
}

const props = defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700',
        submitted: 'bg-blue-100 text-blue-700',
        in_review: 'bg-amber-100 text-amber-700',
        completed: 'bg-green-100 text-green-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        self: 'Self Assessment',
        manager: 'Manager Review',
        peer: 'Peer Review',
        '360': '360° Review',
    };
    return labels[type] || type;
};

const renderStars = (rating: number | null) => {
    if (!rating) return [];
    const stars = [];
    for (let i = 1; i <= 5; i++) {
        stars.push(i <= Math.round(rating));
    }
    return stars;
};
</script>

<template>
    <Head title="Performance Reviews" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Performance Reviews</h1>
                    <p class="text-gray-500 mt-1">Track your performance evaluations and growth</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Reviews</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <ChartBarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Completed</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pending</p>
                            <p class="text-2xl font-bold text-amber-600">{{ stats.pending }}</p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Average Rating</p>
                            <div class="flex items-center gap-2">
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ stats.average_rating?.toFixed(1) || '-' }}
                                </p>
                                <div class="flex">
                                    <component
                                        v-for="(filled, i) in renderStars(stats.average_rating)"
                                        :key="i"
                                        :is="filled ? StarIconSolid : StarIcon"
                                        class="h-4 w-4"
                                        :class="filled ? 'text-amber-400' : 'text-gray-300'"
                                        aria-hidden="true"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <StarIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Review History</h2>
                </div>

                <div class="divide-y divide-gray-100">
                    <div v-for="review in reviews" :key="review.id" class="p-5 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="font-medium text-gray-900">{{ review.review_period }}</h3>
                                    <span :class="getStatusColor(review.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ review.status.replace('_', ' ') }}
                                    </span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                        {{ getTypeLabel(review.review_type) }}
                                    </span>
                                </div>
                                <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                    <span v-if="review.reviewer">Reviewer: {{ review.reviewer.full_name }}</span>
                                    <span v-if="review.due_date">Due: {{ new Date(review.due_date).toLocaleDateString() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div v-if="review.overall_rating" class="flex items-center gap-1">
                                    <component
                                        v-for="(filled, i) in renderStars(review.overall_rating)"
                                        :key="i"
                                        :is="filled ? StarIconSolid : StarIcon"
                                        class="h-5 w-5"
                                        :class="filled ? 'text-amber-400' : 'text-gray-300'"
                                        aria-hidden="true"
                                    />
                                    <span class="ml-2 font-medium text-gray-900">{{ review.overall_rating.toFixed(1) }}</span>
                                </div>

                                <Link :href="route('employee.portal.performance.show', review.id)"
                                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    View Details
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-if="reviews.length === 0" class="p-8 text-center text-gray-500">
                        <ChartBarIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                        <p>No performance reviews yet</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
