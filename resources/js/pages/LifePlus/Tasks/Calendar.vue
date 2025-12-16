<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Task {
    id: number;
    title: string;
    priority: string;
    due_date: string | null;
    is_completed: boolean;
}

const props = defineProps<{
    tasks: Task[];
    month: string;
}>();

const currentDate = ref(new Date(props.month + '-01'));

const monthName = computed(() => {
    return currentDate.value.toLocaleDateString('en', { month: 'long', year: 'numeric' });
});

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days = [];

    // Add empty slots for days before the first day of month
    for (let i = 0; i < firstDay.getDay(); i++) {
        days.push(null);
    }

    // Add all days of the month
    for (let d = 1; d <= lastDay.getDate(); d++) {
        days.push(new Date(year, month, d));
    }

    return days;
});

const getTasksForDate = (date: Date | null) => {
    if (!date) return [];
    const dateStr = date.toISOString().split('T')[0];
    return props.tasks.filter(t => t.due_date?.startsWith(dateStr));
};

const isToday = (date: Date | null) => {
    if (!date) return false;
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

const prevMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    router.get(route('lifeplus.tasks.calendar'), {
        month: newDate.toISOString().slice(0, 7),
    }, { preserveState: true });
};

const nextMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    router.get(route('lifeplus.tasks.calendar'), {
        month: newDate.toISOString().slice(0, 7),
    }, { preserveState: true });
};

const selectedDate = ref<Date | null>(null);
const selectedTasks = computed(() => selectedDate.value ? getTasksForDate(selectedDate.value) : []);

const selectDate = (date: Date | null) => {
    if (date) {
        selectedDate.value = date;
    }
};

const getPriorityColor = (priority: string) => {
    return {
        high: 'bg-red-500',
        medium: 'bg-amber-500',
        low: 'bg-emerald-500',
    }[priority] || 'bg-gray-400';
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.tasks.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to tasks"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">Calendar</h1>
        </div>

        <!-- Month Navigation -->
        <div class="flex items-center justify-between bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <button 
                @click="prevMonth"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Previous month"
            >
                <ChevronLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </button>
            <h2 class="text-lg font-semibold text-gray-900">{{ monthName }}</h2>
            <button 
                @click="nextMonth"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Next month"
            >
                <ChevronRightIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </button>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-1 mb-2">
                <div 
                    v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                    :key="day"
                    class="text-center text-xs font-medium text-gray-500 py-2"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Days -->
            <div class="grid grid-cols-7 gap-1">
                <button 
                    v-for="(date, index) in daysInMonth" 
                    :key="index"
                    @click="selectDate(date)"
                    :disabled="!date"
                    :class="[
                        'aspect-square rounded-xl flex flex-col items-center justify-center relative transition-colors',
                        date ? 'hover:bg-gray-100' : '',
                        isToday(date) ? 'bg-blue-100 text-blue-700 font-bold' : '',
                        selectedDate?.toDateString() === date?.toDateString() ? 'ring-2 ring-blue-500' : '',
                    ]"
                >
                    <span v-if="date" class="text-sm">{{ date.getDate() }}</span>
                    <!-- Task indicators -->
                    <div v-if="date && getTasksForDate(date).length > 0" class="flex gap-0.5 mt-0.5">
                        <span 
                            v-for="(task, i) in getTasksForDate(date).slice(0, 3)" 
                            :key="i"
                            class="w-1.5 h-1.5 rounded-full"
                            :class="getPriorityColor(task.priority)"
                        ></span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Selected Date Tasks -->
        <div v-if="selectedDate" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-900">
                    {{ selectedDate.toLocaleDateString('en', { weekday: 'long', month: 'short', day: 'numeric' }) }}
                </h3>
                <Link 
                    :href="route('lifeplus.tasks.index')"
                    class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                    aria-label="Add task"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                </Link>
            </div>

            <div v-if="selectedTasks.length === 0" class="text-center py-6 text-gray-500">
                No tasks for this day
            </div>

            <div v-else class="space-y-2">
                <div 
                    v-for="task in selectedTasks" 
                    :key="task.id"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl"
                >
                    <span 
                        class="w-2 h-2 rounded-full flex-shrink-0"
                        :class="getPriorityColor(task.priority)"
                    ></span>
                    <span 
                        :class="[
                            'flex-1 text-sm',
                            task.is_completed ? 'line-through text-gray-400' : 'text-gray-900'
                        ]"
                    >
                        {{ task.title }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
