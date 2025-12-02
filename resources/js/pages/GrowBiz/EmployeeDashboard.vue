<template>
    <GrowBizLayout>
        <PullToRefresh ref="pullToRefreshRef" @refresh="handleRefresh">
            <div class="px-4 py-4 pb-6">
                <!-- Welcome Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Good {{ greeting }}! 👋</h1>
                    <p class="text-gray-500 mt-1">{{ employee.position || 'Team Member' }} • {{ employee.department || 'General' }}</p>
                </div>

                <!-- Employee Info Card -->
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                            <UserIcon class="h-7 w-7" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-semibold text-lg">{{ employee.name }}</p>
                            <p class="text-emerald-100 text-sm">Reports to: {{ employee.manager_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div 
                        class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-4 text-white shadow-lg shadow-blue-500/20"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-white/20 rounded-xl">
                                <ClipboardDocumentListIcon class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <span class="text-2xl font-bold">{{ taskStats.total }}</span>
                        </div>
                        <p class="text-sm text-blue-100">My Tasks</p>
                    </div>
                    
                    <div 
                        class="stat-card bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-4 text-white shadow-lg shadow-amber-500/20"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-white/20 rounded-xl">
                                <ClockIcon class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <span class="text-2xl font-bold">{{ taskStats.in_progress }}</span>
                        </div>
                        <p class="text-sm text-amber-100">In Progress</p>
                    </div>

                    <div 
                        class="stat-card bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg shadow-emerald-500/20"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-white/20 rounded-xl">
                                <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <span class="text-2xl font-bold">{{ taskStats.completed }}</span>
                        </div>
                        <p class="text-sm text-emerald-100">Completed</p>
                    </div>
                    
                    <div 
                        class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-4 text-white shadow-lg shadow-purple-500/20"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-white/20 rounded-xl">
                                <ChartBarIcon class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <span class="text-2xl font-bold">{{ taskStats.completion_rate }}%</span>
                        </div>
                        <p class="text-sm text-purple-100">Completion</p>
                    </div>
                </div>

                <!-- Overdue Tasks Alert -->
                <div v-if="overdueTasks.length > 0" class="mb-6">
                    <div class="bg-gradient-to-r from-red-500 to-rose-500 rounded-2xl p-4 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <ExclamationTriangleIcon class="h-5 w-5" aria-hidden="true" />
                                <span class="font-semibold">Overdue Tasks</span>
                            </div>
                            <span class="bg-white/20 text-xs font-medium px-2 py-1 rounded-full">
                                {{ overdueTasks.length }} tasks
                            </span>
                        </div>
                        <div class="space-y-2">
                            <button 
                                v-for="task in overdueTasks.slice(0, 3)" 
                                :key="`overdue-${task.id}`"
                                @click="router.visit(route('growbiz.tasks.show', task.id))"
                                class="w-full bg-white/10 rounded-xl p-3 text-left active:bg-white/20 transition-colors"
                            >
                                <p class="font-medium truncate">{{ task.title }}</p>
                                <p class="text-sm text-red-100">Due: {{ task.due_date }}</p>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- My Active Tasks -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">My Active Tasks</h3>
                    </div>
                    <ul v-if="myTasks.length > 0" class="divide-y divide-gray-100">
                        <li v-for="task in myTasks" :key="`task-${task.id}`">
                            <button 
                                @click="router.visit(route('growbiz.tasks.show', task.id))"
                                class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-medium text-gray-900 truncate pr-2">{{ task.title }}</p>
                                    <StatusBadge v-if="task.status" :status="task.status" size="sm" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-500">{{ task.due_date || 'No due date' }}</p>
                                    <PriorityBadge v-if="task.priority" :priority="task.priority" size="sm" />
                                </div>
                                <!-- Progress Bar -->
                                <div v-if="task.progress_percentage !== undefined" class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full rounded-full transition-all duration-300"
                                                :class="task.progress_percentage >= 100 ? 'bg-emerald-500' : task.progress_percentage >= 50 ? 'bg-blue-500' : 'bg-gray-400'"
                                                :style="{ width: `${task.progress_percentage}%` }"
                                            />
                                        </div>
                                        <span class="text-xs text-gray-500 w-8">{{ task.progress_percentage }}%</span>
                                    </div>
                                </div>
                            </button>
                        </li>
                    </ul>
                    <div v-else class="p-8 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-emerald-100 flex items-center justify-center">
                            <CheckCircleIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                        </div>
                        <p class="text-gray-500 text-sm">No active tasks - you're all caught up!</p>
                    </div>
                </div>

                <!-- Recently Completed -->
                <div v-if="completedTasks.length > 0" class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Recently Completed</h3>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        <li v-for="task in completedTasks" :key="`completed-${task.id}`">
                            <button 
                                @click="router.visit(route('growbiz.tasks.show', task.id))"
                                class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <CheckCircleIcon class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                                        <p class="font-medium text-gray-900 truncate">{{ task.title }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ task.completed_at }}</span>
                                </div>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </PullToRefresh>
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import PullToRefresh from '@/Components/GrowBiz/PullToRefresh.vue';
import StatusBadge from '@/Components/GrowBiz/StatusBadge.vue';
import PriorityBadge from '@/Components/GrowBiz/PriorityBadge.vue';
import { 
    ClipboardDocumentListIcon, 
    ClockIcon, 
    CheckCircleIcon, 
    ChartBarIcon,
    UserIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

interface Task {
    id: number;
    title: string;
    status: string;
    priority: string;
    due_date: string | null;
    completed_at: string | null;
    progress_percentage: number;
}

interface Props {
    userRole: string;
    employee: {
        id: number;
        name: string;
        position: string | null;
        department: string | null;
        manager_name: string;
    };
    taskStats: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        overdue: number;
        completion_rate: number;
    };
    myTasks: Task[];
    completedTasks: Task[];
    overdueTasks: Task[];
    error?: string;
}

defineProps<Props>();

const pullToRefreshRef = ref<InstanceType<typeof PullToRefresh> | null>(null);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'morning';
    if (hour < 17) return 'afternoon';
    return 'evening';
});

const handleRefresh = () => {
    router.reload({
        onFinish: () => {
            pullToRefreshRef.value?.finishRefresh();
        }
    });
};
</script>

<style scoped>
.stat-card {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
</style>
