<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';

interface WorkspaceContext {
    type: 'personal' | 'organization' | 'guest';
    organization_id: number | null;
    organization_slug: string | null;
    organization_name: string | null;
    application_id: number | null;
}

interface Organization {
    id: number;
    name: string;
    slug: string;
}

const page = usePage();
const open = ref(false);
const menuRef = ref<HTMLElement | null>(null);

const workspace = computed<WorkspaceContext | null>(() => (page.props as any).workspace?.context ?? null);
const organizations = computed<Organization[]>(() => (page.props as any).workspace?.organizations ?? []);

const currentLabel = computed(() => {
    if (!workspace.value) return 'Loading...';
    if (workspace.value.type === 'organization' && workspace.value.organization_name) {
        return workspace.value.organization_name;
    }
    return 'Personal Workspace';
});

const currentType = computed(() => workspace.value?.type ?? 'guest');

function switchToPersonal() {
    open.value = false;
    router.post(route('workspace.switch-context'), { type: 'personal' });
}

function switchToOrganization(org: Organization) {
    open.value = false;
    router.post(route('workspace.switch-context'), {
        type: 'organization',
        organization_id: org.id,
    });
}

function handleClickOutside(e: MouseEvent) {
    if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="menuRef" class="relative">
        <button
            @click="open = !open"
            class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
        >
            <span>{{ currentLabel }}</span>
            <ChevronDownIcon class="w-4 h-4" />
        </button>

        <div
            v-if="open"
            class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
        >
            <button
                @click="switchToPersonal"
                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                :class="{ 'bg-blue-50 text-blue-700 font-medium': currentType === 'personal' }"
            >
                <span>Personal Workspace</span>
            </button>

            <div v-if="organizations.length > 0" class="border-t border-gray-100 mt-1 pt-1">
                <div class="px-4 py-1 text-xs text-gray-500 uppercase tracking-wider">Organizations</div>
                <button
                    v-for="org in organizations"
                    :key="org.id"
                    @click="switchToOrganization(org)"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                    :class="{
                        'bg-blue-50 text-blue-700 font-medium':
                            currentType === 'organization' && workspace?.organization_id === org.id,
                    }"
                >
                    <span>{{ org.name }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
