<script setup lang="ts">
import { router } from '@inertiajs/vue3';
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
    grownet: 'from-emerald-500 via-emerald-600 to-teal-600 shadow-emerald-500/25',
    growfinance: 'from-indigo-500 via-indigo-600 to-blue-600 shadow-indigo-500/25',
    bizdocs: 'from-blue-500 via-blue-600 to-sky-600 shadow-blue-500/25',
    stockflow: 'from-teal-500 via-teal-600 to-cyan-600 shadow-teal-500/25',
    bms: 'from-purple-500 via-purple-600 to-indigo-600 shadow-purple-500/25',
    bizboost: 'from-amber-500 via-orange-500 to-rose-500 shadow-orange-500/25',
    growmart: 'from-rose-500 via-red-600 to-pink-600 shadow-rose-500/25',
    growbuilder: 'from-cyan-500 via-blue-600 to-indigo-600 shadow-cyan-500/25',
    growstorage: 'from-sky-400 via-blue-500 to-indigo-500 shadow-sky-500/25',
    zamstay: 'from-emerald-400 via-teal-500 to-emerald-600 shadow-emerald-500/25',
    primeedge: 'from-violet-500 via-purple-600 to-indigo-600 shadow-violet-500/25',

    lifeplus: 'from-amber-400 via-orange-500 to-amber-600 shadow-amber-500/25',
    'quick-invoice': 'from-blue-500 via-indigo-600 to-blue-600 shadow-blue-500/25',
};

interface App {
    id: number;
    name: string;
    slug: string;
    description?: string;
    url?: string;
    icon?: string;
}

const props = defineProps<{
    app: App;
}>();

const IconComponent = appIcons[props.app.slug] || CubeIcon;
const colorClass = appColors[props.app.slug] || 'from-slate-500 to-slate-600 shadow-slate-500/25';

function trackRecent(app: App) {
    try {
        const key = 'mg-recent-items';
        const raw = localStorage.getItem(key);
        let items: { id: number; name: string; slug: string; ts: number }[] = raw ? JSON.parse(raw) : [];
        items = items.filter(i => i.id !== app.id);
        items.unshift({ id: app.id, name: app.name, slug: app.slug, ts: Date.now() });
        if (items.length > 10) items = items.slice(0, 10);
        localStorage.setItem(key, JSON.stringify(items));
    } catch {}
}

function launch(app: App) {
    trackRecent(app);
    const launchUrl = route('workspace.launch', { application: app.id });
    window.location.href = launchUrl;
}
</script>

<template>
    <button
        @click="launch(app)"
        class="group flex flex-col items-center gap-3.5 p-5 bg-white dark:bg-[#0c1322] rounded-2xl border border-slate-200/80 dark:border-white/[0.08] hover:border-indigo-400/50 dark:hover:border-indigo-400/40 hover:shadow-xl hover:shadow-indigo-500/10 dark:hover:shadow-indigo-500/15 hover:-translate-y-1 hover:scale-[1.02] transition-all duration-300 cursor-pointer text-left relative overflow-hidden"
    >
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/[0.03] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        <div
            :class="[
                'w-13 h-13 sm:w-14 sm:h-14 flex items-center justify-center rounded-2xl bg-gradient-to-br text-white shadow-lg group-hover:scale-110 transition-all duration-300',
                colorClass,
            ]"
        >
            <component :is="IconComponent" class="w-6 h-6 sm:w-7 sm:h-7" />
        </div>
        <div class="text-center relative z-10">
            <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200 line-clamp-1">
                {{ app.name }}
            </span>
            <p v-if="app.description" class="text-[11px] text-slate-400 dark:text-slate-400 mt-1 leading-relaxed line-clamp-2">
                {{ app.description }}
            </p>
        </div>
    </button>
</template>
