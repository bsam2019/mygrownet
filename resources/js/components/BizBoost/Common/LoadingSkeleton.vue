<script setup lang="ts">
interface Props {
    type?: 'card' | 'list' | 'table' | 'stat' | 'text' | 'avatar';
    count?: number;
    lines?: number;
}

withDefaults(defineProps<Props>(), {
    type: 'card',
    count: 1,
    lines: 3,
});
</script>

<template>
    <!-- Stat Card Skeleton -->
    <template v-if="type === 'stat'">
        <div v-for="i in count" :key="i" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 animate-pulse">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-200"></div>
                <div class="flex-1">
                    <div class="h-3 w-16 bg-slate-200 rounded mb-2"></div>
                    <div class="h-6 w-24 bg-slate-200 rounded"></div>
                </div>
            </div>
            <div class="mt-4 h-6 bg-slate-100 rounded"></div>
        </div>
    </template>

    <!-- Card Skeleton -->
    <template v-else-if="type === 'card'">
        <div v-for="i in count" :key="i" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 animate-pulse">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-xl bg-slate-200"></div>
                <div class="flex-1">
                    <div class="h-4 w-32 bg-slate-200 rounded mb-2"></div>
                    <div class="h-3 w-24 bg-slate-100 rounded"></div>
                </div>
            </div>
            <div class="space-y-2">
                <div v-for="j in lines" :key="j" class="h-3 bg-slate-100 rounded" :style="{ width: `${100 - (j * 15)}%` }"></div>
            </div>
        </div>
    </template>

    <!-- List Skeleton -->
    <template v-else-if="type === 'list'">
        <div class="space-y-3 animate-pulse">
            <div v-for="i in count" :key="i" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                <div class="h-9 w-9 rounded-xl bg-slate-200"></div>
                <div class="flex-1">
                    <div class="h-4 w-32 bg-slate-200 rounded mb-2"></div>
                    <div class="h-3 w-48 bg-slate-100 rounded"></div>
                </div>
                <div class="h-3 w-12 bg-slate-100 rounded"></div>
            </div>
        </div>
    </template>

    <!-- Table Skeleton -->
    <template v-else-if="type === 'table'">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden animate-pulse">
            <div class="bg-slate-50 px-6 py-3 flex gap-4">
                <div class="h-3 w-20 bg-slate-200 rounded"></div>
                <div class="h-3 w-24 bg-slate-200 rounded"></div>
                <div class="h-3 w-16 bg-slate-200 rounded"></div>
                <div class="h-3 w-20 bg-slate-200 rounded"></div>
            </div>
            <div class="divide-y divide-slate-100">
                <div v-for="i in count" :key="i" class="px-6 py-4 flex gap-4">
                    <div class="h-4 w-20 bg-slate-100 rounded"></div>
                    <div class="h-4 w-32 bg-slate-100 rounded"></div>
                    <div class="h-4 w-16 bg-slate-100 rounded"></div>
                    <div class="h-4 w-20 bg-slate-100 rounded"></div>
                </div>
            </div>
        </div>
    </template>

    <!-- Text Skeleton -->
    <template v-else-if="type === 'text'">
        <div class="space-y-2 animate-pulse">
            <div v-for="i in lines" :key="i" class="h-4 bg-slate-200 rounded" :style="{ width: `${100 - (i * 10)}%` }"></div>
        </div>
    </template>

    <!-- Avatar Skeleton -->
    <template v-else-if="type === 'avatar'">
        <div class="flex items-center gap-3 animate-pulse">
            <div class="h-10 w-10 rounded-full bg-slate-200"></div>
            <div>
                <div class="h-4 w-24 bg-slate-200 rounded mb-1"></div>
                <div class="h-3 w-16 bg-slate-100 rounded"></div>
            </div>
        </div>
    </template>
</template>
