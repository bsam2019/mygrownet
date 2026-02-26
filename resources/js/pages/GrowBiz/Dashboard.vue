<template>
    <GrowBizLayout>
        <PullToRefresh ref="pullToRefreshRef" @refresh="handleRefresh">
            <div class="px-4 py-4 pb-6">
                <!-- Welcome Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Good {{ greeting }}! 👋</h1>
                    <p class="text-gray-500 mt-1">
                        {{ businessProfile?.business_name || 'Your business' }} overview
                    </p>
                </div>

                <!-- Stats Overview - App Style Cards -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div 
                        class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-4 text-white shadow-lg shadow-blue-500/20 active:scale-95 transition-transform"
                        @click="router.visit(route('growbiz.tasks.index'))"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-white/20 rounded-xl">
                                <ClipboardDocumentListIcon class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <span class="text-2xl font-bold">{{ taskStats.total }}</span>
                        </div>
                        <p class="text-sm text-blue-100">Total Tasks</p>
                    </div>
                    
                    <div 
                        class="stat-card bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-4 text-white shadow-lg shadow-amber-500/20 active:scale-95 transition-transform"
                        @click="router.visit(route('growbiz.tasks.index', { status: 'in_progress' }))"
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
                        class="stat-card bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg shadow-emerald-500/20 active:scale-95 transition-transform"
                        @click="router.visit(route('growbiz.tasks.index', { status: 'completed' }))"
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
                        class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-4 text-white shadow-lg shadow-purple-500/20 active:scale-95 transition-transform"
                        @click="router.visit(route('growbiz.employees.index'))"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-white/20 rounded-xl">
                                <UsersIcon class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <span class="text-2xl font-bold">{{ employeeStats.total }}</span>
                        </div>
                        <p class="text-sm text-purple-100">Team Members</p>
                    </div>
                </div>

                <!-- Quick Actions - App Style -->
                <div class="grid grid-cols-4 gap-2 mb-6">
                    <button 
                        @click="router.visit(route('growbiz.tasks.create'))"
                        class="quick-action bg-white rounded-2xl shadow-sm p-3 flex flex-col items-center justify-center gap-1.5 active:scale-95 active:bg-gray-50 transition-all"
                    >
                        <div class="p-2 bg-blue-100 rounded-xl">
                            <PlusIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-900">Task</span>
                    </button>
                    
                    <button 
                        @click="router.visit(route('growbiz.projects.index'))"
                        class="quick-action bg-white rounded-2xl shadow-sm p-3 flex flex-col items-center justify-center gap-1.5 active:scale-95 active:bg-gray-50 transition-all"
                    >
                        <div class="p-2 bg-indigo-100 rounded-xl">
                            <ViewColumnsIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-900">Projects</span>
                    </button>
                    
                    <button 
                        @click="router.visit(route('growbiz.inventory.index'))"
                        class="quick-action bg-white rounded-2xl shadow-sm p-3 flex flex-col items-center justify-center gap-1.5 active:scale-95 active:bg-gray-50 transition-all"
                    >
                        <div class="p-2 bg-teal-100 rounded-xl">
                            <CubeIcon class="h-5 w-5 text-teal-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-900">Inventory</span>
                    </button>
                    
                    <button 
                        @click="router.visit(route('growbiz.appointments.index'))"
                        class="quick-action bg-white rounded-2xl shadow-sm p-3 flex flex-col items-center justify-center gap-1.5 active:scale-95 active:bg-gray-50 transition-all"
                    >
                        <div class="p-2 bg-pink-100 rounded-xl">
                            <CalendarDaysIcon class="h-5 w-5 text-pink-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-900">Book</span>
                    </button>
                </div>
                
                <!-- Secondary Quick Actions -->
                <div class="grid grid-cols-3 gap-2 mb-6">
                    <button 
                        @click="router.visit(route('growbiz.todos.index'))"
                        class="quick-action bg-white rounded-xl shadow-sm p-3 flex items-center gap-2 active:scale-95 active:bg-gray-50 transition-all"
                    >
                        <div class="p-1.5 bg-emerald-100 rounded-lg">
                            <ListBulletIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-700">My To-Do</span>
                    </button>
                    
                    <button 
                        @click="router.visit(route('growbiz.employees.index'))"
                        class="quick-action bg-white rounded-xl shadow-sm p-3 flex items-center gap-2 active:scale-95 active:bg-gray-50 transition-all"
                    >
                        <div class="p-1.5 bg-purple-100 rounded-lg">
                            <UsersIcon class="h-4 w-4 text-purple-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-700">Team</span>
                    </button>
                    
                    <button 
                        @click="router.visit(route('growbiz.reports.analytics'))"
                        class="quick-action bg-white rounded-xl shadow-sm p-3 flex items-center gap-2 active:scale-95 active:bg-gray-50 transition-all"
                    >
                        <div class="p-1.5 bg-amber-100 rounded-lg">
                            <ChartBarIcon class="h-4 w-4 text-amber-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-700">Reports</span>
                    </button>
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

                <!-- Task Lists -->
                <div class="space-y-4">
                    <!-- Upcoming Tasks -->
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Upcoming Tasks</h3>
                            <button 
                                @click="router.visit(route('growbiz.tasks.index'))"
                                class="text-sm text-emerald-600 font-medium active:text-emerald-700"
                            >
                                View All
                            </button>
                        </div>
                        <ul v-if="upcomingTasks.length > 0" class="divide-y divide-gray-100">
                            <li v-for="task in upcomingTasks" :key="`upcoming-${task.id}`">
                                <button 
                                    @click="router.visit(route('growbiz.tasks.show', task.id))"
                                    class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                                >
                                    <div class="flex items-center justify-between">
                                        <p class="font-medium text-gray-900 truncate pr-2">{{ task.title }}</p>
                                        <StatusBadge v-if="task.status" :status="task.status" size="sm" />
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">{{ task.due_date || 'No due date' }}</p>
                                </button>
                            </li>
                        </ul>
                        <div v-else class="p-8 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                                <ClipboardDocumentListIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                            </div>
                            <p class="text-gray-500 text-sm">No upcoming tasks</p>
                        </div>
                    </div>

                    <!-- Team Overview - Compact -->
                    <div class="bg-white rounded-2xl shadow-sm p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Team Overview</h3>
                            <button 
                                @click="router.visit(route('growbiz.employees.index'))"
                                class="text-sm text-emerald-600 font-medium active:text-emerald-700"
                            >
                                Manage
                            </button>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <div class="text-center p-2 bg-emerald-50 rounded-xl">
                                <p class="text-lg font-bold text-emerald-600">{{ employeeStats.active }}</p>
                                <p class="text-xs text-gray-500">Active</p>
                            </div>
                            <div class="text-center p-2 bg-gray-50 rounded-xl">
                                <p class="text-lg font-bold text-gray-500">{{ employeeStats.inactive }}</p>
                                <p class="text-xs text-gray-500">Inactive</p>
                            </div>
                            <div class="text-center p-2 bg-amber-50 rounded-xl">
                                <p class="text-lg font-bold text-amber-500">{{ employeeStats.on_leave }}</p>
                                <p class="text-xs text-gray-500">Leave</p>
                            </div>
                            <div class="text-center p-2 bg-blue-50 rounded-xl">
                                <p class="text-lg font-bold text-blue-600">{{ taskStats.completion_rate }}%</p>
                                <p class="text-xs text-gray-500">Done</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Recent Activity</h3>
                        </div>
                        <ul v-if="recentTasks.length > 0" class="divide-y divide-gray-100">
                            <li v-for="task in recentTasks" :key="`recent-${task.id}`">
                                <button 
                                    @click="router.visit(route('growbiz.tasks.show', task.id))"
                                    class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                                >
                                    <div class="flex items-center justify-between">
                                        <p class="font-medium text-gray-900 truncate pr-2">{{ task.title }}</p>
                                        <StatusBadge v-if="task.status" :status="task.status" size="sm" />
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">{{ task.created_at }}</p>
                                </button>
                            </li>
                        </ul>
                        <div v-else class="p-8 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                                <ClipboardDocumentListIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                            </div>
                            <p class="text-gray-500 text-sm mb-2">No tasks yet</p>
                            <button 
                                @click="router.visit(route('growbiz.tasks.create'))"
                                class="text-emerald-600 text-sm font-medium"
                            >
                                Create your first task
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </PullToRefresh>
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import PullToRefresh from '@/Components/GrowBiz/PullToRefresh.vue';
import StatusBadge from '@/Components/GrowBiz/StatusBadge.vue';
import PriorityBadge from '@/Components/GrowBiz/PriorityBadge.vue';
import { 
    ClipboardDocumentListIcon, 
    ClockIcon, 
    CheckCircleIcon, 
    UsersIcon,
    PlusIcon,
    UserPlusIcon,
    ExclamationTriangleIcon,
    ListBulletIcon,
    ViewColumnsIcon,
    CubeIcon,
    CalendarDaysIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

interface Task {
    id: number;
    title: string;
    status: string;
    priority: string;
    due_date: string | null;
    created_at: string;
}

interface BusinessProfile {
    id: number;
    business_name: string | null;
    industry: string | null;
}

interface Props {
    userRole: string;
    businessProfile: BusinessProfile | null;
    taskStats: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        overdue: number;
        completion_rate: number;
    };
    employeeStats: {
        total: number;
        active: number;
        inactive: number;
        on_leave: number;
    };
    recentTasks: Task[];
    upcomingTasks: Task[];
    overdueTasks: Task[];
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
.stat-card,
.quick-action {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    cursor: pointer;
}
</style>
