<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, watch } from 'vue';
import {
    FlagIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ChevronDownIcon,
    ChevronUpIcon,
} from '@heroicons/vue/24/outline';

interface Milestone {
    title: string;
    target_date: string;
    completed: boolean;
    completed_at: string;
}

interface Goal {
    id: number;
    title: string;
    description: string;
    category: string;
    progress: number;
    status: string;
    start_date: string;
    due_date: string;
    milestones: Milestone[];
}

interface Props {
    goals: Goal[];
    stats: {
        total: number;
        completed: number;
        in_progress: number;
        pending: number;
        average_progress: number;
        completion_rate: number;
    };
    filters: {
        status?: string;
        category?: string;
    };
}

const props = defineProps<Props>();

const selectedStatus = ref(props.filters.status || '');
const selectedCategory = ref(props.filters.category || '');
const expandedGoals = ref<number[]>([]);

const applyFilters = () => {
    router.get(route('employee.portal.goals.index'), {
        status: selectedStatus.value || undefined,
        category: selectedCategory.value || undefined,
    }, { preserveState: true });
};

watch([selectedStatus, selectedCategory], applyFilters);

const toggleExpand = (goalId: number) => {
    const index = expandedGoals.value.indexOf(goalId);
    if (index === -1) {
        expandedGoals.value.push(goalId);
    } else {
        expandedGoals.value.splice(index, 1);
    }
};

const isExpanded = (goalId: number) => expandedGoals.value.includes(goalId);

const updateProgress = (goalId: number, progress: number) => {
    router.patch(route('employee.portal.goals.update-progress', goalId), {
        progress,
    }, { preserveScroll: true });
};

const updateMilestone = (goalId: number, milestoneIndex: number, completed: boolean) => {
    router.patch(route('employee.portal.goals.update-milestone', { goal: goalId, milestone: milestoneIndex }), {
        completed,
    }, { preserveScroll: true });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'draft': 'bg-gray-100 text-gray-700',
        'pending': 'bg-amber-100 text-amber-700',
        'in_progress': 'bg-blue-100 text-blue-700',
        'completed': 'bg-green-100 text-green-700',
        'cancelled': 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getProgressColor = (progress: number) => {
    if (progress >= 75) return 'bg-green-500';
    if (progress >= 50) return 'bg-blue-500';
    if (progress >= 25) return 'bg-amber-500';
    return 'bg-gray-400';
};

const isOverdue = (goal: Goal) => {
    if (['completed', 'cancelled'].includes(goal.status)) return false;
    return new Date(goal.due_date) < new Date();
};

const categories = ['performance', 'development', 'project', 'personal'];
</script>

<template>
    <Head title="My Goals" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Goals</h1>
                <p class="text-gray-500 mt-1">Track your objectives and milestones</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Goals</p>
                    <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">In Progress</p>
                    <p class="text-2xl font-bold text-blue-600">{{ stats.in_progress }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Avg Progress</p>
                    <p class="text-2xl font-bold text-gray-900">{{ stats.average_progress }}%</p>
                </div>
            </div>

            <!-- Goals List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100 flex flex-wrap items-center gap-4">
                    <h2 class="font-semibold text-gray-900">Goals</h2>
                    <div class="flex-1"></div>
                    <select v-model="selectedStatus"
                        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                    <select v-model="selectedCategory"
                        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">
                            {{ cat.charAt(0).toUpperCase() + cat.slice(1) }}
                        </option>
                    </select>
                </div>

                <div class="divide-y divide-gray-100">
                    <div v-for="goal in goals" :key="goal.id" class="p-4">
                        <!-- Goal Header -->
                        <div class="flex items-start gap-4">
                            <button @click="toggleExpand(goal.id)"
                                class="p-1 hover:bg-gray-100 rounded transition-colors mt-1"
                                :aria-label="isExpanded(goal.id) ? 'Collapse goal' : 'Expand goal'">
                                <ChevronDownIcon v-if="!isExpanded(goal.id)" class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                <ChevronUpIcon v-else class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            </button>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ goal.title }}</h3>
                                        <p v-if="goal.description" class="text-sm text-gray-500 mt-1">
                                            {{ goal.description }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                            {{ goal.category }}
                                        </span>
                                        <span :class="getStatusColor(goal.status)"
                                            class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ goal.status.replace('_', ' ') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-500">Progress</span>
                                        <span class="font-medium text-gray-900">{{ goal.progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div :class="getProgressColor(goal.progress)"
                                            class="h-2 rounded-full transition-all duration-300"
                                            :style="{ width: `${goal.progress}%` }">
                                        </div>
                                    </div>
                                </div>

                                <!-- Due Date -->
                                <div class="flex items-center gap-4 mt-3 text-sm">
                                    <span :class="isOverdue(goal) ? 'text-red-600' : 'text-gray-500'">
                                        Due: {{ new Date(goal.due_date).toLocaleDateString() }}
                                        <ExclamationTriangleIcon v-if="isOverdue(goal)" class="inline h-4 w-4 ml-1" aria-hidden="true" />
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded Content -->
                        <div v-if="isExpanded(goal.id)" class="mt-4 ml-10 space-y-4">
                            <!-- Progress Slider -->
                            <div v-if="goal.status !== 'completed'" class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Update Progress
                                </label>
                                <input type="range" 
                                    :value="goal.progress"
                                    @change="(e) => updateProgress(goal.id, parseInt((e.target as HTMLInputElement).value))"
                                    min="0" max="100" step="5"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" />
                            </div>

                            <!-- Milestones -->
                            <div v-if="goal.milestones && goal.milestones.length > 0">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Milestones</h4>
                                <div class="space-y-2">
                                    <div v-for="(milestone, index) in goal.milestones" :key="index"
                                        class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                        <input type="checkbox"
                                            :checked="milestone.completed"
                                            @change="updateMilestone(goal.id, index, !milestone.completed)"
                                            :disabled="goal.status === 'completed'"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                        <span :class="milestone.completed ? 'line-through text-gray-400' : 'text-gray-700'">
                                            {{ milestone.title }}
                                        </span>
                                        <span v-if="milestone.target_date" class="text-xs text-gray-400 ml-auto">
                                            {{ new Date(milestone.target_date).toLocaleDateString() }}
                                        </span>
                                        <CheckCircleIcon v-if="milestone.completed" class="h-4 w-4 text-green-500 ml-auto" aria-hidden="true" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="goals.length === 0" class="p-12 text-center">
                        <FlagIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-medium text-gray-900">No goals found</h3>
                        <p class="text-gray-500 mt-1">You don't have any goals assigned yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
