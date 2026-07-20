<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { BuildingOfficeIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';

interface OrgApp {
    id: number;
    name: string;
    slug: string;
}

interface Organization {
    id: number;
    name: string;
    slug: string;
    type?: string;
    country?: string;
    apps?: OrgApp[];
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
            <div v-if="organization.apps && organization.apps.length > 0" class="flex flex-wrap gap-1 mt-1.5">
                <span
                    v-for="app in organization.apps.slice(0, 4)"
                    :key="app.id"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700"
                >
                    {{ app.name }}
                </span>
                <span
                    v-if="organization.apps.length > 4"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-50 text-gray-500"
                >
                    +{{ organization.apps.length - 4 }}
                </span>
            </div>
        </div>
        <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-gray-600 flex-shrink-0" />
    </div>
</template>
