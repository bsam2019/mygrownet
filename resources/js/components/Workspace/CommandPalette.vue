<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    Squares2X2Icon,
    BuildingOfficeIcon,
    MagnifyingGlassIcon,
    CubeIcon,
    SparklesIcon,
    ArrowRightIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/solid';

interface App {
    id: number;
    name: string;
    slug: string;
    description?: string;
}

interface Organization {
    id: number;
    name: string;
    slug: string;
    type?: string;
}

interface NavItem {
    label: string;
    route: string;
    icon: any;
    category: string;
}

interface Result {
    id: string;
    label: string;
    description: string;
    icon: any;
    category: string;
    action: () => void;
}

const props = defineProps<{ open: boolean }>();
const emit = defineEmits<{ close: [] }>();

const page = usePage();
const workspace = (page.props as any).workspace;

const apps: App[] = [];
if (workspace?.apps) {
    Object.values(workspace.apps as Record<string, App[]>).forEach((list: App[]) => apps.push(...list));
}

const orgs: Organization[] = workspace?.organizations ?? [];

const navItems: NavItem[] = [
    { label: 'Workspace', route: 'workspace', icon: Squares2X2Icon, category: 'Navigation' },
    { label: 'Browse Apps', route: 'apps.catalog', icon: CubeIcon, category: 'Navigation' },
    { label: 'Create Organization', route: 'workspace.organization.create', icon: BuildingOfficeIcon, category: 'Actions' },
];

const query = ref('');
const input = ref<HTMLInputElement | null>(null);
const selectedIdx = ref(0);

watch(() => props.open, (val) => {
    if (val) {
        query.value = '';
        selectedIdx.value = 0;
        nextTick(() => input.value?.focus());
    }
});

const results = computed(() => {
    const q = query.value.toLowerCase().trim();
    const out: Result[] = [];

    if (!q) return out;

    const add = (label: string, desc: string, icon: any, category: string, action: () => void) => {
        out.push({ id: category + ':' + label, label, description: desc, icon, category, action });
    };

    // Nav items
    for (const n of navItems) {
        if (n.label.toLowerCase().includes(q)) {
            add(n.label, `Go to ${n.label}`, n.icon, n.category, () => {
                router.visit(route(n.route));
                emit('close');
            });
        }
    }

    // Apps
    for (const a of apps) {
        if (a.name.toLowerCase().includes(q) || a.slug.toLowerCase().includes(q)) {
            add(a.name, a.description || `Launch ${a.name}`, GlobeAltIcon, 'Applications', () => {
                router.post(route('workspace.launch', { application: a.id }));
                emit('close');
            });
        }
    }

    // Organizations
    for (const o of orgs) {
        if (o.name.toLowerCase().includes(q)) {
            add(o.name, `Switch to organization`, BuildingOfficeIcon, 'Organizations', () => {
                router.post(route('workspace.switch-context'), { type: 'organization', organization_id: o.id });
                router.visit(route('workspace'));
                emit('close');
            });
        }
    }

    return out;
});

const grouped = computed(() => {
    const groups: { category: string; items: Result[] }[] = [];
    const seen = new Set<string>();
    for (const r of results.value) {
        if (!seen.has(r.category)) {
            seen.add(r.category);
            groups.push({ category: r.category, items: [] });
        }
        const g = groups.find(g => g.category === r.category);
        if (g) g.items.push(r);
    }
    return groups;
});

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') { emit('close'); return; }
    if (e.key === 'ArrowDown') { e.preventDefault(); selectedIdx.value = Math.min(selectedIdx.value + 1, results.value.length - 1); return; }
    if (e.key === 'ArrowUp') { e.preventDefault(); selectedIdx.value = Math.max(selectedIdx.value - 1, 0); return; }
    if (e.key === 'Enter' && results.value[selectedIdx.value]) {
        results.value[selectedIdx.value].action();
    }
}
</script>

<template>
    <teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-[100] flex items-start justify-center pt-[15vh]"
            @keydown="onKeydown"
            tabindex="-1"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="emit('close')"></div>
            <div class="relative w-full max-w-lg mx-4 bg-white dark:bg-[#121926] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/[0.06] overflow-hidden">
                <!-- Search -->
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 dark:border-white/[0.04]">
                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 flex-shrink-0" />
                    <input
                        ref="input"
                        v-model="query"
                        type="text"
                        placeholder="Search apps, pages, organizations..."
                        class="flex-1 text-sm bg-transparent border-none outline-none text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30"
                        @keydown="onKeydown"
                    />
                    <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium text-gray-400 bg-gray-100 dark:bg-white/[0.06] rounded border border-gray-200 dark:border-white/[0.08]">ESC</kbd>
                </div>

                <!-- Results -->
                <div class="max-h-80 overflow-y-auto py-2">
                    <div v-if="query && results.length === 0" class="px-5 py-8 text-center">
                        <MagnifyingGlassIcon class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-white/20" />
                        <p class="text-sm text-gray-500 dark:text-white/50">No results for "<span class="font-medium text-gray-700 dark:text-white/70">{{ query }}</span>"</p>
                    </div>

                    <template v-for="(group, gi) in grouped" :key="group.category">
                        <div class="px-5 py-1.5 text-[10px] font-semibold text-gray-400 dark:text-white/30 uppercase tracking-wider">{{ group.category }}</div>
                        <div
                            v-for="(item, ii) in group.items"
                            :key="item.id"
                            @click="item.action"
                            @mouseenter="selectedIdx = results.indexOf(item)"
                            :class="[
                                'flex items-center gap-3 px-5 py-2.5 cursor-pointer transition-colors',
                                selectedIdx === results.indexOf(item)
                                    ? 'bg-indigo-50 dark:bg-white/[0.06] text-indigo-700 dark:text-white'
                                    : 'text-gray-700 dark:text-white/70 hover:bg-gray-50 dark:hover:bg-white/[0.03]'
                            ]"
                        >
                            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/[0.06] flex items-center justify-center flex-shrink-0"
                                :class="{ 'bg-indigo-100 dark:bg-indigo-500/10': selectedIdx === results.indexOf(item) }">
                                <component :is="item.icon" class="w-4 h-4" :class="selectedIdx === results.indexOf(item) ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-white/50'" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ item.label }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ item.description }}</p>
                            </div>
                            <ArrowRightIcon class="w-3.5 h-3.5 text-gray-300 dark:text-white/20 flex-shrink-0" />
                        </div>
                    </template>

                    <div v-if="!query" class="px-5 py-8 text-center">
                        <SparklesIcon class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-white/20" />
                        <p class="text-sm text-gray-500 dark:text-white/50">Type to search across your workspace</p>
                        <div class="mt-3 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-white/30">
                            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/[0.06] rounded font-mono">↑↓</span>
                            <span>navigate</span>
                            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/[0.06] rounded font-mono">↵</span>
                            <span>open</span>
                            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/[0.06] rounded font-mono">ESC</span>
                            <span>close</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>
