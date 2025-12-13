<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    EllipsisVerticalIcon,
    CalendarDaysIcon,
    UserGroupIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    XMarkIcon,
    CheckIcon,
    ClockIcon,
    FlagIcon,
} from '@heroicons/vue/24/outline';

interface Assignee {
    id: number;
    name: string;
    initials: string;
}

interface Task {
    id: number;
    title: string;
    description: string | null;
    priority: string;
    status: string;
    due_date: string | null;
    is_overdue: boolean;
    progress_percentage: number;
    assignees: Assignee[];
    subtask_count: number;
    completed_subtask_count: number;
    tags: string[];
    kanban_order: number;
}

interface Column {
    id: number;
    name: string;
    color: string;
    wip_limit: number | null;
    is_done_column: boolean;
    task_count: number;
    tasks: Task[];
}

interface Project {
    id: number;
    name: string;
    color: string;
    status: string;
    progress_percentage: number;
}

const props = defineProps<{
    project: Project;
    columns: Column[];
}>();

const draggedTask = ref<Task | null>(null);
const draggedFromColumn = ref<number | null>(null);
const showAddTask = ref<number | null>(null);
const showColumnMenu = ref<number | null>(null);
const showAddColumn = ref(false);
const isSubmitting = ref(false);

const newTask = ref({
    title: '',
    priority: 'medium',
});

const newColumn = ref({
    name: '',
    color: '#6b7280',
});

// Drag and Drop
const onDragStart = (task: Task, columnId: number) => {
    draggedTask.value = task;
    draggedFromColumn.value = columnId;
};

const onDragOver = (e: DragEvent) => {
    e.preventDefault();
};

const onDrop = (columnId: number, position: number = 0) => {
    if (!draggedTask.value) return;

    router.post(route('growbiz.projects.kanban.move', props.project.id), {
        task_id: draggedTask.value.id,
        column_id: columnId,
        position: position,
    }, {
        preserveScroll: true,
        onFinish: () => {
            draggedTask.value = null;
            draggedFromColumn.value = null;
        },
    });
};

const onDragEnd = () => {
    draggedTask.value = null;
    draggedFromColumn.value = null;
};

// Add Task
const addTask = (columnId: number) => {
    if (!newTask.value.title.trim() || isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.tasks.store'), {
        title: newTask.value.title,
        priority: newTask.value.priority,
        project_id: props.project.id,
        kanban_column_id: columnId,
        status: 'pending',
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newTask.value = { title: '', priority: 'medium' };
            showAddTask.value = null;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

// Add Column
const addColumn = () => {
    if (!newColumn.value.name.trim() || isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.projects.columns.store', props.project.id), newColumn.value, {
        preserveScroll: true,
        onSuccess: () => {
            newColumn.value = { name: '', color: '#6b7280' };
            showAddColumn.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

// Delete Column
const deleteColumn = (columnId: number) => {
    if (!confirm('Delete this column? Tasks will be moved to the first column.')) return;
    router.delete(route('growbiz.projects.columns.destroy', [props.project.id, columnId]), {
        preserveScroll: true,
    });
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'urgent': return 'text-red-600';
        case 'high': return 'text-orange-500';
        case 'medium': return 'text-yellow-500';
        default: return 'text-gray-400';
    }
};

const getPriorityBg = (priority: string) => {
    switch (priority) {
        case 'urgent': return 'bg-red-50 border-red-200';
        case 'high': return 'bg-orange-50 border-orange-200';
        default: return 'bg-white border-gray-200';
    }
};
</script>

<template>
    <GrowBizLayout>
        <div class="h-full flex flex-col">
            <!-- Header -->
            <div class="flex-shrink-0 p-4 bg-white border-b border-gray-200">
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
                            <p class="text-sm text-gray-500">{{ project.progress_percentage }}% complete</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('growbiz.projects.gantt', project.id)"
                            class="p-2 hover:bg-gray-100 rounded-lg"
                            title="Gantt Chart"
                            aria-label="View Gantt chart"
                        >
                            <ChartBarIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </Link>
                        <Link
                            :href="route('growbiz.projects.show', project.id)"
                            class="p-2 hover:bg-gray-100 rounded-lg"
                            title="Settings"
                            aria-label="Project settings"
                        >
                            <Cog6ToothIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Kanban Board -->
            <div class="flex-1 overflow-x-auto p-4">
                <div class="flex gap-4 h-full min-w-max">
                    <!-- Columns -->
                    <div
                        v-for="column in columns"
                        :key="column.id"
                        class="w-72 flex-shrink-0 flex flex-col bg-gray-50 rounded-xl"
                        @dragover="onDragOver"
                        @drop="onDrop(column.id, column.tasks.length)"
                    >
                        <!-- Column Header -->
                        <div class="flex items-center justify-between p-3 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: column.color }"></div>
                                <span class="font-medium text-gray-900">{{ column.name }}</span>
                                <span class="text-xs text-gray-400 bg-gray-200 px-1.5 py-0.5 rounded">
                                    {{ column.task_count }}
                                    <span v-if="column.wip_limit">/{{ column.wip_limit }}</span>
                                </span>
                            </div>
                            <div class="relative">
                                <button
                                    @click="showColumnMenu = showColumnMenu === column.id ? null : column.id"
                                    class="p-1 hover:bg-gray-200 rounded"
                                    aria-label="Column options"
                                >
                                    <EllipsisVerticalIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                                </button>
                                <div
                                    v-if="showColumnMenu === column.id"
                                    class="absolute right-0 top-full mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10"
                                >
                                    <button
                                        @click="deleteColumn(column.id); showColumnMenu = null"
                                        class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                    >
                                        Delete Column
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tasks -->
                        <div class="flex-1 overflow-y-auto p-2 space-y-2">
                            <div
                                v-for="(task, index) in column.tasks"
                                :key="task.id"
                                draggable="true"
                                @dragstart="onDragStart(task, column.id)"
                                @dragend="onDragEnd"
                                @dragover="onDragOver"
                                @drop.stop="onDrop(column.id, index)"
                                class="bg-white rounded-lg border p-3 cursor-move hover:shadow-sm transition-shadow"
                                :class="[
                                    getPriorityBg(task.priority),
                                    { 'opacity-50': draggedTask?.id === task.id }
                                ]"
                            >
                                <Link :href="route('growbiz.tasks.show', task.id)" class="block">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <h4 class="text-sm font-medium text-gray-900 line-clamp-2">{{ task.title }}</h4>
                                        <FlagIcon
                                            v-if="task.priority !== 'low'"
                                            class="h-4 w-4 flex-shrink-0"
                                            :class="getPriorityColor(task.priority)"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <!-- Tags -->
                                    <div v-if="task.tags.length > 0" class="flex flex-wrap gap-1 mb-2">
                                        <span
                                            v-for="tag in task.tags.slice(0, 3)"
                                            :key="tag"
                                            class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-xs"
                                        >
                                            {{ tag }}
                                        </span>
                                    </div>

                                    <!-- Progress -->
                                    <div v-if="task.progress_percentage > 0" class="mb-2">
                                        <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-blue-500 rounded-full"
                                                :style="{ width: `${task.progress_percentage}%` }"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <span v-if="task.due_date" class="flex items-center gap-1" :class="{ 'text-red-500': task.is_overdue }">
                                                <CalendarDaysIcon class="h-3 w-3" aria-hidden="true" />
                                                {{ task.due_date }}
                                            </span>
                                            <span v-if="task.subtask_count > 0" class="flex items-center gap-1">
                                                <CheckIcon class="h-3 w-3" aria-hidden="true" />
                                                {{ task.completed_subtask_count }}/{{ task.subtask_count }}
                                            </span>
                                        </div>
                                        <div v-if="task.assignees.length > 0" class="flex -space-x-1">
                                            <div
                                                v-for="assignee in task.assignees.slice(0, 3)"
                                                :key="assignee.id"
                                                class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-medium border border-white"
                                                :title="assignee.name"
                                            >
                                                {{ assignee.initials }}
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <!-- Add Task Form -->
                            <div v-if="showAddTask === column.id" class="bg-white rounded-lg border border-blue-200 p-3">
                                <input
                                    v-model="newTask.title"
                                    type="text"
                                    placeholder="Task title..."
                                    class="w-full text-sm border-0 p-0 focus:ring-0 placeholder-gray-400"
                                    @keyup.enter="addTask(column.id)"
                                    @keyup.escape="showAddTask = null"
                                    autofocus
                                />
                                <div class="flex items-center justify-between mt-2">
                                    <select v-model="newTask.priority" class="text-xs border-gray-200 rounded py-1">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    <div class="flex items-center gap-1">
                                        <button
                                            @click="showAddTask = null"
                                            class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            @click="addTask(column.id)"
                                            :disabled="!newTask.title.trim() || isSubmitting"
                                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                                        >
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <div class="p-2 border-t border-gray-200">
                            <button
                                v-if="showAddTask !== column.id"
                                @click="showAddTask = column.id"
                                class="w-full flex items-center justify-center gap-1 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg"
                            >
                                <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                Add Task
                            </button>
                        </div>
                    </div>

                    <!-- Add Column -->
                    <div class="w-72 flex-shrink-0">
                        <div v-if="showAddColumn" class="bg-gray-50 rounded-xl p-3">
                            <input
                                v-model="newColumn.name"
                                type="text"
                                placeholder="Column name..."
                                class="w-full text-sm border-gray-200 rounded-lg mb-2"
                                @keyup.enter="addColumn"
                                @keyup.escape="showAddColumn = false"
                                autofocus
                            />
                            <div class="flex items-center justify-between">
                                <button
                                    @click="showAddColumn = false"
                                    class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="addColumn"
                                    :disabled="!newColumn.name.trim() || isSubmitting"
                                    class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Add Column
                                </button>
                            </div>
                        </div>
                        <button
                            v-else
                            @click="showAddColumn = true"
                            class="w-full flex items-center justify-center gap-2 py-4 text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl border-2 border-dashed border-gray-300"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add Column
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
