<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { ChevronDownIcon, UserCircleIcon, BuildingOfficeIcon } from '@heroicons/vue/24/solid';

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
            class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white/70 hover:text-white/90 bg-white/[0.06] hover:bg-white/[0.1] rounded-lg transition-all border border-white/[0.06]"
        >
            <span class="max-w-[140px] truncate">{{ currentLabel }}</span>
            <ChevronDownIcon class="w-3.5 h-3.5 text-white/40" />
        </button>

        <div
            v-if="open"
            class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-200 py-1.5 z-50"
        >
            <button
                @click="switchToPersonal"
                class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors"
                :class="{ 'bg-indigo-50 text-indigo-700 font-medium': currentType === 'personal' }"
            >
                <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <UserCircleIcon class="w-4 h-4 text-gray-500" />
                </div>
                <span>Personal Workspace</span>
            </button>

            <div v-if="organizations.length > 0" class="border-t border-gray-100 mt-1 pt-1">
                <div class="px-4 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Organizations</div>
                <button
                    v-for="org in organizations"
                    :key="org.id"
                    @click="switchToOrganization(org)"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors"
                    :class="{
                        'bg-indigo-50 text-indigo-700 font-medium':
                            currentType === 'organization' && workspace?.organization_id === org.id,
                    }"
                >
                    <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <BuildingOfficeIcon class="w-4 h-4 text-gray-500" />
                    </div>
                    <span>{{ org.name }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
