<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import {
    ListBulletIcon,
    ClockIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Task {
    id: number;
    title: string;
    priority: string;
    status: string;
    due_date: string;
    assigner?: { full_name: string };
}

interface Props {
    tasksByStatus: {
        todo?: Task[];
        in_progress?: Task[];
        review?: Task[];
        completed?: Task[];
    };
}

const props = defineProps<Props>();

const columns = [
    { key: 'todo', label: 'To Do', color: 'bg-gray-500' },
    { key: 'in_progress', label: 'In Progress', color: 'bg-blue-500' },
    { key: 'review', label: 'Review', color: 'bg-purple-500' },
    { key: 'completed', label: 'Completed', color: 'bg-green-500' },
];

const draggedTask = ref<Task | null>(null);
const dragOverColumn = ref<string | null>(null);

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        'low': 'border-l-gray-400',
        'medium': 'border-l-blue-400',
        'high': 'border-l-amber-400',
        'urgent': 'border-l-red-400',
    };
    return colors[priority] || 'border-l-gray-400';
};

const isOverdue = (task: Task) => {
    if (!task.due_date || ['completed', 'cancelled'].includes(task.status)) return false;
    return new Date(task.due_date) < new Date();
};

const onDragStart = (task: Task) => {
    draggedTask.value = task;
};

const onDragOver = (e: DragEvent, columnKey: string) => {
    e.preventDefault();
    dragOverColumn.value = columnKey;
};

const onDragLeave = () => {
    dragOverColumn.value = null;
};

const onDrop = (columnKey: string) => {
    if (draggedTask.value && draggedTask.value.status !== columnKey) {
        router.patch(route('employee.portal.tasks.update-status', draggedTask.value.id), {
            status: columnKey,
        }, { preserveScroll: true });
    }
    draggedTask.value = null;
    dragOverColumn.value = null;
};

const getTasksForColumn = (columnKey: string): Task[] => {
    return props.tasksByStatus[columnKey as keyof typeof props.tasksByStatus] || [];
};
</script>

<template>
    <Head title="Task Board" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Task Board</h1>
                    <p class="text-gray-500 mt-1">Drag and drop tasks to update their status</p>
                </div>
                <Link :href="route('employee.portal.tasks.index')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <ListBulletIcon class="h-5 w-5 mr-2 text-gray-500" aria-hidden="true" />
                    List View
                </Link>
            </div>

            <!-- Kanban Board -->
            <div class="flex gap-4 overflow-x-auto pb-4">
                <div v-for="column in columns" :key="column.key"
                    class="flex-shrink-0 w-80"
                    @dragover="onDragOver($event, column.key)"
                    @dragleave="onDragLeave"
                    @drop="onDrop(column.key)">
                    
                    <!-- Column Header -->
                    <div class="flex items-center gap-2 mb-3">
                        <div :class="column.color" class="w-3 h-3 rounded-full"></div>
                        <h3 class="font-semibold text-gray-900">{{ column.label }}</h3>
                        <span class="text-sm text-gray-500">
                            ({{ getTasksForColumn(column.key).length }})
                        </span>
                    </div>

                    <!-- Column Content -->
                    <div :class="[
                        'bg-gray-100 rounded-xl p-3 min-h-[500px] transition-colors',
                        dragOverColumn === column.key ? 'bg-blue-50 ring-2 ring-blue-300' : ''
                    ]">
                        <div class="space-y-3">
                            <div v-for="task in getTasksForColumn(column.key)" :key="task.id"
                                draggable="true"
                                @dragstart="onDragStart(task)"
                                :class="[
                                    'bg-white rounded-lg p-4 shadow-sm border-l-4 cursor-move hover:shadow-md transition-shadow',
                                    getPriorityColor(task.priority)
                                ]">
                                <Link :href="route('employee.portal.tasks.show', task.id)"
                                    class="font-medium text-gray-900 hover:text-blue-600 block">
                                    {{ task.title }}
                                </Link>

                                <div class="flex items-center justify-between mt-3">
                                    <span :class="[
                                        'px-2 py-0.5 text-xs font-medium rounded-full',
                                        task.priority === 'urgent' ? 'bg-red-100 text-red-700' :
                                        task.priority === 'high' ? 'bg-amber-100 text-amber-700' :
                                        task.priority === 'medium' ? 'bg-blue-100 text-blue-700' :
                                        'bg-gray-100 text-gray-700'
                                    ]">
                                        {{ task.priority }}
                                    </span>

                                    <div v-if="task.due_date" 
                                        class="flex items-center gap-1 text-xs"
                                        :class="isOverdue(task) ? 'text-red-600' : 'text-gray-500'">
                                        <ClockIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                        {{ new Date(task.due_date).toLocaleDateString() }}
                                        <ExclamationTriangleIcon v-if="isOverdue(task)" class="h-3.5 w-3.5" aria-hidden="true" />
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="getTasksForColumn(column.key).length === 0"
                                class="text-center py-8 text-gray-400 text-sm">
                                No tasks
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
