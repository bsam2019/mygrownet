<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    PlusIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    CalendarIcon,
    SunIcon,
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
    is_overdue: boolean;
}

interface Props {
    todayTodos: Todo[];
    overdueTodos: Todo[];
    stats: {
        total: number;
        pending: number;
        completed: number;
        overdue: number;
        due_today: number;
    };
    priorities: { value: string; label: string }[];
}

const props = defineProps<Props>();

defineOptions({ layout: GrowBizLayout });

const showQuickAdd = ref(false);
const quickAddTitle = ref('');

const toggleTodo = (todo: Todo) => {
    router.post(route('growbiz.todos.toggle', todo.id), {}, {
        preserveScroll: true,
    });
};

const quickAdd = () => {
    if (!quickAddTitle.value.trim()) return;
    
    const today = new Date().toISOString().split('T')[0];
    router.post(route('growbiz.todos.store'), {
        title: quickAddTitle.value,
        due_date: today,
        priority: 'medium',
    }, {
        preserveScroll: true,
        onSuccess: () => {
            quickAddTitle.value = '';
            showQuickAdd.value = false;
        },
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

const formatTime = (time: string | null) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour = h % 12 || 12;
    return `${hour}:${minutes} ${ampm}`;
};

const todayDate = new Date().toLocaleDateString('en-US', { 
    weekday: 'long', 
    month: 'long', 
    day: 'numeric' 
});
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
                    <SunIcon class="h-6 w-6 text-amber-500" aria-hidden="true" />
                    Today
                </h1>
                <p class="text-sm text-gray-500">{{ todayDate }}</p>
            </div>
        </div>

        <!-- Quick Add -->
        <div class="bg-white rounded-xl p-4">
            <div v-if="!showQuickAdd" class="flex items-center justify-between">
                <span class="text-gray-500">{{ props.todayTodos.length }} tasks for today</span>
                <button
                    @click="showQuickAdd = true"
                    class="flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-sm font-medium"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    Quick Add
                </button>
            </div>
            <form v-else @submit.prevent="quickAdd" class="flex gap-2">
                <input
                    v-model="quickAddTitle"
                    type="text"
                    placeholder="Add a task for today..."
                    autofocus
                    class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
                <button
                    type="submit"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-medium"
                >
                    Add
                </button>
                <button
                    type="button"
                    @click="showQuickAdd = false; quickAddTitle = ''"
                    class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-medium"
                >
                    Cancel
                </button>
            </form>
        </div>

        <!-- Overdue Section -->
        <div v-if="overdueTodos.length > 0" class="space-y-2">
            <h2 class="flex items-center gap-2 text-sm font-semibold text-red-600 uppercase">
                <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                Overdue ({{ overdueTodos.length }})
            </h2>
            <div class="space-y-2">
                <div
                    v-for="todo in overdueTodos"
                    :key="todo.id"
                    class="bg-white rounded-xl p-4 flex items-start gap-3 ring-2 ring-red-100"
                >
                    <button
                        @click="toggleTodo(todo)"
                        class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-red-300 hover:border-red-500 flex items-center justify-center transition-colors"
                    >
                    </button>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900">{{ todo.title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-red-600 font-medium">
                                Was due {{ new Date(todo.due_date!).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
                            </span>
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getPriorityColor(todo.priority)]">
                                {{ todo.priority }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Tasks -->
        <div class="space-y-2">
            <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-600 uppercase">
                <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                Today's Tasks
            </h2>

            <div v-if="todayTodos.length === 0" class="text-center py-8 bg-white rounded-xl">
                <SunIcon class="h-12 w-12 mx-auto text-amber-300" aria-hidden="true" />
                <p class="mt-2 text-gray-500">No tasks scheduled for today</p>
                <button
                    @click="showQuickAdd = true"
                    class="mt-3 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium"
                >
                    Add a task
                </button>
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="todo in todayTodos"
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

        <!-- Progress -->
        <div class="bg-white rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Today's Progress</span>
                <span class="text-sm text-gray-500">{{ stats.completion_rate }}%</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                    :style="{ width: `${stats.completion_rate}%` }"
                />
            </div>
        </div>
    </div>
</template>
