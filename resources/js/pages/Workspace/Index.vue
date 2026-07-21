<script setup lang="ts">
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import AppGrid from '@/Components/Workspace/AppGrid.vue';
import OrganizationList from '@/Components/Workspace/OrganizationList.vue';
import IntendedAppHighlight from '@/Components/Workspace/IntendedAppHighlight.vue';
import GlobalAppSwitcher from '@/Components/Workspace/GlobalAppSwitcher.vue';
import { RocketLaunchIcon, BuildingOfficeIcon } from '@heroicons/vue/24/solid';

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
    apps?: OrgApp[];
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

const hasAnyApps = computed(() => {
    return Object.values(apps.value).some((appList) => appList.length > 0);
});
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
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-1">Applications</h2>
                        <p class="text-sm text-gray-500">Your available applications</p>
                    </div>
                    <Link
                        :href="route('apps.catalog')"
                        class="text-sm font-medium text-blue-600 hover:text-blue-700 underline underline-offset-2"
                    >
                        Browse All Apps
                    </Link>
                </div>
                <AppGrid :apps="apps" />
                <div v-if="!hasAnyApps" class="text-center py-12">
                    <RocketLaunchIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-sm font-medium text-gray-900 mb-1">No applications available yet</p>
                    <p class="text-sm text-gray-500">
                        <template v-if="context?.type === 'personal'">
                            <Link :href="route('apps.catalog')" class="text-blue-600 underline underline-offset-2">Browse all apps</Link>
                            or join an organization to access business tools.
                        </template>
                        <template v-else>
                            <Link :href="route('apps.catalog')" class="text-blue-600 underline underline-offset-2">Browse all apps</Link>
                            to install applications for this organization.
                        </template>
                    </p>
                </div>
            </section>

            <!-- Organizations -->
            <section v-if="organizations.length > 0 || context?.type !== 'organization'" class="mb-10">
                <OrganizationList :organizations="organizations" />
            </section>
        </div>
    </WorkspaceLayout>
</template>
