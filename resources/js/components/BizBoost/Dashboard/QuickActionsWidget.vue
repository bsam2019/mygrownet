<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    DocumentTextIcon,
    SparklesIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    UserPlusIcon,
    ShoppingBagIcon,
    RocketLaunchIcon,
    CalendarIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

interface QuickAction {
    id: string;
    name: string;
    description: string;
    icon: any;
    href: string;
    color: string;
    gradient: string;
}

interface Props {
    pendingTasks?: number;
    scheduledPosts?: number;
    lowStockProducts?: number;
}

const props = withDefaults(defineProps<Props>(), {
    pendingTasks: 0,
    scheduledPosts: 0,
    lowStockProducts: 0,
});

const primaryActions: QuickAction[] = [
    {
        id: 'new-post',
        name: 'New Post',
        description: 'Create & schedule',
        icon: DocumentTextIcon,
        href: '/bizboost/posts/create',
        color: 'text-violet-600',
        gradient: 'from-violet-500 to-purple-600',
    },
    {
        id: 'ai-content',
        name: 'AI Content',
        description: 'Generate with AI',
        icon: SparklesIcon,
        href: '/bizboost/ai',
        color: 'text-pink-600',
        gradient: 'from-pink-500 to-rose-600',
    },
    {
        id: 'record-sale',
        name: 'Record Sale',
        description: 'Log transaction',
        icon: CurrencyDollarIcon,
        href: '/bizboost/sales/create',
        color: 'text-emerald-600',
        gradient: 'from-emerald-500 to-teal-600',
    },
    {
        id: 'analytics',
        name: 'Analytics',
        description: 'View insights',
        icon: ChartBarIcon,
        href: '/bizboost/analytics',
        color: 'text-blue-600',
        gradient: 'from-blue-500 to-cyan-600',
    },
];

const secondaryActions = [
    { name: 'Add Customer', icon: UserPlusIcon, href: '/bizboost/customers/create' },
    { name: 'Add Product', icon: ShoppingBagIcon, href: '/bizboost/products/create' },
    { name: 'New Campaign', icon: RocketLaunchIcon, href: '/bizboost/campaigns/create' },
    { name: 'Calendar', icon: CalendarIcon, href: '/bizboost/calendar' },
];

// Contextual suggestions based on business state
const suggestions = computed(() => {
    const items = [];
    if (props.pendingTasks > 0) {
        items.push({
            text: `You have ${props.pendingTasks} pending follow-ups`,
            href: '/bizboost/reminders',
            action: 'View tasks',
        });
    }
    if (props.scheduledPosts > 0) {
        items.push({
            text: `${props.scheduledPosts} posts scheduled for today`,
            href: '/bizboost/calendar',
            action: 'View calendar',
        });
    }
    if (props.lowStockProducts > 0) {
        items.push({
            text: `${props.lowStockProducts} products running low`,
            href: '/bizboost/products?filter=low-stock',
            action: 'Check inventory',
        });
    }
    return items;
});

// Time-based greeting
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});
</script>

<template>
    <div class="space-y-4">
        <!-- Contextual suggestions -->
        <div v-if="suggestions.length" class="space-y-2">
            <div
                v-for="suggestion in suggestions"
                :key="suggestion.text"
                class="flex items-center justify-between p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800"
            >
                <p class="text-sm text-amber-800 dark:text-amber-200">{{ suggestion.text }}</p>
                <Link
                    :href="suggestion.href"
                    class="text-xs font-medium text-amber-700 dark:text-amber-300 hover:text-amber-900 dark:hover:text-amber-100 flex items-center gap-1"
                >
                    {{ suggestion.action }}
                    <ArrowRightIcon class="h-3 w-3" aria-hidden="true" />
                </Link>
            </div>
        </div>

        <!-- Primary actions grid -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <Link
                v-for="action in primaryActions"
                :key="action.id"
                :href="action.href"
                class="group relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-4 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700 hover:shadow-lg hover:ring-2 hover:ring-violet-200 dark:hover:ring-violet-700 transition-all duration-300"
            >
                <!-- Gradient background on hover -->
                <div :class="[
                    'absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-5 dark:group-hover:opacity-10 transition-opacity duration-300',
                    action.gradient
                ]"></div>

                <div class="relative">
                    <div :class="[
                        'inline-flex p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 group-hover:scale-110 transition-transform duration-300',
                        action.color
                    ]">
                        <component :is="action.icon" class="h-5 w-5" aria-hidden="true" />
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">{{ action.name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ action.description }}</p>
                </div>
            </Link>
        </div>

        <!-- Secondary actions -->
        <div class="flex flex-wrap gap-2">
            <Link
                v-for="action in secondaryActions"
                :key="action.name"
                :href="action.href"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors text-sm font-medium"
            >
                <component :is="action.icon" class="h-4 w-4" aria-hidden="true" />
                {{ action.name }}
            </Link>
        </div>
    </div>
</template>
