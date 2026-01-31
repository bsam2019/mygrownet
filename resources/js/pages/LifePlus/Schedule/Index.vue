<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import ScheduleTimeline from '@/components/LifePlus/ScheduleTimeline.vue';
import ScheduleStats from '@/components/LifePlus/ScheduleStats.vue';
import CreateScheduleBlockModal from '@/components/LifePlus/CreateScheduleBlockModal.vue';
import {
    PlusIcon,
    CalendarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface ScheduleBlock {
    id: number;
    title: string;
    description: string | null;
    date: string;
    start_time: string;
    end_time: string;
    color: string;
    category: string;
    is_completed: boolean;
    completed_at: string | null;
    duration_minutes: number;
    task: any | null;
}

interface Schedule {
    blocks: ScheduleBlock[];
    date: string;
    total_scheduled_minutes: number;
    completed_blocks: number;
    total_blocks: number;
}

interface Stats {
    today_total: number;
    today_completed: number;
    today_minutes: number;
    today_completed_minutes: number;
}

const props = defineProps<{
    schedule: Schedule;
    stats: Stats;
}>();

const showCreateModal = ref(false);
const selectedDate = ref(props.schedule.date);

const formattedDate = computed(() => {
    const date = new Date(selectedDate.value);
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const isToday = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return selectedDate.value === today;
});

const navigateDate = (direction: 'prev' | 'next') => {
    const date = new Date(selectedDate.value);
    date.setDate(date.getDate() + (direction === 'next' ? 1 : -1));
    selectedDate.value = date.toISOString().split('T')[0];
    
    router.get(route('lifeplus.schedule.index'), { date: selectedDate.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const goToToday = () => {
    selectedDate.value = new Date().toISOString().split('T')[0];
    router.get(route('lifeplus.schedule.index'), { date: selectedDate.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleBlockCreated = () => {
    showCreateModal.value = false;
    router.reload({ only: ['schedule', 'stats'] });
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Day Plan</h1>
                <p class="text-sm text-gray-600 mt-1">{{ formattedDate }}</p>
            </div>
            <button
                @click="showCreateModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:shadow-lg transition-all"
                aria-label="Add schedule block"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                <span class="font-semibold">Add</span>
            </button>
        </div>

        <!-- Date Navigation -->
        <div class="flex items-center justify-between bg-white rounded-2xl p-3 shadow-md">
            <button
                @click="navigateDate('prev')"
                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                aria-label="Previous day"
            >
                <ChevronLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </button>
            
            <button
                v-if="!isToday"
                @click="goToToday"
                class="flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"
            >
                <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                <span class="text-sm font-semibold">Today</span>
            </button>
            <div v-else class="flex items-center gap-2 px-4 py-2">
                <CalendarIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                <span class="text-sm font-semibold text-blue-600">Today</span>
            </div>
            
            <button
                @click="navigateDate('next')"
                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                aria-label="Next day"
            >
                <ChevronRightIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </button>
        </div>

        <!-- Stats -->
        <ScheduleStats :stats="stats" />

        <!-- Timeline -->
        <ScheduleTimeline 
            :blocks="schedule.blocks" 
            :date="selectedDate"
            @create-block="showCreateModal = true"
        />

        <!-- Create Modal -->
        <CreateScheduleBlockModal
            v-if="showCreateModal"
            :date="selectedDate"
            @close="showCreateModal = false"
            @created="handleBlockCreated"
        />
    </div>
</template>
