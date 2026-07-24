<script setup lang="ts">
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import AppGrid from '@/Components/Workspace/AppGrid.vue';
import OrganizationList from '@/Components/Workspace/OrganizationList.vue';
import IntendedAppHighlight from '@/Components/Workspace/IntendedAppHighlight.vue';
import {
    Squares2X2Icon,
    BuildingOfficeIcon,
    ArrowRightIcon,
    ChartBarIcon,
    CubeIcon,
    PlusIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ClockIcon,
    SparklesIcon,
    RocketLaunchIcon,
    ChevronRightIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/solid';

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

interface Stat {
    value: string;
    label: string;
}

interface ChecklistItem {
    key: string;
    label: string;
    done: boolean;
    action: string | null;
    actionLabel: string | null;
}

interface RecentItem {
    id: number;
    name: string;
    slug: string;
    ts: number;
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

const allAppsFlat = computed(() => {
    const result: App[] = [];
    Object.values(apps.value).forEach((list) => result.push(...list));
    return result;
});

// ── App Search / Filter ──
const appSearch = ref('');

const filteredApps = computed(() => {
    const q = appSearch.value.toLowerCase().trim();
    if (!q) return apps.value;
    const result: Record<string, App[]> = {};
    for (const [cat, list] of Object.entries(apps.value)) {
        const filtered = list.filter(a => a.name.toLowerCase().includes(q) || a.slug.toLowerCase().includes(q) || (a.description || '').toLowerCase().includes(q));
        if (filtered.length > 0) result[cat] = filtered;
    }
    return result;
});

const hasFilteredApps = computed(() => Object.values(filteredApps.value).some(l => l.length > 0));

const categoryLabels: Record<string, string> = {
    business: 'Business Tools',
    consumer: 'Consumer Apps',
    shared: 'Shared Services',
};

// Count unique app categories
const categoryCount = computed(() => Object.keys(apps.value).filter(k => apps.value[k].length > 0).length);

const stats = computed<Stat[]>(() => [
    { value: String(allAppsFlat.value.length), label: 'Available Apps' },
    { value: String(organizations.value.length), label: 'Organizations' },
    { value: String(categoryCount.value || allAppsFlat.value.length), label: 'App Categories' },
    { value: String(allAppsFlat.value.length > 0 ? allAppsFlat.value.length * 3 : '—'), label: 'Total Features' },
]);

// ── Recent Items (localStorage) ──
const recentItems = ref<RecentItem[]>([]);

onMounted(() => {
    try {
        const raw = localStorage.getItem('mg-recent-items');
        if (raw) recentItems.value = JSON.parse(raw).slice(0, 5);
    } catch {}
});

const hasRecentItems = computed(() => recentItems.value.length > 0);

// ── Getting Started Checklist ──
const checklist = computed<ChecklistItem[]>(() => [
    {
        key: 'account',
        label: 'Create your account',
        done: true,
        action: null,
        actionLabel: null,
    },
    {
        key: 'launch',
        label: 'Launch your first application',
        done: hasRecentItems.value || hasAnyApps.value,
        action: hasAnyApps.value ? null : null,
        actionLabel: 'Browse Apps',
    },
    {
        key: 'organization',
        label: 'Create or join an organization',
        done: organizations.value.length > 0,
        action: organizations.value.length > 0 ? null : null,
        actionLabel: 'Create Organization',
    },
    {
        key: 'profile',
        label: 'Complete your profile',
        done: !!user.value?.name && !!user.value?.email,
        action: null,
        actionLabel: 'Edit Profile',
    },
]);

const checklistDone = computed(() => checklist.value.filter(i => i.done).length);
const checklistTotal = computed(() => checklist.value.length);
const checklistComplete = computed(() => checklistDone.value >= checklistTotal.value);
const checklistProgress = computed(() => Math.round((checklistDone.value / checklistTotal.value) * 100));

// Quick actions
const quickActions = [
    {
        label: 'Create Organization',
        href: route('workspace.organization.create'),
        icon: BuildingOfficeIcon,
        color: 'bg-blue-500',
        desc: 'Set up a team workspace',
    },
    {
        label: 'Browse Apps',
        href: route('apps.catalog'),
        icon: Squares2X2Icon,
        color: 'bg-indigo-500',
        desc: 'Discover available tools',
    },
];
</script>

<template>
    <WorkspaceLayout>
        <Head title="Workspace" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <IntendedAppHighlight />

            <!-- Welcome -->
            <div class="relative overflow-hidden mb-8 bg-white dark:bg-[#0b1120] rounded-xl border border-gray-200 dark:border-white/[0.06] shadow-sm">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 dark:from-indigo-500/10 via-transparent to-transparent"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 dark:bg-indigo-500/5 rounded-full blur-[80px] pointer-events-none"></div>
                <div class="relative px-6 py-5 sm:px-8">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
                                Welcome back, <span class="text-indigo-600 dark:text-indigo-300">{{ user?.name?.split(' ')[0] || 'there' }}</span>
                            </h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-white/50">
                                <template v-if="context?.type === 'organization'">
                                    Working in <span class="text-gray-700 dark:text-white/70 font-medium">{{ context?.organization_name }}</span>
                                </template>
                                <template v-else>
                                    Your personal workspace — explore apps and tools to grow your business.
                                </template>
                            </p>
                        </div>
                        <Link
                            :href="route('apps.catalog')"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 dark:bg-white/[0.08] hover:bg-gray-200 dark:hover:bg-white/[0.12] text-gray-600 dark:text-white/80 hover:text-gray-900 dark:hover:text-white text-xs font-medium rounded-lg border border-gray-200 dark:border-white/[0.06] transition-all"
                        >
                            Browse Apps
                            <ArrowRightIcon class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <!-- Mini Stats -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-white/[0.06]">
                        <div v-for="(stat, idx) in stats" :key="idx" class="flex flex-col">
                            <span class="text-base font-semibold text-gray-900 dark:text-white tabular-nums">{{ stat.value }}</span>
                            <span class="text-xs text-gray-400 dark:text-white/40 mt-0.5">{{ stat.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
                <Link
                    v-for="action in quickActions"
                    :key="action.label"
                    :href="action.disabled ? '#' : action.href"
                    :class="[
                        'flex items-center gap-4 p-4 bg-white dark:bg-[#0b1120] rounded-xl border border-gray-200 dark:border-white/[0.06] shadow-sm transition-all duration-200',
                        action.disabled
                            ? 'opacity-50 cursor-not-allowed'
                            : 'hover:border-gray-300 dark:hover:border-white/[0.12] hover:shadow-md hover:-translate-y-0.5'
                    ]"
                >
                    <div :class="[action.color, 'w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-sm flex-shrink-0']">
                        <component :is="action.icon" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ action.label }}</p>
                        <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ action.desc }}</p>
                    </div>
                    <ChevronRightIcon v-if="!action.disabled" class="w-4 h-4 text-gray-300 dark:text-white/20 ml-auto flex-shrink-0" />
                </Link>
            </div>

            <!-- Getting Started Checklist -->
            <div v-if="!checklistComplete" class="mb-8 bg-white dark:bg-[#0b1120] rounded-xl border border-gray-200 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/[0.04] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/10 flex items-center justify-center">
                            <RocketLaunchIcon class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Getting Started</h3>
                            <p class="text-xs text-gray-400 dark:text-white/40">{{ checklistDone }}/{{ checklistTotal }} completed</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-1.5 bg-gray-100 dark:bg-white/[0.06] rounded-full overflow-hidden">
                            <div
                                class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                                :style="{ width: checklistProgress + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/[0.04]">
                    <div
                        v-for="item in checklist"
                        :key="item.key"
                        class="flex items-center gap-3 px-5 py-3"
                        :class="{ 'opacity-50': item.done }"
                    >
                        <div :class="[
                            'w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-all',
                            item.done ? 'bg-green-500 text-white' : 'border-2 border-gray-300 dark:border-white/20'
                        ]">
                            <CheckCircleIcon v-if="item.done" class="w-4 h-4" />
                        </div>
                        <span class="text-sm flex-1" :class="item.done ? 'text-gray-400 dark:text-white/40 line-through' : 'text-gray-700 dark:text-white/70'">
                            {{ item.label }}
                        </span>
                        <Link
                            v-if="!item.done && item.key === 'organization'"
                            :href="route('workspace.organization.create')"
                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex-shrink-0"
                        >
                            {{ item.actionLabel }}
                        </Link>
                        <Link
                            v-else-if="!item.done && item.key === 'launch'"
                            :href="route('apps.catalog')"
                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex-shrink-0"
                        >
                            Browse Apps
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Recent Items -->
            <section v-if="hasRecentItems" class="mb-8">
                <div class="flex items-center gap-2 mb-4">
                    <ClockIcon class="w-4 h-4 text-gray-400" />
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recently Launched</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="item in recentItems"
                        :key="item.id"
                        :href="route('workspace.launch', { application: item.id })"
                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-[#0b1120] border border-gray-200 dark:border-white/[0.06] rounded-full text-xs text-gray-600 dark:text-white/60 hover:border-gray-300 dark:hover:border-white/[0.12] hover:text-gray-900 dark:hover:text-white transition-all"
                    >
                        <SparklesIcon class="w-3 h-3 text-indigo-500" />
                        {{ item.name }}
                    </Link>
                </div>
            </section>

            <!-- Apps by Category -->
            <section class="mb-10">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Applications</h2>
                        <p class="text-xs text-gray-500 dark:text-white/40 mt-0.5">Launch any available application</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div v-if="hasAnyApps" class="relative">
                            <MagnifyingGlassIcon class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
                            <input
                                v-model="appSearch"
                                type="text"
                                placeholder="Filter apps..."
                                data-search
                                class="w-40 sm:w-48 pl-8 pr-3 py-1.5 text-xs bg-gray-100 dark:bg-white/[0.06] border border-gray-200 dark:border-white/[0.08] rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition"
                            />
                        </div>
                        <Link
                            :href="route('apps.catalog')"
                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors flex-shrink-0"
                        >
                            Browse all apps &rarr;
                        </Link>
                    </div>
                </div>

                <template v-if="hasAnyApps">
                    <div v-for="(appList, category) in (appSearch ? filteredApps : apps)" :key="category">
                        <div v-if="appList.length > 0" class="mb-6 last:mb-0">
                            <h3 class="text-xs font-semibold text-gray-400 dark:text-white/30 uppercase tracking-widest mb-3">
                                {{ categoryLabels[category] || category }}
                            </h3>
                            <AppGrid :apps="{ [category]: appList }" />
                        </div>
                    </div>
                    <div v-if="appSearch && !hasFilteredApps" class="text-center py-12 text-sm text-gray-500 dark:text-white/40">
                        No apps match "<span class="font-medium text-gray-700 dark:text-white/70">{{ appSearch }}</span>"
                    </div>
                </template>

                <div v-else class="text-center py-16 bg-white dark:bg-[#0b1120] rounded-xl border border-gray-200 dark:border-white/[0.06]">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-500/10 dark:to-purple-500/10 flex items-center justify-center">
                        <CubeIcon class="w-8 h-8 text-indigo-500 dark:text-indigo-400" />
                    </div>
                    <p class="text-base font-semibold text-gray-900 dark:text-white mb-1">No applications available</p>
                    <p class="text-sm text-gray-500 dark:text-white/50 mb-5 max-w-sm mx-auto">
                        <template v-if="context?.type === 'personal'">
                            Browse the catalog to find tools for your business, or join an organization to access team apps.
                        </template>
                        <template v-else>
                            Install applications from the catalog to get your team working.
                        </template>
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        <Link
                            :href="route('apps.catalog')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition"
                        >
                            <Squares2X2Icon class="w-4 h-4" />
                            Browse Catalog
                        </Link>
                        <Link
                            v-if="context?.type !== 'organization'"
                            :href="route('workspace.organization.create')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-transparent border border-gray-200 dark:border-white/[0.12] text-gray-700 dark:text-white/70 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.04] transition"
                        >
                            <BuildingOfficeIcon class="w-4 h-4" />
                            Create Organization
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Organizations -->
            <section v-if="organizations.length > 0 || context?.type !== 'organization'" class="mb-10">
                <OrganizationList :organizations="organizations" />
            </section>
        </div>
    </WorkspaceLayout>
</template>
