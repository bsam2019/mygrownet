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

const isOrgContext = computed(() => {
    const host = window.location.hostname;
    const orgSubdomains = ['stockflow.mygrownet.com', 'bms.mygrownet.com', 'growfinance.mygrownet.com', 'bizdocs.mygrownet.com', 'bizboost.mygrownet.com'];
    return orgSubdomains.includes(host);
});

const allApps = computed(() => {
    const list = Object.values(props.apps).flat();
    if (isOrgContext.value) {
        const consumerSlugs = ['grownet', 'growmusic', 'growstream', 'growmart', 'lifeplus', 'zamstay', 'primeedge'];
        return list.filter(a => !consumerSlugs.includes(a.slug));
    }
    return list;
});
</script>

<template>
    <div v-if="allApps.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <AppTile v-for="app in allApps" :key="app.id" :app="app" />
    </div>
</template>
