<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    ViewColumnsIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CalendarDaysIcon,
    FlagIcon,
} from '@heroicons/vue/24/outline';

interface Task {
    id: number;
    name: string;
    start: string;
    end: string;
    progress: number;
    status: string;
    priority: string;
    assignees: string[];
    dependencies: { task_id: number; type: string; lag: number }[];
    milestone_id: number | null;
    parent_id: number | null;
}

interface Milestone {
    id: number;
    name: string;
    date: string | null;
    status: string;
}

interface Project {
    id: number;
    name: string;
    color: string;
    progress_percentage: number;
}

const props = defineProps<{
    project: Project;
    tasks: Task[];
    milestones: Milestone[];
    startDate: string | null;
    endDate: string | null;
}>();

// View settings
const viewMode = ref<'day' | 'week' | 'month'>('week');
const viewStartDate = ref(new Date());

// Calculate date range
const dateRange = computed(() => {
    const dates: Date[] = [];
    const start = new Date(viewStartDate.value);
    
    let daysToShow = viewMode.value === 'day' ? 14 : viewMode.value === 'week' ? 12 : 6;
    let increment = viewMode.value === 'day' ? 1 : viewMode.value === 'week' ? 7 : 30;
    
    for (let i = 0; i < daysToShow; i++) {
        const date = new Date(start);
        date.setDate(date.getDate() + (i * increment));
        dates.push(date);
    }
    
    return dates;
});

const formatDateHeader = (date: Date) => {
    if (viewMode.value === 'day') {
        return date.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' });
    } else if (viewMode.value === 'week') {
        return `Week ${getWeekNumber(date)}`;
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
    }
};

const getWeekNumber = (date: Date) => {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d.getTime() - yearStart.getTime()) / 86400000) + 1) / 7);
};

// Calculate task bar position and width
const getTaskBarStyle = (task: Task) => {
    const rangeStart = dateRange.value[0];
    const rangeEnd = dateRange.value[dateRange.value.length - 1];
    const totalDays = Math.ceil((rangeEnd.getTime() - rangeStart.getTime()) / (1000 * 60 * 60 * 24));
    
    const taskStart = new Date(task.start);
    const taskEnd = new Date(task.end);
    
    const startOffset = Math.max(0, Math.ceil((taskStart.getTime() - rangeStart.getTime()) / (1000 * 60 * 60 * 24)));
    const duration = Math.ceil((taskEnd.getTime() - taskStart.getTime()) / (1000 * 60 * 60 * 24)) + 1;
    
    const left = (startOffset / totalDays) * 100;
    const width = Math.min((duration / totalDays) * 100, 100 - left);
    
    return {
        left: `${Math.max(0, left)}%`,
        width: `${Math.max(2, width)}%`,
    };
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'bg-green-500';
        case 'in_progress': return 'bg-blue-500';
        case 'on_hold': return 'bg-yellow-500';
        default: return 'bg-gray-400';
    }
};

const getPriorityIcon = (priority: string) => {
    switch (priority) {
        case 'urgent': return 'text-red-500';
        case 'high': return 'text-orange-500';
        default: return 'text-gray-300';
    }
};

const navigateDate = (direction: number) => {
    const newDate = new Date(viewStartDate.value);
    const increment = viewMode.value === 'day' ? 7 : viewMode.value === 'week' ? 28 : 90;
    newDate.setDate(newDate.getDate() + (direction * increment));
    viewStartDate.value = newDate;
};

const goToToday = () => {
    viewStartDate.value = new Date();
};

onMounted(() => {
    // Set initial view to project start date or today
    if (props.startDate) {
        viewStartDate.value = new Date(props.startDate);
    }
});
</script>

<template>
    <GrowBizLayout>
        <div class="h-full flex flex-col bg-white">
            <!-- Header -->
            <div class="flex-shrink-0 p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <Link :href="route('growbiz.projects.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back to projects">
                            <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: project.color }"></div>
                                <h1 class="text-lg font-bold text-gray-900">{{ project.name }}</h1>
                            </div>
                            <p class="text-sm text-gray-500">Gantt Chart</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('growbiz.projects.kanban', project.id)"
                            class="p-2 hover:bg-gray-100 rounded-lg"
                            title="Kanban Board"
                            aria-label="View Kanban board"
                        >
                            <ViewColumnsIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </Link>
                    </div>
                </div>

                <!-- View Controls -->
                <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center gap-2">
                        <button
                            @click="navigateDate(-1)"
                            class="p-2 hover:bg-gray-100 rounded-lg"
                            aria-label="Previous period"
                        >
                            <ChevronLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </button>
                        <button
                            @click="goToToday"
                            class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg"
                        >
                            Today
                        </button>
                        <button
                            @click="navigateDate(1)"
                            class="p-2 hover:bg-gray-100 rounded-lg"
                            aria-label="Next period"
                        >
                            <ChevronRightIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </button>
                    </div>
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button
                            @click="viewMode = 'day'"
                            class="px-3 py-1 text-sm rounded-md transition-colors"
                            :class="viewMode === 'day' ? 'bg-white shadow text-gray-900' : 'text-gray-600'"
                        >
                            Day
                        </button>
                        <button
                            @click="viewMode = 'week'"
                            class="px-3 py-1 text-sm rounded-md transition-colors"
                            :class="viewMode === 'week' ? 'bg-white shadow text-gray-900' : 'text-gray-600'"
                        >
                            Week
                        </button>
                        <button
                            @click="viewMode = 'month'"
                            class="px-3 py-1 text-sm rounded-md transition-colors"
                            :class="viewMode === 'month' ? 'bg-white shadow text-gray-900' : 'text-gray-600'"
                        >
                            Month
                        </button>
                    </div>
                </div>
            </div>

            <!-- Gantt Chart -->
            <div class="flex-1 overflow-auto">
                <div class="min-w-[800px]">
                    <!-- Timeline Header -->
                    <div class="flex border-b border-gray-200 bg-gray-50 sticky top-0 z-10">
                        <div class="w-64 flex-shrink-0 p-3 border-r border-gray-200 font-medium text-gray-700">
                            Task
                        </div>
                        <div class="flex-1 flex">
                            <div
                                v-for="date in dateRange"
                                :key="date.toISOString()"
                                class="flex-1 p-2 text-center text-xs text-gray-500 border-r border-gray-100"
                            >
                                {{ formatDateHeader(date) }}
                            </div>
                        </div>
                    </div>

                    <!-- Milestones -->
                    <div v-if="milestones.length > 0" class="border-b border-gray-200 bg-purple-50">
                        <div class="flex items-center">
                            <div class="w-64 flex-shrink-0 p-3 border-r border-gray-200">
                                <span class="text-sm font-medium text-purple-700">Milestones</span>
                            </div>
                            <div class="flex-1 relative h-10">
                                <div
                                    v-for="milestone in milestones.filter(m => m.date)"
                                    :key="milestone.id"
                                    class="absolute top-1/2 -translate-y-1/2"
                                    :style="getTaskBarStyle({ start: milestone.date!, end: milestone.date!, progress: 0, status: milestone.status } as any)"
                                >
                                    <div class="flex items-center gap-1">
                                        <div class="w-3 h-3 bg-purple-500 rotate-45"></div>
                                        <span class="text-xs text-purple-700 whitespace-nowrap">{{ milestone.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks -->
                    <div v-if="tasks.length === 0" class="p-8 text-center text-gray-500">
                        <CalendarDaysIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                        <p>No tasks in this project yet</p>
                        <Link :href="route('growbiz.projects.kanban', project.id)" class="mt-2 inline-block text-blue-600 font-medium">
                            Add tasks in Kanban view
                        </Link>
                    </div>

                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="flex items-center border-b border-gray-100 hover:bg-gray-50"
                    >
                        <!-- Task Name -->
                        <div class="w-64 flex-shrink-0 p-3 border-r border-gray-200">
                            <Link :href="route('growbiz.tasks.show', task.id)" class="flex items-center gap-2 group">
                                <FlagIcon
                                    v-if="task.priority === 'urgent' || task.priority === 'high'"
                                    class="h-4 w-4 flex-shrink-0"
                                    :class="getPriorityIcon(task.priority)"
                                    aria-hidden="true"
                                />
                                <span class="text-sm text-gray-900 group-hover:text-blue-600 truncate">
                                    {{ task.name }}
                                </span>
                            </Link>
                            <div v-if="task.assignees.length > 0" class="text-xs text-gray-400 mt-0.5 truncate">
                                {{ task.assignees.join(', ') }}
                            </div>
                        </div>

                        <!-- Task Bar -->
                        <div class="flex-1 relative h-12 py-2">
                            <!-- Grid lines -->
                            <div class="absolute inset-0 flex">
                                <div
                                    v-for="(_, index) in dateRange"
                                    :key="index"
                                    class="flex-1 border-r border-gray-100"
                                ></div>
                            </div>

                            <!-- Task bar -->
                            <div
                                class="absolute top-1/2 -translate-y-1/2 h-6 rounded-full overflow-hidden"
                                :class="getStatusColor(task.status)"
                                :style="getTaskBarStyle(task)"
                            >
                                <!-- Progress fill -->
                                <div
                                    class="h-full bg-white/30"
                                    :style="{ width: `${task.progress}%` }"
                                ></div>
                                <!-- Label -->
                                <span class="absolute inset-0 flex items-center justify-center text-xs text-white font-medium px-2 truncate">
                                    {{ task.progress }}%
                                </span>
                            </div>

                            <!-- Dependencies (simplified arrows) -->
                            <template v-for="dep in task.dependencies" :key="`${task.id}-${dep.task_id}`">
                                <!-- Dependency lines would be drawn here with SVG in a full implementation -->
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex-shrink-0 p-3 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-center gap-6 text-xs text-gray-500">
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                        <span>Pending</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <span>In Progress</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <span>On Hold</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span>Completed</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-purple-500 rotate-45"></div>
                        <span>Milestone</span>
                    </div>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
