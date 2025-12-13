<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    ViewColumnsIcon,
    ChartBarIcon,
    PencilIcon,
    TrashIcon,
    CalendarDaysIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    FlagIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Column {
    id: number;
    name: string;
    color: string;
    sort_order: number;
    wip_limit: number | null;
    is_done_column: boolean;
}

interface Milestone {
    id: number;
    name: string;
    description: string | null;
    due_date: string | null;
    status: string;
    status_color: string;
    is_overdue: boolean;
    progress: number;
}

interface Project {
    id: number;
    name: string;
    description: string | null;
    color: string;
    status: string;
    status_label: string;
    status_color: string;
    start_date: string | null;
    end_date: string | null;
    budget: number;
    currency: string;
    progress_percentage: number;
    task_count: number;
    completed_task_count: number;
    days_remaining: number | null;
    is_overdue: boolean;
    columns: Column[];
    milestones: Milestone[];
}

interface Statistics {
    total_tasks: number;
    completed_tasks: number;
    in_progress_tasks: number;
    overdue_tasks: number;
    completion_rate: number;
    estimated_hours: number;
    actual_hours: number;
    hours_variance: number;
}

const props = defineProps<{
    project: Project;
    statistics: Statistics;
}>();

const showEditModal = ref(false);
const showMilestoneModal = ref(false);
const isSubmitting = ref(false);

const editForm = ref({
    name: props.project.name,
    description: props.project.description || '',
    color: props.project.color,
    status: props.project.status,
    start_date: props.project.start_date || '',
    end_date: props.project.end_date || '',
    budget: props.project.budget || '',
});

const newMilestone = ref({
    name: '',
    description: '',
    due_date: '',
});

const colors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
];

const updateProject = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.put(route('growbiz.projects.update', props.project.id), editForm.value, {
        onSuccess: () => {
            showEditModal.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const deleteProject = () => {
    if (!confirm(`Delete "${props.project.name}"? This will remove all tasks in this project.`)) return;
    router.delete(route('growbiz.projects.destroy', props.project.id));
};

const addMilestone = () => {
    if (!newMilestone.value.name.trim() || isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.projects.milestones.store', props.project.id), newMilestone.value, {
        onSuccess: () => {
            showMilestoneModal.value = false;
            newMilestone.value = { name: '', description: '', due_date: '' };
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const deleteMilestone = (milestoneId: number) => {
    if (!confirm('Delete this milestone?')) return;
    router.delete(route('growbiz.projects.milestones.destroy', [props.project.id, milestoneId]));
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('growbiz.projects.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back to projects">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: project.color }"></div>
                            <h1 class="text-xl font-bold text-gray-900">{{ project.name }}</h1>
                        </div>
                        <span :class="project.status_color" class="px-2 py-0.5 rounded-full text-xs font-medium">
                            {{ project.status_label }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="showEditModal = true"
                        class="p-2 hover:bg-gray-100 rounded-lg"
                        aria-label="Edit project"
                    >
                        <PencilIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </button>
                    <button
                        @click="deleteProject"
                        class="p-2 hover:bg-red-50 rounded-lg"
                        aria-label="Delete project"
                    >
                        <TrashIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3">
                <Link
                    :href="route('growbiz.projects.kanban', project.id)"
                    class="flex items-center gap-3 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors"
                >
                    <ViewColumnsIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                    <div>
                        <p class="font-semibold text-blue-900">Kanban Board</p>
                        <p class="text-sm text-blue-600">Manage tasks visually</p>
                    </div>
                </Link>
                <Link
                    :href="route('growbiz.projects.gantt', project.id)"
                    class="flex items-center gap-3 p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors"
                >
                    <ChartBarIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
                    <div>
                        <p class="font-semibold text-purple-900">Gantt Chart</p>
                        <p class="text-sm text-purple-600">View timeline</p>
                    </div>
                </Link>
            </div>

            <!-- Progress -->
            <div class="bg-white rounded-xl p-4 border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-gray-900">Overall Progress</span>
                    <span class="text-2xl font-bold" :style="{ color: project.color }">{{ project.progress_percentage }}%</span>
                </div>
                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all"
                        :style="{ width: `${project.progress_percentage}%`, backgroundColor: project.color }"
                    ></div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    {{ project.completed_task_count }} of {{ project.task_count }} tasks completed
                </p>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-xl p-4 border border-gray-100">
                    <div class="flex items-center gap-2 text-gray-500 mb-1">
                        <CheckCircleIcon class="h-5 w-5 text-green-500" aria-hidden="true" />
                        <span class="text-sm">Completed</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ statistics.completed_tasks }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100">
                    <div class="flex items-center gap-2 text-gray-500 mb-1">
                        <ClockIcon class="h-5 w-5 text-blue-500" aria-hidden="true" />
                        <span class="text-sm">In Progress</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ statistics.in_progress_tasks }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100">
                    <div class="flex items-center gap-2 text-gray-500 mb-1">
                        <ExclamationTriangleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                        <span class="text-sm">Overdue</span>
                    </div>
                    <p class="text-2xl font-bold text-red-600">{{ statistics.overdue_tasks }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100">
                    <div class="flex items-center gap-2 text-gray-500 mb-1">
                        <CalendarDaysIcon class="h-5 w-5 text-purple-500" aria-hidden="true" />
                        <span class="text-sm">Days Left</span>
                    </div>
                    <p class="text-2xl font-bold" :class="project.is_overdue ? 'text-red-600' : 'text-gray-900'">
                        {{ project.days_remaining ?? '∞' }}
                    </p>
                </div>
            </div>

            <!-- Milestones -->
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="flex items-center justify-between p-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Milestones</h3>
                    <button
                        @click="showMilestoneModal = true"
                        class="text-sm text-blue-600 font-medium hover:text-blue-700"
                    >
                        + Add Milestone
                    </button>
                </div>
                <div v-if="project.milestones.length === 0" class="p-6 text-center text-gray-500">
                    <FlagIcon class="h-8 w-8 mx-auto text-gray-300 mb-2" aria-hidden="true" />
                    <p>No milestones yet</p>
                </div>
                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="milestone in project.milestones"
                        :key="milestone.id"
                        class="p-4 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-3 h-3 rotate-45"
                                :class="milestone.status === 'completed' ? 'bg-green-500' : milestone.is_overdue ? 'bg-red-500' : 'bg-purple-500'"
                            ></div>
                            <div>
                                <p class="font-medium text-gray-900">{{ milestone.name }}</p>
                                <p v-if="milestone.due_date" class="text-sm text-gray-500">
                                    Due: {{ milestone.due_date }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="milestone.status_color" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ milestone.status }}
                            </span>
                            <button
                                @click="deleteMilestone(milestone.id)"
                                class="p-1 hover:bg-red-50 rounded"
                                aria-label="Delete milestone"
                            >
                                <TrashIcon class="h-4 w-4 text-red-400" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Details -->
            <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-3">
                <h3 class="font-semibold text-gray-900">Details</h3>
                <div v-if="project.description" class="text-sm text-gray-600">
                    {{ project.description }}
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Start Date</span>
                        <p class="font-medium text-gray-900">{{ project.start_date || 'Not set' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">End Date</span>
                        <p class="font-medium text-gray-900">{{ project.end_date || 'Not set' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Budget</span>
                        <p class="font-medium text-gray-900">
                            {{ project.budget ? `${project.currency} ${project.budget.toLocaleString()}` : 'Not set' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Hours Logged</span>
                        <p class="font-medium text-gray-900">{{ statistics.actual_hours }}h / {{ statistics.estimated_hours }}h</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150">
                <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/50" @click="showEditModal = false"></div>
            </Transition>
            <Transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="translate-y-full" leave-active-class="transition-transform duration-200 ease-in" leave-to-class="translate-y-full">
                <div v-if="showEditModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl max-h-[90vh] overflow-y-auto safe-area-bottom">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Edit Project</h2>
                        <button @click="showEditModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="updateProject" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input v-model="editForm.name" type="text" required class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="editForm.description" rows="3" class="w-full border-gray-200 rounded-lg"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select v-model="editForm.status" class="w-full border-gray-200 rounded-lg">
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="color in colors"
                                    :key="color"
                                    type="button"
                                    @click="editForm.color = color"
                                    class="w-8 h-8 rounded-full border-2 transition-all"
                                    :class="editForm.color === color ? 'border-gray-900 scale-110' : 'border-transparent'"
                                    :style="{ backgroundColor: color }"
                                ></button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input v-model="editForm.start_date" type="date" class="w-full border-gray-200 rounded-lg" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input v-model="editForm.end_date" type="date" class="w-full border-gray-200 rounded-lg" />
                            </div>
                        </div>
                        <button type="submit" :disabled="isSubmitting" class="w-full py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 disabled:opacity-50">
                            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>

        <!-- Milestone Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150">
                <div v-if="showMilestoneModal" class="fixed inset-0 z-50 bg-black/50" @click="showMilestoneModal = false"></div>
            </Transition>
            <Transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="translate-y-full" leave-active-class="transition-transform duration-200 ease-in" leave-to-class="translate-y-full">
                <div v-if="showMilestoneModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl safe-area-bottom">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Add Milestone</h2>
                        <button @click="showMilestoneModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="addMilestone" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input v-model="newMilestone.name" type="text" required class="w-full border-gray-200 rounded-lg" placeholder="e.g., Phase 1 Complete" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="newMilestone.description" rows="2" class="w-full border-gray-200 rounded-lg"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                            <input v-model="newMilestone.due_date" type="date" class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <button type="submit" :disabled="isSubmitting || !newMilestone.name" class="w-full py-3 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 disabled:opacity-50">
                            {{ isSubmitting ? 'Adding...' : 'Add Milestone' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
