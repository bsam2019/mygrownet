<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import {
    CubeIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    ShoppingBagIcon,
    HomeModernIcon,
    ChartBarIcon,
    RocketLaunchIcon,
    CloudIcon,
    BuildingOfficeIcon,
    BookOpenIcon,
    MegaphoneIcon,
    GlobeAltIcon,
    CommandLineIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/solid';

const appIcons: Record<string, any> = {
    grownet: RocketLaunchIcon,
    growfinance: CurrencyDollarIcon,
    bizdocs: DocumentTextIcon,
    stockflow: ChartBarIcon,
    bms: BuildingOfficeIcon,
    bizboost: MegaphoneIcon,
    growmart: ShoppingBagIcon,
    growbuilder: GlobeAltIcon,
    growstorage: CloudIcon,
    zamstay: HomeModernIcon,
    primeedge: CommandLineIcon,

    lifeplus: BookOpenIcon,
    'quick-invoice': DocumentTextIcon,
};

const appColors: Record<string, string> = {
    grownet: 'from-green-500 to-green-600',
    growfinance: 'from-indigo-500 to-indigo-600',
    bizdocs: 'from-blue-500 to-blue-600',
    stockflow: 'from-teal-500 to-teal-600',
    bms: 'from-purple-500 to-purple-600',
    bizboost: 'from-orange-500 to-orange-600',
    growmart: 'from-red-500 to-red-600',
    growbuilder: 'from-cyan-500 to-cyan-600',
    growstorage: 'from-sky-500 to-sky-600',
    zamstay: 'from-emerald-500 to-emerald-600',
    primeedge: 'from-violet-500 to-violet-600',

    lifeplus: 'from-yellow-500 to-amber-600',
    'quick-invoice': 'from-blue-500 to-blue-600',
};

interface App {
    id: number;
    name: string;
    slug: string;
    category?: string;
    url?: string;
    description?: string;
    available: boolean;
    reason: string | null;
}

const page = usePage();

const apps = computed(() => (page.props as any).apps as Record<string, App[]> ?? {});

const workspace = computed(() => (page.props as any).workspace ?? {});
const context = computed(() => workspace.value.context ?? {});
const isOrganization = computed(() => context.value.type === 'organization');
const orgSlug = computed(() => context.value.organization_slug);

const categoryLabels: Record<string, string> = {
    business: 'Business Tools',
    consumer: 'Personal Apps',
    shared: 'Shared Services',
};

const categoryDescriptions: Record<string, string> = {
    business: 'Run and grow your business',
    consumer: 'Personal growth and rewards',
    shared: 'Available in any context',
};

function launch(app: App) {
    if (!app.available) return;
    router.post(route('workspace.launch', { application: app.id }));
}

function install(app: App) {
    if (!orgSlug.value) return;
    router.post(route('workspace.organization.install', { slug: orgSlug.value, application: app.id }));
}
</script>

<template>
    <WorkspaceLayout>
        <Head title="App Catalog" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Link
                :href="route('workspace')"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="w-4 h-4" />
                Back to Workspace
            </Link>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">App Catalog</h1>
                <p class="text-sm text-gray-500 mt-1">Browse all available applications on the platform</p>
            </div>

            <div class="space-y-8">
                <div v-for="(categoryApps, category) in apps" :key="category">
                    <div v-if="categoryApps.length > 0">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ categoryLabels[category] || category }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ categoryDescriptions[category] || '' }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <div
                                v-for="app in categoryApps"
                                :key="app.id"
                            >
                                <button
                                    v-if="app.available"
                                    @click="launch(app)"
                                    class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md cursor-pointer transition-all w-full"
                                >
                                    <div
                                        :class="[
                                            'w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-sm',
                                            appColors[app.slug] || 'from-blue-500 to-blue-600',
                                        ]"
                                    >
                                        <component :is="appIcons[app.slug] || CubeIcon" class="w-6 h-6" />
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 text-center">{{ app.name }}</span>
                                </button>
                                <div
                                    v-else
                                    class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-gray-100 opacity-60 w-full"
                                >
                                    <div class="relative">
                                        <div
                                            :class="[
                                                'w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-sm',
                                                appColors[app.slug] || 'from-blue-500 to-blue-600',
                                            ]"
                                        >
                                            <component :is="appIcons[app.slug] || CubeIcon" class="w-6 h-6" />
                                        </div>
                                        <div
                                            v-if="app.reason"
                                            class="absolute -top-1.5 -right-1.5 bg-gray-700 text-white text-[10px] font-medium px-1.5 py-0.5 rounded-full whitespace-nowrap shadow-sm"
                                        >
                                            {{ app.reason }}
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-400 text-center">{{ app.name }}</span>
                                    <button
                                        v-if="isOrganization && app.reason === 'Not Installed for Organization'"
                                        @click="install(app)"
                                        class="mt-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-full transition-colors"
                                    >
                                        Install
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>