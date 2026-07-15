<script setup lang="ts">
interface Props {
    rows?: number;
    cols?: number;
    type?: 'table' | 'card' | 'stats';
}

withDefaults(defineProps<Props>(), {
    rows: 5,
    cols: 4,
    type: 'table',
});
</script>

<template>
    <!-- Table skeleton -->
    <div v-if="type === 'table'" class="animate-pulse">
        <div class="mb-4 flex gap-4">
            <div v-for="c in cols" :key="c" class="h-4 flex-1 rounded bg-gray-200" />
        </div>
        <div v-for="r in rows" :key="r" class="mb-3 flex gap-4">
            <div v-for="c in cols" :key="c" class="h-3 flex-1 rounded bg-gray-100" />
        </div>
    </div>

    <!-- Card skeleton -->
    <div v-else-if="type === 'card'" class="grid gap-4" :class="cols > 1 ? 'sm:grid-cols-2 lg:grid-cols-3' : ''">
        <div v-for="r in rows" :key="r" class="animate-pulse rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="mb-3 h-4 w-2/3 rounded bg-gray-200" />
            <div class="mb-2 h-3 w-1/2 rounded bg-gray-100" />
            <div class="h-3 w-3/4 rounded bg-gray-100" />
        </div>
    </div>

    <!-- Stats skeleton -->
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="r in rows" :key="r" class="animate-pulse rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="mb-2 h-3 w-1/3 rounded bg-gray-200" />
            <div class="h-6 w-1/2 rounded bg-gray-100" />
        </div>
    </div>
</template>
