<script setup lang="ts">
import OrganizationCard from './OrganizationCard.vue';
import { Link } from '@inertiajs/vue3';
import { BuildingOfficeIcon, PlusIcon } from '@heroicons/vue/24/solid';

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
    organizations: Organization[];
}>();
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Organizations</h3>
                <p class="text-xs text-gray-500 mt-0.5">Manage your teams and business entities</p>
            </div>
            <Link
                :href="route('workspace.organization.create')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0b1120] text-white text-xs font-medium rounded-lg hover:bg-gray-800 transition-colors"
            >
                <PlusIcon class="w-3.5 h-3.5" />
                <span class="hidden sm:inline">Create</span>
            </Link>
        </div>

        <div v-if="organizations.length === 0" class="bg-white rounded-xl border border-dashed border-gray-300/80 p-10 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-gray-100 flex items-center justify-center">
                <BuildingOfficeIcon class="w-6 h-6 text-gray-400" />
            </div>
            <p class="text-sm font-medium text-gray-900 mb-1">No organizations yet</p>
            <p class="text-xs text-gray-500 mb-5 max-w-xs mx-auto">Create or join an organization to access team features, shared apps, and business tools.</p>
            <Link
                :href="route('workspace.organization.create')"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0b1120] text-white text-xs font-medium rounded-lg hover:bg-gray-800 transition-colors"
            >
                <PlusIcon class="w-4 h-4" />
                Create Organization
            </Link>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <OrganizationCard v-for="org in organizations" :key="org.id" :organization="org" />
        </div>
    </div>
</template>
