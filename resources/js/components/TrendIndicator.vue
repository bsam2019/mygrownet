<template>
    <div class="flex items-center" :class="[textClass]">
        <component 
            :is="value >= 0 ? ArrowUpIcon : ArrowDownIcon" 
            class="w-4 h-4 mr-1"
        />
        <span class="text-sm font-medium">
            {{ value >= 0 ? '+' : '' }}{{ value }}%
        </span>
        <span v-if="showBadge" class="ml-2 px-2 py-0.5 text-xs rounded-full" :class="badgeClass">
            {{ value >= 0 ? 'Up' : 'Down' }}
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    value: {
        type: Number,
        required: true
    },
    showBadge: {
        type: Boolean,
        default: false
    }
});

const textClass = computed(() => {
    return props.value >= 0 ? 'text-green-600' : 'text-red-600';
});

const badgeClass = computed(() => {
    return props.value >= 0 
        ? 'bg-green-100 text-green-800' 
        : 'bg-red-100 text-red-800';
});
</script>
