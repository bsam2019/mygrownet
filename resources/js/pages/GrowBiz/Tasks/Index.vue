<template>
    <GrowBizLayout>
        <PullToRefresh ref="pullToRefreshRef" @refresh="handleRefresh">
            <div class="px-4 py-4 pb-6">
                <!-- Floating Action Button (only for owners/supervisors) -->
                <FloatingActionButton 
                    v-if="canCreateTasks"
                    @click="router.visit(route('growbiz.tasks.create'))"
                />
                
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Tasks</h1>
                        <p class="text-sm text-gray-500">Manage your team's work</p>
                    </div>
                    <Link 
                        v-if="canCreateTasks"
                        :href="route('growbiz.tasks.create')" 
                        class="hidden sm:inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 active:bg-emerald-800 transition-colors shadow-sm"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        New Task
                    </Link>
                </div>

                <!-- Stats - Horizontal Scroll -->
                <div class="mb-6 -mx-4 px-4">
                    <div class="flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory">
                        <button 
                            @click="setStatusFilter('')"
                            :class="[
                                'stat-chip snap-start flex-shrink-0 min-w-[90px] p-3 rounded-2xl text-center transition-all',
                                !localFilters.status ? 'bg-gray-900 text-white shadow-lg' : 'bg-white shadow-sm active:scale-95'
                            ]"
                        >
                            <p class="text-xl font-bold">{{ stats.total }}</p>
                            <p class="text-xs opacity-70">Total</p>
                        </button>
                        <button 
                            @click="setStatusFilter('pending')"
                            :class="[
                                'stat-chip snap-start flex-shrink-0 min-w-[90px] p-3 rounded-2xl text-center transition-all',
                                localFilters.status === 'pending' ? 'bg-gray-600 text-white shadow-lg' : 'bg-white shadow-sm active:scale-95'
                            ]"
                        >
                            <p class="text-xl font-bold" :class="localFilters.status === 'pending' ? '' : 'text-gray-600'">{{ stats.pending }}</p>
                            <p class="text-xs opacity-70">Pending</p>
                        </button>
                        <button 
                            @click="setStatusFilter('in_progress')"
                            :class="[
                                'stat-chip snap-start flex-shrink-0 min-w-[90px] p-3 rounded-2xl text-center transition-all',
                                localFilters.status === 'in_progress' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-white shadow-sm active:scale-95'
                            ]"
                        >
                            <p class="text-xl font-bold" :class="localFilters.status === 'in_progress' ? '' : 'text-blue-600'">{{ stats.in_progress }}</p>
                            <p class="text-xs opacity-70">Progress</p>
                        </button>
                        <button 
                            @click="setStatusFilter('completed')"
                            :class="[
                                'stat-chip snap-start flex-shrink-0 min-w-[90px] p-3 rounded-2xl text-center transition-all',
                                localFilters.status === 'completed' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-white shadow-sm active:scale-95'
                            ]"
                        >
                            <p class="text-xl font-bold" :class="localFilters.status === 'completed' ? '' : 'text-emerald-600'">{{ stats.completed }}</p>
                            <p class="text-xs opacity-70">Done</p>
                        </button>
                        <button 
                            @click="setStatusFilter('overdue')"
                            :class="[
                                'stat-chip snap-start flex-shrink-0 min-w-[90px] p-3 rounded-2xl text-center transition-all',
                                localFilters.status === 'overdue' ? 'bg-red-600 text-white shadow-lg shadow-red-500/30' : 'bg-white shadow-sm active:scale-95'
                            ]"
                        >
                            <p class="text-xl font-bold" :class="localFilters.status === 'overdue' ? '' : 'text-red-600'">{{ stats.overdue }}</p>
                            <p class="text-xs opacity-70">Overdue</p>
                        </button>
                    </div>
                </div>

                <!-- Filter Button -->
                <button 
                    @click="showFilters = true"
                    class="w-full mb-4 flex items-center justify-between p-4 bg-white rounded-2xl shadow-sm active:bg-gray-50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <FunnelIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <span class="text-gray-700">Filters & Search</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-if="activeFilterCount > 0" class="bg-emerald-100 text-emerald-700 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ activeFilterCount }}
                        </span>
                        <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </div>
                </button>

                <!-- Tasks List -->
                <SkeletonLoader v-if="isLoading" type="list" :count="5" />
                <div v-else-if="tasks.length > 0" class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <TransitionGroup name="list" tag="ul" class="divide-y divide-gray-100">
                        <SwipeableListItem 
                            v-for="task in tasks" 
                            :key="`task-${task.id}`"
                            @view="router.visit(route('growbiz.tasks.show', task.id))"
                            @edit="router.visit(route('growbiz.tasks.edit', task.id))"
                            @delete="confirmDelete(task)"
                        >
                            <div class="p-4 bg-white">
                                <div class="flex items-start gap-3">
                                    <div :class="[
                                        'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0',
                                        task.is_overdue ? 'bg-red-100' : 
                                        task.status === 'completed' ? 'bg-emerald-100' :
                                        task.status === 'in_progress' ? 'bg-blue-100' : 'bg-gray-100'
                                    ]">
                                        <ClipboardDocumentListIcon :class="[
                                            'h-5 w-5',
                                            task.is_overdue ? 'text-red-600' : 
                                            task.status === 'completed' ? 'text-emerald-600' :
                                            task.status === 'in_progress' ? 'text-blue-600' : 'text-gray-500'
                                        ]" aria-hidden="true" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-900 truncate">{{ task.title }}</p>
                                            <PriorityBadge v-if="task.priority" :priority="task.priority" size="sm" />
                                        </div>
                                        <p v-if="task.due_date" :class="['text-sm mt-1', task.is_overdue ? 'text-red-600 font-medium' : 'text-gray-500']">
                                            {{ task.is_overdue ? '⚠️ Overdue: ' : 'Due: ' }}{{ task.due_date }}
                                        </p>
                                        <p v-if="task.description" class="text-sm text-gray-400 truncate mt-1">
                                            {{ task.description }}
                                        </p>
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
                                    </div>
                                    <StatusBadge v-if="task.status" :status="task.status" size="sm" />
                                </div>
                            </div>
                        </SwipeableListItem>
                    </TransitionGroup>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <ClipboardDocumentListIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>
                    <p class="text-gray-500 mb-6">
                        {{ canCreateTasks ? 'Get started by creating your first task.' : 'No tasks have been assigned to you yet.' }}
                    </p>
                    <HapticButton v-if="canCreateTasks" @click="router.visit(route('growbiz.tasks.create'))">
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Create Task
                    </HapticButton>
                </div>
            </div>
        </PullToRefresh>

        <!-- Mobile Filter Bottom Sheet -->
        <BottomSheet v-model="showFilters" title="Filters">
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        v-model="localFilters.search" 
                        type="text" 
                        placeholder="Search tasks..."
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="localFilters.priority = ''"
                            :class="[
                                'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                localFilters.priority === '' 
                                    ? 'bg-emerald-600 text-white' 
                                    : 'bg-gray-100 text-gray-700 active:bg-gray-200'
                            ]"
                        >
                            All
                        </button>
                        <button 
                            v-for="priority in priorities" 
                            :key="priority.value"
                            @click="localFilters.priority = priority.value"
                            :class="[
                                'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                localFilters.priority === priority.value 
                                    ? 'bg-emerald-600 text-white' 
                                    : 'bg-gray-100 text-gray-700 active:bg-gray-200'
                            ]"
                        >
                            {{ priority.label }}
                        </button>
                    </div>
                </div>
                <div class="pt-4 flex justify-between gap-3">
                    <button 
                        @click="clearFilters(); showFilters = false"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 rounded-xl font-medium active:bg-gray-200 transition-colors"
                    >
                        Clear All
                    </button>
                    <button 
                        @click="applyFilters(); showFilters = false"
                        class="flex-1 px-4 py-3 text-white bg-emerald-600 rounded-xl font-medium active:bg-emerald-700 transition-colors"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </BottomSheet>
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import StatusBadge from '@/Components/GrowBiz/StatusBadge.vue';
import PriorityBadge from '@/Components/GrowBiz/PriorityBadge.vue';
import PullToRefresh from '@/Components/GrowBiz/PullToRefresh.vue';
import BottomSheet from '@/Components/GrowBiz/BottomSheet.vue';
import SwipeableListItem from '@/Components/GrowBiz/SwipeableListItem.vue';
import SkeletonLoader from '@/Components/GrowBiz/SkeletonLoader.vue';
import FloatingActionButton from '@/Components/GrowBiz/FloatingActionButton.vue';
import HapticButton from '@/Components/GrowBiz/HapticButton.vue';
import { PlusIcon, ChevronRightIcon, ClipboardDocumentListIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import { useDebounceFn } from '@vueuse/core';
import { useToast } from '@/composables/useToast';

interface Task {
    id: number;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    due_date: string | null;
    is_overdue: boolean;
    progress_percentage: number;
    actual_hours: number;
    created_at: string;
}

interface Props {
    tasks: Task[];
    stats: { total: number; pending: number; in_progress: number; completed: number; overdue: number; };
    filters: { status: string | null; priority: string | null; search: string | null; };
    statuses: Array<{ value: string; label: string }>;
    priorities: Array<{ value: string; label: string }>;
    userRole?: string;
}

const props = defineProps<Props>();

// Check if user can create tasks (owner or supervisor)
const canCreateTasks = computed(() => props.userRole !== 'employee');

const { toast } = useToast();
const showFilters = ref(false);
const isLoading = ref(false);
const pullToRefreshRef = ref<InstanceType<typeof PullToRefresh> | null>(null);

const localFilters = reactive({
    status: props.filters.status || '',
    priority: props.filters.priority || '',
    search: props.filters.search || '',
});

const activeFilterCount = computed(() => {
    let count = 0;
    if (localFilters.priority) count++;
    if (localFilters.search) count++;
    return count;
});

const setStatusFilter = (status: string) => {
    localFilters.status = status;
    applyFilters();
};

const applyFilters = () => {
    router.get(route('growbiz.tasks.index'), {
        status: localFilters.status || undefined,
        priority: localFilters.priority || undefined,
        search: localFilters.search || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const debouncedFilter = useDebounceFn(applyFilters, 300);

const clearFilters = () => {
    localFilters.status = '';
    localFilters.priority = '';
    localFilters.search = '';
    router.get(route('growbiz.tasks.index'));
};

const handleRefresh = () => {
    router.reload({
        onFinish: () => {
            pullToRefreshRef.value?.finishRefresh();
        }
    });
};

const confirmDelete = (task: Task) => {
    if (confirm(`Are you sure you want to delete "${task.title}"?`)) {
        router.delete(route('growbiz.tasks.destroy', task.id), {
            onSuccess: () => {
                toast.success('Task deleted successfully');
            },
            onError: () => {
                toast.error('Failed to delete task');
            }
        });
    }
};
</script>

<style scoped>
/* List animations */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

.list-move {
    transition: transform 0.3s ease;
}

.stat-chip {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

/* Hide scrollbar but keep functionality */
.overflow-x-auto {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.overflow-x-auto::-webkit-scrollbar {
    display: none;
}
</style>
