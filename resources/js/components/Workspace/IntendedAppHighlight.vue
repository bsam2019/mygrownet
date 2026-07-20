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
        class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between"
    >
        <div class="text-sm text-blue-800">
            <span class="font-medium">{{ intendedApp.name }}</span>
            <span class="text-blue-600"> is ready for you</span>
        </div>
        <button
            @click="continueToApp"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
        >
            Continue
            <ArrowRightIcon class="w-4 h-4" />
        </button>
    </div>
</template>
