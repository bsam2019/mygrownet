<script setup lang="ts">
/**
 * Divider Section Preview Component
 */
import { computed } from 'vue';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
}>();

const content = computed(() => props.section.content);
</script>

<template>
    <div
        class="flex items-center justify-center"
        :style="{ height: `${content.height || 40}px` }"
    >
        <!-- Line style -->
        <div
            v-if="content.style === 'line'"
            class="w-full border-t"
            :style="{ borderColor: content.color || '#e5e7eb' }"
        ></div>
        
        <!-- Dots style -->
        <div v-else-if="content.style === 'dots'" class="flex gap-2">
            <span
                v-for="n in 3"
                :key="n"
                class="w-2 h-2 rounded-full"
                :style="{ backgroundColor: content.color || '#e5e7eb' }"
            ></span>
        </div>
        
        <!-- Space style is just empty space (default) -->
    </div>
</template>
