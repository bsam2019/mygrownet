<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ScheduleBlockCard from './ScheduleBlockCard.vue';
import {
    PlusCircleIcon,
} from '@heroicons/vue/24/outline';

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

const props = defineProps<{
    blocks: ScheduleBlock[];
    date: string;
}>();

const emit = defineEmits<{
    (e: 'create-block'): void;
}>();

// Generate time slots (6 AM to 11 PM)
const timeSlots = computed(() => {
    const slots = [];
    for (let hour = 6; hour <= 23; hour++) {
        slots.push({
            hour,
            label: hour === 12 ? '12 PM' : hour > 12 ? `${hour - 12} PM` : `${hour} AM`,
            time: `${hour.toString().padStart(2, '0')}:00`,
        });
    }
    return slots;
});

// Position blocks on timeline
const positionedBlocks = computed(() => {
    return props.blocks.map(block => {
        const [startHour, startMin] = block.start_time.split(':').map(Number);
        const [endHour, endMin] = block.end_time.split(':').map(Number);
        
        // Calculate position from 6 AM (hour 6)
        const startMinutes = (startHour - 6) * 60 + startMin;
        const endMinutes = (endHour - 6) * 60 + endMin;
        const duration = endMinutes - startMinutes;
        
        // Each hour is 80px, so calculate pixel positions
        const top = (startMinutes / 60) * 80;
        const height = (duration / 60) * 80;
        
        return {
            ...block,
            top,
            height,
        };
    });
});

const handleToggle = (blockId: number) => {
    router.post(route('lifeplus.schedule.toggle', blockId), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['schedule', 'stats'] });
        },
    });
};

const handleDelete = (blockId: number) => {
    if (confirm('Delete this schedule block?')) {
        router.delete(route('lifeplus.schedule.destroy', blockId), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['schedule', 'stats'] });
            },
        });
    }
};
</script>

<template>
    <div class="bg-white rounded-3xl shadow-lg p-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-gray-900">Timeline</h2>
            <button
                @click="emit('create-block')"
                class="text-blue-600 text-sm font-semibold hover:text-blue-700"
            >
                + Add Block
            </button>
        </div>

        <!-- Empty State -->
        <div v-if="blocks.length === 0" class="text-center py-12">
            <PlusCircleIcon class="h-16 w-16 text-gray-300 mx-auto mb-3" aria-hidden="true" />
            <p class="text-gray-500 text-sm mb-4">No schedule blocks yet</p>
            <button
                @click="emit('create-block')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                <PlusCircleIcon class="h-5 w-5" aria-hidden="true" />
                <span class="font-semibold">Create Your First Block</span>
            </button>
        </div>

        <!-- Timeline -->
        <div v-else class="relative">
            <!-- Time Labels -->
            <div class="space-y-0">
                <div
                    v-for="slot in timeSlots"
                    :key="slot.hour"
                    class="relative h-20 border-t border-gray-100"
                >
                    <span class="absolute -top-2 left-0 text-xs text-gray-500 font-medium bg-white px-1">
                        {{ slot.label }}
                    </span>
                </div>
            </div>

            <!-- Schedule Blocks -->
            <div class="absolute inset-0 left-16 pointer-events-none">
                <div class="relative h-full pointer-events-auto">
                    <ScheduleBlockCard
                        v-for="block in positionedBlocks"
                        :key="block.id"
                        :block="block"
                        :style="{
                            position: 'absolute',
                            top: `${block.top}px`,
                            height: `${block.height}px`,
                            left: '0',
                            right: '0',
                        }"
                        @toggle="handleToggle"
                        @delete="handleDelete"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
