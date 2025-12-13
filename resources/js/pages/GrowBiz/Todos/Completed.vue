<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    CheckBadgeIcon,
    ArrowLeftIcon,
    ArrowPathIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

interface Todo {
    id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    priority: 'low' | 'medium' | 'high';
    status: 'pending' | 'in_progress' | 'completed';
    completed_at: string | null;
}

interface Props {
    completedTodos: Todo[];
    stats: {
        total: number;
        completed: number;
        completion_rate: number;
    };
}

const props = defineProps<Props>();

defineOptions({ layout: GrowBizLayout });

const reopenTodo = (todo: Todo) => {
    router.post(route('growbiz.todos.toggle', todo.id), {}, {
        preserveScroll: true,
    });
};

const deleteTodo = (todo: Todo) => {
    if (!confirm('Delete this completed todo?')) return;
    router.delete(route('growbiz.todos.destroy', todo.id), {
        preserveScroll: true,
    });
};

const formatCompletedDate = (dateStr: string | null) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'text-red-600 bg-red-50';
        case 'medium': return 'text-amber-600 bg-amber-50';
        case 'low': return 'text-gray-600 bg-gray-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link :href="route('growbiz.todos.index')" class="p-2 -ml-2 rounded-xl hover:bg-gray-100">
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <CheckBadgeIcon class="h-6 w-6 text-emerald-500" aria-hidden="true" />
                    Completed
                </h1>
                <p class="text-sm text-gray-500">{{ completedTodos.length }} tasks done</p>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold">{{ stats.completed }}</p>
                    <p class="text-emerald-100">tasks completed</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold">{{ stats.completion_rate }}%</p>
                    <p class="text-emerald-100">completion rate</p>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="completedTodos.length === 0" class="text-center py-12 bg-white rounded-xl">
            <CheckBadgeIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
            <p class="mt-2 text-gray-500">No completed tasks yet</p>
            <p class="text-sm text-gray-400">Complete some tasks to see them here</p>
        </div>

        <!-- Completed Todos List -->
        <div v-else class="space-y-2">
            <div
                v-for="todo in completedTodos"
                :key="todo.id"
                class="bg-white rounded-xl p-4 flex items-start gap-3 opacity-75 hover:opacity-100 transition-opacity"
            >
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center">
                    <CheckCircleSolid class="h-4 w-4 text-white" aria-hidden="true" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-500 line-through">{{ todo.title }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-gray-400">
                            Completed {{ formatCompletedDate(todo.completed_at) }}
                        </span>
                        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium opacity-50', getPriorityColor(todo.priority)]">
                            {{ todo.priority }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        @click="reopenTodo(todo)"
                        class="p-2 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-amber-50"
                        title="Reopen task"
                    >
                        <ArrowPathIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <button
                        @click="deleteTodo(todo)"
                        class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                        title="Delete task"
                    >
                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
