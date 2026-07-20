<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Squares2X2Icon } from '@heroicons/vue/24/solid';

interface App {
    id: number;
    name: string;
    slug: string;
    url?: string;
}

interface WorkspaceContext {
    type: string;
    organization_id: number | null;
    organization_slug: string | null;
    organization_name: string | null;
    application_id: number | null;
}

const open = ref(false);
const page = usePage();

const context = computed<WorkspaceContext | undefined>(() => (page.props as any).workspace?.context);
const appsByCategory = computed<Record<string, App[]>>(() => (page.props as any).workspace?.apps ?? {});
const orgs = computed<any[]>(() => (page.props as any).workspace?.organizations ?? []);

const allApps = computed(() => {
    const apps: App[] = [];
    Object.values(appsByCategory.value).forEach((appList) => {
        apps.push(...appList);
    });
    return apps;
});

function goTo(url?: string) {
    open.value = false;
    if (url) {
        window.location.href = url;
    }
}

function goToOrg(slug: string) {
    open.value = false;
    router.visit(route('workspace.organization', { slug }));
}

function goToPlatform() {
    open.value = false;
    router.visit(route('workspace'));
}

const currentLabel = computed(() => {
    if (!context.value) return 'MyGrowNet';
    if (context.value.type === 'organization' && context.value.organization_name) {
        return context.value.organization_name;
    }
    return 'Personal Workspace';
});
</script>

<template>
    <div class="relative">
        <button
            @click="open = !open"
            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            title="Switch Application"
        >
            <Squares2X2Icon class="w-5 h-5" />
        </button>

        <div
            v-if="open"
            class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-200 p-3 z-50"
            @click.away="open = false"
        >
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2 px-1">
                {{ currentLabel }}
            </p>

            <div class="grid grid-cols-3 gap-2 mb-3" v-if="allApps.length > 0">
                <button
                    v-for="app in allApps"
                    :key="app.id"
                    @click="goTo(app.url)"
                    class="flex flex-col items-center gap-1 p-2 rounded-lg hover:bg-gray-50 transition-colors text-center"
                >
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 text-xs font-bold">{{ app.name.charAt(0) }}</span>
                    </div>
                    <span class="text-xs text-gray-600 truncate w-full">{{ app.name }}</span>
                </button>
            </div>

            <div v-if="orgs.length > 0" class="border-t border-gray-100 pt-2 mt-1">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1 px-1">Organizations</p>
                <button
                    v-for="org in orgs"
                    :key="org.id"
                    @click="goToOrg(org.slug)"
                    class="w-full text-left px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-50 rounded-lg"
                >
                    {{ org.name }}
                </button>
            </div>

            <div class="border-t border-gray-100 pt-2 mt-1">
                <button
                    @click="goToPlatform()"
                    class="w-full text-left px-2 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg font-medium"
                >
                    MyGrowNet Platform
                </button>
            </div>
        </div>
    </div>
</template>
