<script setup lang="ts">
import OrganizationCard from './OrganizationCard.vue';

interface Organization {
    id: number;
    name: string;
    slug: string;
    type?: string;
    country?: string;
}

defineProps<{
    organizations: Organization[];
}>();
</script>

<template>
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Organizations</h3>
        <div v-if="organizations.length === 0" class="text-sm text-gray-500">
            You are not a member of any organization yet.
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <OrganizationCard v-for="org in organizations" :key="org.id" :organization="org" />
        </div>
    </div>
</template>
