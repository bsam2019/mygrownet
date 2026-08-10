<script setup lang="ts">
import AppTile from './AppTile.vue';
import { computed } from 'vue';

interface App {
    id: number;
    name: string;
    slug: string;
    description?: string;
    url?: string;
    icon?: string;
}

const props = defineProps<{
    apps: Record<string, App[]>;
}>();

const allApps = computed(() => {
    return Object.values(props.apps).flat();
});
</script>

<template>
    <div v-if="allApps.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <AppTile v-for="app in allApps" :key="app.id" :app="app" />
    </div>
</template>
