<script setup lang="ts">
import AppTile from './AppTile.vue';

interface App {
    id: number;
    name: string;
    slug: string;
    description?: string;
    url?: string;
    icon?: string;
}

defineProps<{
    apps: Record<string, App[]>;
}>();

const categoryLabels: Record<string, string> = {
    business: 'Business Tools',
    consumer: 'Personal Apps',
    shared: 'Shared Services',
};

const categoryDescriptions: Record<string, string> = {
    business: 'Run and grow your business',
    consumer: 'Personal growth and rewards',
    shared: 'Available in any context',
};
</script>

<template>
    <div class="space-y-8">
        <div v-for="(apps, category) in apps" :key="category">
            <div v-if="apps.length > 0">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ categoryLabels[category] || category }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ categoryDescriptions[category] || '' }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <AppTile v-for="app in apps" :key="app.id" :app="app" />
                </div>
            </div>
        </div>
    </div>
</template>
