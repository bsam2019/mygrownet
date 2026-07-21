<script setup lang="ts">
import OrganizationCard from './OrganizationCard.vue';
import { Link } from '@inertiajs/vue3';

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
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Organizations</h3>
        <div v-if="organizations.length === 0" class="bg-white rounded-xl border border-dashed border-gray-300 p-8 text-center">
            <BuildingOfficeIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-sm font-medium text-gray-900 mb-1">You are not a member of any organization yet</p>
            <p class="text-sm text-gray-500 mb-4">Create or join an organization to access business tools</p>
            <Link
                :href="route('workspace.organization.create')"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                Create Organization
            </Link>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <OrganizationCard v-for="org in organizations" :key="org.id" :organization="org" />
        </div>
    </div>
</template>

<script lang="ts">
import { BuildingOfficeIcon } from '@heroicons/vue/24/solid';
export default { components: { BuildingOfficeIcon } };
</script>
