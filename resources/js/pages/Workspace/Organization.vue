<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import AppGrid from '@/Components/Workspace/AppGrid.vue';
import { BuildingOfficeIcon, UsersIcon } from '@heroicons/vue/24/solid';

interface Organization {
    id: number;
    name: string;
    slug: string;
    type?: string;
    country?: string;
    currency?: string;
}

interface App {
    id: number;
    name: string;
    slug: string;
    url?: string;
}

interface Member {
    id: number;
    user: { id: number; name: string; email: string; profile?: { avatar?: string } };
    role: string;
}

defineProps<{
    organization: Organization;
    apps: Record<string, App[]>;
    members: Member[];
    context: Record<string, any>;
}>();
</script>

<template>
    <WorkspaceLayout>
        <Head :title="organization.name" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Org Header -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md">
                        <BuildingOfficeIcon class="w-7 h-7" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold text-gray-900">{{ organization.name }}</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ organization.type || 'Organization' }}
                            <span v-if="organization.country">· {{ organization.country }}</span>
                            <span v-if="organization.currency">· {{ organization.currency }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- App Grid -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Installed Applications</h2>
                <AppGrid :apps="apps" />
            </section>

            <!-- Members -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <UsersIcon class="w-5 h-5 text-gray-500" />
                    Members
                </h2>
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    <div v-for="member in members" :key="member.id" class="flex items-center gap-3 p-4">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                            {{ member.user.name?.charAt(0) || '?' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ member.user.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ member.user.email }}</p>
                        </div>
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full capitalize">
                            {{ member.role }}
                        </span>
                    </div>
                    <div v-if="members.length === 0" class="p-4 text-sm text-gray-500 text-center">
                        No members found.
                    </div>
                </div>
            </section>
        </div>
    </WorkspaceLayout>
</template>
