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
    grownet: 'from-emerald-500 to-emerald-600',
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
    description?: string;
    url?: string;
    icon?: string;
}

const props = defineProps<{
    app: App;
}>();

const IconComponent = appIcons[props.app.slug] || CubeIcon;
const colorClass = appColors[props.app.slug] || 'from-gray-500 to-gray-600';

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
    router.post(route('workspace.launch', { application: app.id }));
}
</script>

<template>
    <button
        @click="launch(app)"
        class="group flex flex-col items-center gap-3 p-5 bg-white rounded-xl border border-gray-200/80 hover:border-gray-300 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer text-left"
    >
        <div
            :class="[
                'w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-sm group-hover:shadow-md transition-all duration-200',
                colorClass,
            ]"
        >
            <component :is="IconComponent" class="w-6 h-6" />
        </div>
        <div class="text-center">
            <span class="text-sm font-semibold text-gray-800 group-hover:text-gray-900">{{ app.name }}</span>
            <p v-if="app.description" class="text-xs text-gray-400 mt-1 leading-relaxed line-clamp-2">{{ app.description }}</p>
        </div>
    </button>
</template>
