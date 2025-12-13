<script setup lang="ts">
import { computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    CalendarDaysIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

interface Todo {
    id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    due_time: string | null;
    priority: 'low' | 'medium' | 'high';
    status: 'pending' | 'in_progress' | 'completed';
}

interface Props {
    upcomingTodos: Todo[];
    stats: {
        total: number;
        pending: number;
        completed: number;
    };
    priorities: { value: string; label: string }[];
}

const props = defineProps<Props>();

defineOptions({ layout: GrowBizLayout });

const toggleTodo = (todo: Todo) => {
    router.post(route('growbiz.todos.toggle', todo.id), {}, {
        preserveScroll: true,
    });
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'text-red-600 bg-red-50';
        case 'medium': return 'text-amber-600 bg-amber-50';
        case 'low': return 'text-gray-600 bg-gray-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};

// Group todos by date
const groupedTodos = computed(() => {
    const groups: Record<string, Todo[]> = {};
    
    props.upcomingTodos.forEach(todo => {
        const date = todo.due_date || 'No Date';
        if (!groups[date]) {
            groups[date] = [];
        }
        groups[date].push(todo);
    });
    
    return groups;
});

const formatDateHeader = (dateStr: string) => {
    if (dateStr === 'No Date') return 'No Date';
    
    const date = new Date(dateStr);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (date.toDateString() === tomorrow.toDateString()) {
        return 'Tomorrow';
    }
    
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        month: 'short', 
        day: 'numeric' 
    });
};

const formatTime = (time: string | null) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour = h % 12 || 12;
    return `${hour}:${minutes} ${ampm}`;
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
                    <CalendarDaysIcon class="h-6 w-6 text-blue-500" aria-hidden="true" />
                    Upcoming
                </h1>
                <p class="text-sm text-gray-500">Next 7 days</p>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="bg-white rounded-xl p-4 flex items-center justify-between">
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ upcomingTodos.length }}</p>
                <p class="text-sm text-gray-500">upcoming tasks</p>
            </div>
            <Link
                :href="route('growbiz.todos.index')"
                class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg text-sm font-medium"
            >
                View All
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="upcomingTodos.length === 0" class="text-center py-12 bg-white rounded-xl">
            <CalendarDaysIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
            <p class="mt-2 text-gray-500">No upcoming tasks</p>
            <p class="text-sm text-gray-400">Tasks due in the next 7 days will appear here</p>
        </div>

        <!-- Grouped Todos -->
        <div v-else class="space-y-4">
            <div v-for="(todos, date) in groupedTodos" :key="date" class="space-y-2">
                <h2 class="text-sm font-semibold text-gray-600 uppercase px-1">
                    {{ formatDateHeader(date) }}
                </h2>
                <div class="space-y-2">
                    <div
                        v-for="todo in todos"
                        :key="todo.id"
                        :class="[
                            'bg-white rounded-xl p-4 flex items-start gap-3 transition-all',
                            todo.status === 'completed' ? 'opacity-60' : ''
                        ]"
                    >
                        <button
                            @click="toggleTodo(todo)"
                            :class="[
                                'flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors',
                                todo.status === 'completed'
                                    ? 'bg-emerald-500 border-emerald-500'
                                    : 'border-gray-300 hover:border-emerald-500'
                            ]"
                        >
                            <CheckCircleSolid
                                v-if="todo.status === 'completed'"
                                class="h-4 w-4 text-white"
                                aria-hidden="true"
                            />
                        </button>
                        <div class="flex-1 min-w-0">
                            <p :class="[
                                'font-medium',
                                todo.status === 'completed' ? 'line-through text-gray-400' : 'text-gray-900'
                            ]">
                                {{ todo.title }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span v-if="todo.due_time" class="text-xs text-gray-500">
                                    {{ formatTime(todo.due_time) }}
                                </span>
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getPriorityColor(todo.priority)]">
                                    {{ todo.priority }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
