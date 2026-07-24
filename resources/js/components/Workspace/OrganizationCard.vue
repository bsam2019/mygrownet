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
        class="flex items-center gap-4 p-4 bg-white rounded-xl border border-gray-200/80 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer group"
    >
        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-[#0b1120] text-white flex-shrink-0">
            <BuildingOfficeIcon class="w-5 h-5" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ organization.name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ organization.type || 'Organization' }}</p>
            <div v-if="organization.apps && organization.apps.length > 0" class="flex flex-wrap gap-1 mt-2">
                <span
                    v-for="app in organization.apps.slice(0, 4)"
                    :key="app.id"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700"
                >
                    {{ app.name }}
                </span>
                <span
                    v-if="organization.apps.length > 4"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500"
                >
                    +{{ organization.apps.length - 4 }}
                </span>
            </div>
        </div>
        <ChevronRightIcon class="w-4 h-4 text-gray-300 group-hover:text-gray-500 flex-shrink-0 transition-colors" />
    </div>
</template>
