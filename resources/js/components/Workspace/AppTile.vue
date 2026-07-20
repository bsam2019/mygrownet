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
    growmarket: ShoppingBagIcon,
    growbuilder: GlobeAltIcon,
    growbackup: CloudIcon,
    zamstay: HomeModernIcon,
    primeedge: CommandLineIcon,
    library: BookOpenIcon,
    messaging: CubeIcon,
    employee_portal: CubeIcon,
};

const appColors: Record<string, string> = {
    grownet: 'from-green-500 to-green-600',
    growfinance: 'from-indigo-500 to-indigo-600',
    bizdocs: 'from-blue-500 to-blue-600',
    stockflow: 'from-teal-500 to-teal-600',
    bms: 'from-purple-500 to-purple-600',
    bizboost: 'from-orange-500 to-orange-600',
    growmart: 'from-red-500 to-red-600',
    growmarket: 'from-red-500 to-rose-600',
    growbuilder: 'from-cyan-500 to-cyan-600',
    growbackup: 'from-sky-500 to-sky-600',
    zamstay: 'from-emerald-500 to-emerald-600',
    primeedge: 'from-violet-500 to-violet-600',
    library: 'from-yellow-500 to-amber-600',
    messaging: 'from-gray-500 to-gray-600',
    employee_portal: 'from-lime-500 to-lime-600',
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
const colorClass = appColors[props.app.slug] || 'from-blue-500 to-blue-600';

function launch(app: App) {
    router.post(route('workspace.launch', { application: app.id }));
}
</script>

<template>
    <button
        @click="launch(app)"
        class="group flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer"
    >
        <div
            :class="[
                'w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-sm group-hover:shadow-md transition-shadow',
                colorClass,
            ]"
        >
            <component :is="IconComponent" class="w-6 h-6" />
        </div>
        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 text-center">{{ app.name }}</span>
    </button>
</template>
