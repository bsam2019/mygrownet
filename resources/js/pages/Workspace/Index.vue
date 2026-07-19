<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import AppGrid from '@/Components/Workspace/AppGrid.vue';
import OrganizationList from '@/Components/Workspace/OrganizationList.vue';
import IntendedAppHighlight from '@/Components/Workspace/IntendedAppHighlight.vue';
import GlobalAppSwitcher from '@/Components/Workspace/GlobalAppSwitcher.vue';
import { RocketLaunchIcon } from '@heroicons/vue/24/solid';

interface WorkspaceContext {
    type: 'personal' | 'organization' | 'guest';
    organization_id: number | null;
    organization_slug: string | null;
    organization_name: string | null;
    application_id: number | null;
}

interface App {
    id: number;
    name: string;
    slug: string;
    url?: string;
    description?: string;
}

interface Organization {
    id: number;
    name: string;
    slug: string;
    type?: string;
}

const page = usePage();

const workspace = computed(() => (page.props as any).workspace as {
    context: WorkspaceContext;
    apps: Record<string, App[]>;
    organizations: Organization[];
} | undefined);

const context = computed(() => workspace.value?.context);
const apps = computed(() => workspace.value?.apps ?? {});
const organizations = computed(() => workspace.value?.organizations ?? []);
const user = computed(() => (page.props as any).auth?.user);
</script>

<template>
    <WorkspaceLayout>
        <Head title="Workspace" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <IntendedAppHighlight />

            <!-- Welcome -->
            <div class="mb-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-1">
                            Welcome back, {{ user?.name || 'there' }}!
                        </h1>
                        <p class="text-blue-100 text-sm">
                            <template v-if="context?.type === 'organization'">
                                Working in <strong>{{ context?.organization_name }}</strong>
                            </template>
                            <template v-else>
                                Your personal workspace
                            </template>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <GlobalAppSwitcher />
                        <RocketLaunchIcon class="w-10 h-10 text-blue-200 opacity-50 hidden sm:block" />
                    </div>
                </div>
            </div>

            <!-- Apps -->
            <section class="mb-10">
                <h2 class="text-xl font-semibold text-gray-900 mb-1">Applications</h2>
                <p class="text-sm text-gray-500 mb-6">Launch any application from your workspace</p>
                <AppGrid :apps="apps" />
                <div v-if="Object.keys(apps).length === 0" class="text-center py-12 text-gray-500">
                    No applications available in this context.
                </div>
            </section>

            <!-- Organizations -->
            <section v-if="organizations.length > 0" class="mb-10">
                <OrganizationList :organizations="organizations" />
            </section>
        </div>
    </WorkspaceLayout>
</template>
