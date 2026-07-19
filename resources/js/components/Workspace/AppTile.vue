<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CubeIcon } from '@heroicons/vue/24/solid';

interface App {
    id: number;
    name: string;
    slug: string;
    description?: string;
    url?: string;
    icon?: string;
}

defineProps<{
    app: App;
}>();

function launch(app: App) {
    if (app.url) {
        window.location.href = app.url;
    }
}
</script>

<template>
    <button
        @click="launch(app)"
        class="group flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer"
    >
        <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-sm group-hover:shadow-md transition-shadow">
            <CubeIcon class="w-6 h-6" />
        </div>
        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 text-center">{{ app.name }}</span>
    </button>
</template>
