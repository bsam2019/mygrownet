<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import {
    CubeIcon, CurrencyDollarIcon, DocumentTextIcon,
    ShoppingBagIcon, HomeModernIcon, ChartBarIcon,
    RocketLaunchIcon, CloudIcon, BuildingOfficeIcon,
    BookOpenIcon, MegaphoneIcon, GlobeAltIcon,
    CommandLineIcon, ArrowLeftIcon,
} from '@heroicons/vue/24/solid';

const appIcons: Record<string, any> = {
    grownet: RocketLaunchIcon, growfinance: CurrencyDollarIcon,
    bizdocs: DocumentTextIcon, stockflow: ChartBarIcon,
    bms: BuildingOfficeIcon, bizboost: MegaphoneIcon,
    growmart: ShoppingBagIcon, growbuilder: GlobeAltIcon,
    growstorage: CloudIcon, zamstay: HomeModernIcon,
    primeedge: CommandLineIcon, lifeplus: BookOpenIcon,
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

const appDescriptions: Record<string, string> = {
    bms: 'Business Management Suite',
    stockflow: 'Stock management & inventory audit',
    growfinance: 'Financial management & reporting',
    bizdocs: 'Document management & storage',
    growbuilder: 'Website builder & e-commerce',
    growmart: 'E-commerce marketplace',
    grownet: 'Product platform — GrowStream, GrowLearn, GrowTemplates',
    zamstay: 'Property booking platform',
    primeedge: 'Investment advisory',
    lifeplus: 'Health & wellness',
    bizboost: 'Marketing & business growth',
    growstorage: 'Cloud storage & backup',
    'quick-invoice': 'Simple invoicing',
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
const context = computed(() => (page.props as any).workspace?.context);

const isOrgContext = computed(() => {
    if (context.value?.type === 'organization' || context.value?.organization_id != null) return true;
    const host = window.location.hostname;
    const orgSubdomains = ['stockflow.mygrownet.com', 'bms.mygrownet.com', 'growfinance.mygrownet.com', 'bizdocs.mygrownet.com', 'bizboost.mygrownet.com'];
    if (orgSubdomains.includes(host)) return true;
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('context') === 'organization' || urlParams.has('org');
});

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
</script>

<template>
    <WorkspaceLayout>
        <Head title="App Catalog" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Link
                :href="route('workspace')"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white mb-6 transition-colors"
            >
                <ArrowLeftIcon class="w-4 h-4" />
                Back to Workspace
            </Link>

            <div class="mb-8 p-6 bg-gradient-to-br from-white via-slate-50/80 to-indigo-50/20 dark:from-[#0b1120] dark:via-[#0b1120] dark:to-indigo-950/20 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] shadow-sm">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">App Catalog</h1>
                    <span
                        v-if="isOrgContext"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20"
                    >
                        <SparklesIcon class="w-3.5 h-3.5" />
                        {{ context?.organization_name || 'Organization Workspace' }}
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-white/[0.08]"
                    >
                        Personal Workspace
                    </span>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5">
                    {{ isOrgContext ? 'Browse and launch business applications available for your organization' : 'Browse all available applications and personal tools on the platform' }}
                </p>
            </div>

            <div class="space-y-10">
                <div v-for="(categoryApps, category) in apps" :key="category">
                    <div v-if="categoryApps.length > 0 && !(isOrgContext && category === 'consumer')">
                        <div class="mb-4 pb-2 border-b border-slate-200/60 dark:border-white/[0.06]">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ categoryLabels[category] || category }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5">{{ categoryDescriptions[category] || '' }}</p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <Link
                                v-for="app in categoryApps"
                                :key="app.id"
                                :href="route('apps.show', { slug: app.slug })"
                                class="group flex flex-col items-center gap-3 p-5 bg-white dark:bg-[#0c1322] rounded-2xl border border-slate-200/80 dark:border-white/[0.08] hover:border-indigo-400/50 dark:hover:border-indigo-400/40 hover:shadow-xl hover:shadow-indigo-500/10 dark:hover:shadow-indigo-500/15 hover:-translate-y-1 hover:scale-[1.02] transition-all duration-300 cursor-pointer text-left relative overflow-hidden"
                            >
                                <div
                                    :class="[
                                        'w-13 h-13 sm:w-14 sm:h-14 flex items-center justify-center rounded-2xl bg-gradient-to-br text-white shadow-lg group-hover:scale-110 transition-all duration-300',
                                        appColors[app.slug] || 'from-indigo-500 to-indigo-600 shadow-indigo-500/25',
                                    ]"
                                >
                                    <component :is="appIcons[app.slug] || CubeIcon" class="w-6 h-6 sm:w-7 sm:h-7" />
                                </div>
                                <div class="text-center relative z-10">
                                    <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 line-clamp-1">
                                        {{ app.name }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 dark:text-slate-400 text-center line-clamp-1 leading-tight mt-0.5 block">
                                        {{ appDescriptions[app.slug] || '' }}
                                    </span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>
