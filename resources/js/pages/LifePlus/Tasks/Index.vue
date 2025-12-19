<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import { useLifePlusAccess } from '@/composables/useLifePlusAccess';
import UpgradePrompt from '@/Components/LifePlus/UpgradePrompt.vue';
import {
    PlusIcon,
    CheckCircleIcon,
    TrashIcon,
    FunnelIcon,
    CalendarIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

defineOptions({ layout: LifePlusLayout });

const { hasReachedLimit, getLimit, getRemaining, isFreeTier } = useLifePlusAccess();
const taskLimitReached = computed(() => hasReachedLimit('tasks'));
const taskLimit = computed(() => getLimit('tasks'));
const tasksRemaining = computed(() => getRemaining('tasks'));

interface Task {
    id: number;
    title: string;
    description: string | null;
    priority: string;
    priority_color: string;
    due_date: string | null;
    formatted_due: string | null;
    is_completed: boolean;
    is_overdue: boolean;
    is_today: boolean;
}

interface Stats {
    total: number;
    completed: number;
    pending: number;
    today: number;
    overdue: number;
    completion_rate: number;
}

const props = defineProps<{
    tasks: Task[];
    stats: Stats;
    filters: Record<string, any>;
}>();

const showAddModal = ref(false);
const activeTab = ref<'all' | 'today' | 'completed'>('all');

const form = useForm({
    title: '',
    description: '',
    priority: 'medium',
    due_date: '',
    due_time: '',
});

const filteredTasks = computed(() => {
    if (activeTab.value === 'today') {
        return props.tasks.filter(t => t.is_today && !t.is_completed);
    }
    if (activeTab.value === 'completed') {
        return props.tasks.filter(t => t.is_completed);
    }
    return props.tasks.filter(t => !t.is_completed);
});

const toggleTask = (id: number) => {
    router.post(route('lifeplus.tasks.toggle', id), {}, {
        preserveScroll: true,
    });
};

const deleteTask = (id: number) => {
    if (confirm('Delete this task?')) {
        router.delete(route('lifeplus.tasks.destroy', id), {
            preserveScroll: true,
        });
    }
};

const submitTask = () => {
    form.post(route('lifeplus.tasks.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

const getPriorityLabel = (priority: string) => {
    return { low: 'Low', medium: 'Medium', high: 'High' }[priority] || priority;
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
        <!-- Header with Gradient -->
        <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Tasks</h1>
                    <p class="text-sm text-blue-100 mt-1">Stay organized and productive ✓</p>
                </div>
                <button 
                    v-if="!taskLimitReached"
                    @click="showAddModal = true"
                    class="flex items-center gap-2 px-5 py-2.5 bg-white/20 backdrop-blur-sm text-white rounded-xl font-semibold hover:bg-white/30 transition-all shadow-md"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Task
                </button>
                <div v-else class="flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm text-white/70 rounded-xl text-sm">
                    <LockClosedIcon class="h-4 w-4" aria-hidden="true" />
                    Limit ({{ taskLimit }})
                </div>
            </div>
        </div>
        
        <div class="p-4 space-y-6">
        
        <!-- Task Limit Warning for Free Users -->
        <div v-if="isFreeTier && tasksRemaining !== null && tasksRemaining <= 3" class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                    <LockClosedIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-amber-900">{{ tasksRemaining }} tasks remaining</p>
                    <p class="text-sm text-amber-700">Upgrade to Premium for unlimited tasks</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 gap-2">
            <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl p-3 text-center shadow-lg border border-gray-200">
                <p class="text-2xl font-bold text-gray-900">{{ stats.pending }}</p>
                <p class="text-xs text-gray-500">Pending</p>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 text-center shadow-lg border border-blue-200">
                <p class="text-2xl font-bold text-blue-700">{{ stats.today }}</p>
                <p class="text-xs text-blue-600">Today</p>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-3 text-center shadow-lg border border-red-200">
                <p class="text-2xl font-bold text-red-700">{{ stats.overdue }}</p>
                <p class="text-xs text-red-600">Overdue</p>
            </div>
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-3 text-center shadow-lg border border-emerald-200">
                <p class="text-2xl font-bold text-emerald-700">{{ stats.completed }}</p>
                <p class="text-xs text-emerald-600">Done</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 bg-gradient-to-r from-gray-100 to-gray-200 p-1 rounded-xl shadow-inner">
            <button 
                @click="activeTab = 'all'"
                :class="[
                    'flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all',
                    activeTab === 'all' ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg' : 'text-gray-600 hover:bg-white/50'
                ]"
            >
                All
            </button>
            <button 
                @click="activeTab = 'today'"
                :class="[
                    'flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all',
                    activeTab === 'today' ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg' : 'text-gray-600 hover:bg-white/50'
                ]"
            >
                Today
            </button>
            <button 
                @click="activeTab = 'completed'"
                :class="[
                    'flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all',
                    activeTab === 'completed' ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg' : 'text-gray-600 hover:bg-white/50'
                ]"
            >
                Completed
            </button>
        </div>

        <!-- Task List -->
        <div class="space-y-2">
            <div v-if="filteredTasks.length === 0" class="text-center py-12 bg-gradient-to-br from-white to-blue-50 rounded-3xl border border-blue-200">
                <CheckCircleIcon class="h-12 w-12 text-blue-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">
                    {{ activeTab === 'completed' ? 'No completed tasks' : 'No tasks yet' }}
                </p>
                <button 
                    v-if="activeTab !== 'completed'"
                    @click="showAddModal = true"
                    class="mt-3 text-blue-600 font-semibold hover:text-blue-700"
                >
                    Add your first task
                </button>
            </div>

            <div 
                v-for="task in filteredTasks" 
                :key="task.id"
                class="bg-gradient-to-br from-white to-gray-50 rounded-xl p-4 shadow-lg border border-gray-200 flex items-start gap-3 group hover:shadow-xl hover:scale-[1.02] transition-all"
            >
                <button 
                    @click="toggleTask(task.id)"
                    class="mt-0.5 flex-shrink-0"
                    :aria-label="task.is_completed ? 'Mark as incomplete' : 'Mark as complete'"
                >
                    <component 
                        :is="task.is_completed ? CheckCircleSolid : CheckCircleIcon"
                        :class="[
                            'h-6 w-6 transition-colors',
                            task.is_completed ? 'text-emerald-500' : 'text-gray-300 hover:text-emerald-400'
                        ]"
                        aria-hidden="true"
                    />
                </button>
                
                <div class="flex-1 min-w-0">
                    <p 
                        :class="[
                            'font-medium',
                            task.is_completed ? 'text-gray-400 line-through' : 'text-gray-900'
                        ]"
                    >
                        {{ task.title }}
                    </p>
                    <p v-if="task.description" class="text-sm text-gray-500 mt-0.5 truncate">
                        {{ task.description }}
                    </p>
                    <div class="flex items-center gap-2 mt-2">
                        <span 
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                            :style="{ backgroundColor: task.priority_color + '20', color: task.priority_color }"
                        >
                            {{ getPriorityLabel(task.priority) }}
                        </span>
                        <span 
                            v-if="task.formatted_due"
                            :class="[
                                'inline-flex items-center gap-1 text-xs',
                                task.is_overdue ? 'text-red-600' : 'text-gray-500'
                            ]"
                        >
                            <CalendarIcon class="h-3.5 w-3.5" aria-hidden="true" />
                            {{ task.formatted_due }}
                        </span>
                    </div>
                </div>
                
                <button 
                    @click="deleteTask(task.id)"
                    class="p-2 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-red-50 transition-all"
                    aria-label="Delete task"
                >
                    <TrashIcon class="h-4 w-4 text-red-500" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Add Task Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center">
                    <div 
                        class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom"
                        @click.stop
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Add Task</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitTask" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
                                <input 
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="What needs to be done?"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                                <textarea 
                                    v-model="form.description"
                                    rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Add more details..."
                                ></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                    <select 
                                        v-model="form.priority"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                    <input 
                                        v-model="form.due_date"
                                        type="date"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Adding...' : 'Add Task' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
        </div>
    </div>
</template>
