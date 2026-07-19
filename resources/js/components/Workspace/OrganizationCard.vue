<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { BuildingOfficeIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';

interface Organization {
    id: number;
    name: string;
    slug: string;
    type?: string;
    country?: string;
}

defineProps<{
    organization: Organization;
}>();
</script>

<template>
    <div
        @click="router.visit(route('workspace.organization', { slug: organization.slug }))"
        class="flex items-center gap-4 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer group"
    >
        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 flex-shrink-0">
            <BuildingOfficeIcon class="w-5 h-5" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900 truncate">{{ organization.name }}</p>
            <p class="text-sm text-gray-500 truncate">{{ organization.type || 'Organization' }}</p>
        </div>
        <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-gray-600 flex-shrink-0" />
    </div>
</template>
