<script setup lang="ts">
import { ref } from 'vue';
import {
    CheckCircleIcon,
    TrashIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';

interface ScheduleBlock {
    id: number;
    title: string;
    description: string | null;
    start_time: string;
    end_time: string;
    color: string;
    category: string;
    is_completed: boolean;
    duration_minutes: number;
    task: any | null;
}

const props = defineProps<{
    block: ScheduleBlock;
}>();

const emit = defineEmits<{
    (e: 'toggle', id: number): void;
    (e: 'delete', id: number): void;
}>();

const showActions = ref(false);

const formatTime = (time: string) => {
    const [hour, minute] = time.split(':').map(Number);
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${period}`;
};

const getCategoryLabel = (category: string) => {
    const labels: Record<string, string> = {
        work: 'Work',
        personal: 'Personal',
        health: 'Health',
        learning: 'Learning',
        social: 'Social',
        other: 'Other',
    };
    return labels[category] || category;
};
</script>

<template>
    <div
        class="rounded-xl p-3 shadow-md border-l-4 transition-all hover:shadow-lg cursor-pointer"
        :style="{ 
            backgroundColor: block.is_completed ? '#f3f4f6' : `${block.color}15`,
            borderLeftColor: block.color,
        }"
        @click="showActions = !showActions"
    >
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 
                        class="font-semibold text-sm truncate"
                        :class="block.is_completed ? 'text-gray-500 line-through' : 'text-gray-900'"
                    >
                        {{ block.title }}
                    </h3>
                </div>
                
                <div class="flex items-center gap-2 text-xs text-gray-600 mb-1">
                    <ClockIcon class="h-3 w-3" aria-hidden="true" />
                    <span>{{ formatTime(block.start_time) }} - {{ formatTime(block.end_time) }}</span>
                </div>
                
                <div class="flex items-center gap-2">
                    <span 
                        class="inline-block px-2 py-0.5 rounded-full text-xs font-medium"
                        :style="{ 
                            backgroundColor: `${block.color}20`,
                            color: block.color,
                        }"
                    >
                        {{ getCategoryLabel(block.category) }}
                    </span>
                    <span v-if="block.task" class="text-xs text-gray-500">
                        📋 Linked to task
                    </span>
                </div>
                
                <p 
                    v-if="block.description" 
                    class="text-xs text-gray-600 mt-2 line-clamp-2"
                >
                    {{ block.description }}
                </p>
            </div>
            
            <button
                @click.stop="emit('toggle', block.id)"
                class="flex-shrink-0 p-1 hover:bg-white/50 rounded-lg transition-colors"
                :aria-label="block.is_completed ? 'Mark as incomplete' : 'Mark as complete'"
            >
                <CheckCircleIconSolid 
                    v-if="block.is_completed"
                    class="h-6 w-6 text-emerald-500" 
                    aria-hidden="true"
                />
                <CheckCircleIcon 
                    v-else
                    class="h-6 w-6 text-gray-400 hover:text-emerald-500" 
                    aria-hidden="true"
                />
            </button>
        </div>
        
        <!-- Actions (shown on click) -->
        <div 
            v-if="showActions" 
            class="mt-3 pt-3 border-t border-gray-200 flex items-center justify-end gap-2"
            @click.stop
        >
            <button
                @click="emit('delete', block.id)"
                class="flex items-center gap-1 px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors text-xs font-medium"
            >
                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                <span>Delete</span>
            </button>
        </div>
    </div>
</template>
