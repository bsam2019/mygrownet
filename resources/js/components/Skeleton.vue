<script setup lang="ts">
withDefaults(defineProps<{
    class?: string;
    lines?: number;
    width?: string;
}>(), {
    class: '',
    lines: 1,
    width: '100%',
});
</script>

<template>
    <div v-if="lines === 1" :class="['animate-pulse bg-gray-200 dark:bg-white/[0.06] rounded', class]" :style="{ width }"></div>
    <div v-else :class="['space-y-2', class]">
        <div
            v-for="i in lines"
            :key="i"
            class="animate-pulse bg-gray-200 dark:bg-white/[0.06] rounded h-3"
            :style="{ width: i === lines ? '60%' : width }"
        ></div>
    </div>
</template>
