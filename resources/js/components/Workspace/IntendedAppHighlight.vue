<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowRightIcon } from '@heroicons/vue/24/solid';

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

const page = usePage();

const context = computed<WorkspaceContext | undefined>(() => (page.props as any).workspace?.context ?? undefined);
const appsByCategory = computed<Record<string, App[]>>(() => (page.props as any).workspace?.apps ?? {});

const intendedAppId = computed(() => context.value?.application_id);

const intendedApp = computed<App | undefined>(() => {
    const id = intendedAppId.value;
    if (!id) return undefined;
    for (const category of Object.values(appsByCategory.value)) {
        const found = category.find((a: App) => a.id === id);
        if (found) return found;
    }
    return undefined;
});

const show = computed(() => intendedApp.value != null);

function continueToApp() {
    if (intendedApp.value) {
        router.post(route('workspace.launch', { application: intendedApp.value.id }));
    }
}
</script>

<template>
    <div
        v-if="show && intendedApp"
        class="mb-6 px-5 py-3 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-between"
    >
        <div class="text-sm text-indigo-800">
            <span class="font-medium">{{ intendedApp.name }}</span>
            <span class="text-indigo-500"> is ready for you</span>
        </div>
        <button
            @click="continueToApp"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
        >
            Continue
            <ArrowRightIcon class="w-3.5 h-3.5" />
        </button>
    </div>
</template>
