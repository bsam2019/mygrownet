<script setup lang="ts">
import { computed } from 'vue';
import {
    ClockIcon,
    CheckCircleIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

interface Stats {
    today_total: number;
    today_completed: number;
    today_minutes: number;
    today_completed_minutes: number;
}

const props = defineProps<{
    stats: Stats;
}>();

const completionPercentage = computed(() => {
    if (props.stats.today_total === 0) return 0;
    return Math.round((props.stats.today_completed / props.stats.today_total) * 100);
});

const timePercentage = computed(() => {
    if (props.stats.today_minutes === 0) return 0;
    return Math.round((props.stats.today_completed_minutes / props.stats.today_minutes) * 100);
});

const formatTime = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours === 0) return `${mins}m`;
    if (mins === 0) return `${hours}h`;
    return `${hours}h ${mins}m`;
};
</script>

<template>
    <div class="grid grid-cols-3 gap-3">
        <!-- Total Blocks -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-4 shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <ChartBarIcon class="h-4 w-4 text-white" aria-hidden="true" />
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ stats.today_total }}</p>
            <p class="text-xs text-blue-100 mt-1">Total Blocks</p>
        </div>

        <!-- Completed -->
        <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-4 shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <CheckCircleIcon class="h-4 w-4 text-white" aria-hidden="true" />
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ completionPercentage }}%</p>
            <p class="text-xs text-emerald-100 mt-1">{{ stats.today_completed }}/{{ stats.today_total }} Done</p>
        </div>

        <!-- Time -->
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-4 shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <ClockIcon class="h-4 w-4 text-white" aria-hidden="true" />
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ formatTime(stats.today_minutes) }}</p>
            <p class="text-xs text-purple-100 mt-1">Scheduled</p>
        </div>
    </div>
</template>
