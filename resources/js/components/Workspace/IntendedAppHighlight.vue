<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface WorkspaceContext {
    type: string;
    application_id: number | null;
}

const page = usePage();
const context = computed<WorkspaceContext | null>(() => (page.props as any).workspace?.context ?? null);

const showHighlight = computed(() => context.value?.application_id != null);
</script>

<template>
    <div
        v-if="showHighlight"
        class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800"
    >
        Opening application from external link...
    </div>
</template>
