<template>
    <GrowBizLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('growbiz.tasks.index')" 
                        class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                        aria-label="Back to tasks"
                    >
                        <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900">Task Details</h1>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('growbiz.tasks.edit', task.id)" class="p-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 active:bg-gray-300 transition-colors">
                        <PencilIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <button @click="confirmDelete" class="p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 active:bg-red-200 transition-colors">
                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <div class="max-w-2xl mx-auto space-y-4">
                <!-- Main Task Card -->
                <div class="bg-white rounded-2xl shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ task.title }}</h1>
                                <div class="flex items-center gap-3 mt-2">
                                    <StatusBadge :status="task.status" />
                                    <PriorityBadge :priority="task.priority" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Progress Section -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-gray-700">Progress</h3>
                                <button 
                                    @click="showProgressSlider = !showProgressSlider"
                                    class="text-sm text-blue-600 hover:text-blue-700"
                                >
                                    {{ showProgressSlider ? 'Done' : 'Update' }}
                                </button>
                            </div>
                            <ProgressBar :percentage="task.progress_percentage" :show-label="false" />
                            
                            <!-- Progress Slider -->
                            <div v-if="showProgressSlider" class="mt-4 p-4 bg-gray-50 rounded-xl">
                                <input 
                                    type="range" 
                                    v-model.number="newProgress"
                                    min="0" 
                                    max="100" 
                                    step="5"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                />
                                <div class="flex justify-between mt-2">
                                    <span class="text-sm text-gray-500">{{ newProgress }}%</span>
                                    <button 
                                        @click="updateProgress"
                                        :disabled="newProgress === task.progress_percentage"
                                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Time Tracking -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-2 mb-1">
                                    <ClockIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                                    <span class="text-sm text-gray-500">Time Logged</span>
                                </div>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ formatHours(task.actual_hours) }}
                                </p>
                                <p v-if="task.estimated_hours" class="text-xs text-gray-500 mt-1">
                                    of {{ formatHours(task.estimated_hours) }} estimated
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-2 mb-1">
                                    <ChartBarIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                                    <span class="text-sm text-gray-500">Efficiency</span>
                                </div>
                                <p class="text-xl font-semibold" :class="efficiencyColor">
                                    {{ task.time_efficiency ? `${task.time_efficiency}%` : 'N/A' }}
                                </p>
                                <p v-if="task.remaining_hours !== null" class="text-xs text-gray-500 mt-1">
                                    {{ formatHours(task.remaining_hours) }} remaining
                                </p>
                            </div>
                        </div>

                        <!-- Log Time Button -->
                        <button 
                            @click="showTimeModal = true"
                            class="w-full py-3 px-4 bg-purple-50 text-purple-700 font-medium rounded-xl hover:bg-purple-100 active:bg-purple-200 transition-colors flex items-center justify-center gap-2"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Log Time
                        </button>

                        <div v-if="task.description">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ task.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Due Date</h3>
                                <p class="mt-1 text-gray-900" :class="{ 'text-red-600': task.is_overdue }">
                                    {{ task.due_date || 'Not set' }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Category</h3>
                                <p class="mt-1 text-gray-900">{{ task.category || 'None' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Started</h3>
                                <p class="mt-1 text-gray-900">{{ task.started_at || 'Not started' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Completed</h3>
                                <p class="mt-1 text-gray-900">{{ task.completed_at || 'In progress' }}</p>
                            </div>
                        </div>

                        <!-- Quick Status Update -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Update Status</h3>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="status in statuses" :key="status.value"
                                    @click="updateStatus(status.value)"
                                    :disabled="task.status === status.value"
                                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                                    :class="task.status === status.value ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'">
                                    {{ status.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Feed -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity</h3>
                    <TaskActivityFeed :updates="updates" />
                </div>

                <!-- Comments Section -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Comments</h3>
                    <CommentSection 
                        :comments="comments"
                        :task-id="task.id"
                        :current-user-id="currentUserId"
                    />
                </div>

                <!-- Add Note Section -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Note</h3>
                    <form @submit.prevent="addNote">
                        <textarea
                            v-model="noteContent"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                            placeholder="Add a note about this task..."
                        />
                        <button
                            type="submit"
                            :disabled="!noteContent.trim()"
                            class="mt-3 px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Add Note
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Time Log Modal -->
        <TimeLogModal 
            :show="showTimeModal" 
            @close="showTimeModal = false"
            @submit="logTime"
        />
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import StatusBadge from '@/Components/GrowBiz/StatusBadge.vue';
import PriorityBadge from '@/Components/GrowBiz/PriorityBadge.vue';
import ProgressBar from '@/Components/GrowBiz/ProgressBar.vue';
import TaskActivityFeed from '@/Components/GrowBiz/TaskActivityFeed.vue';
import TimeLogModal from '@/Components/GrowBiz/TimeLogModal.vue';
import CommentSection from '@/Components/GrowBiz/CommentSection.vue';
import { 
    ArrowLeftIcon, 
    PencilIcon, 
    TrashIcon,
    ClockIcon,
    ChartBarIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';
import { useToast } from '@/composables/useToast';

interface Comment {
    id: number;
    content: string;
    user?: { id: number; name: string };
    created_at: string;
}

interface Props {
    task: {
        id: number;
        title: string;
        description: string | null;
        status: string;
        priority: string;
        due_date: string | null;
        category: string | null;
        progress_percentage: number;
        estimated_hours: number | null;
        actual_hours: number;
        time_efficiency: number | null;
        remaining_hours: number | null;
        started_at: string | null;
        completed_at: string | null;
        is_overdue: boolean;
        created_at: string;
        updated_at: string;
    };
    updates: Array<{
        id: number;
        update_type: string;
        formatted_type: string;
        old_status?: string;
        new_status?: string;
        old_progress?: number;
        new_progress?: number;
        hours_logged?: number;
        notes?: string;
        user?: { id: number; name: string };
        created_at: string;
    }>;
    comments: Comment[];
    currentUserId: number;
    statuses: Array<{ value: string; label: string }>;
    priorities: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const { toast } = useToast();

const showProgressSlider = ref(false);
const showTimeModal = ref(false);
const newProgress = ref(props.task.progress_percentage);
const noteContent = ref('');

const efficiencyColor = computed(() => {
    if (!props.task.time_efficiency) return 'text-gray-900';
    if (props.task.time_efficiency >= 100) return 'text-green-600';
    if (props.task.time_efficiency >= 80) return 'text-blue-600';
    if (props.task.time_efficiency >= 60) return 'text-yellow-600';
    return 'text-red-600';
});

const formatHours = (hours: number | null): string => {
    if (hours === null || hours === undefined) return '0h';
    if (hours < 1) return `${Math.round(hours * 60)}m`;
    if (hours === 1) return '1h';
    return `${hours.toFixed(1)}h`;
};

const updateStatus = (status: string) => {
    router.patch(route('growbiz.tasks.status', props.task.id), { status }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Task status updated');
        },
        onError: () => {
            toast.error('Failed to update status');
        }
    });
};

const updateProgress = () => {
    router.patch(route('growbiz.tasks.progress', props.task.id), { 
        progress: newProgress.value 
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Progress updated');
            showProgressSlider.value = false;
        },
        onError: () => {
            toast.error('Failed to update progress');
        }
    });
};

const logTime = (data: { hours: number; notes: string }) => {
    router.post(route('growbiz.tasks.time', props.task.id), data, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Time logged successfully');
            showTimeModal.value = false;
        },
        onError: () => {
            toast.error('Failed to log time');
        }
    });
};

const addNote = () => {
    if (!noteContent.value.trim()) return;
    
    router.post(route('growbiz.tasks.notes.store', props.task.id), {
        notes: noteContent.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Note added');
            noteContent.value = '';
        },
        onError: () => {
            toast.error('Failed to add note');
        }
    });
};

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('growbiz.tasks.destroy', props.task.id), {
            onSuccess: () => {
                toast.success('Task deleted successfully');
            },
            onError: () => {
                toast.error('Failed to delete task');
            }
        });
    }
};

// Comments are now handled by CommentSection component directly
</script>
