<script setup lang="ts">
import { computed, type Component } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import BottomSheet from './BottomSheet.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    SparklesIcon,
    PhotoIcon,
    RocketLaunchIcon,
    ChatBubbleLeftRightIcon,
    LightBulbIcon,
    ClockIcon,
    BuildingStorefrontIcon,
    QrCodeIcon,
    LinkIcon,
    RectangleStackIcon,
    AcademicCapIcon,
    UserGroupIcon,
    MapPinIcon,
    CodeBracketIcon,
    ShoppingCartIcon,
    PaintBrushIcon,
    Cog6ToothIcon,
    ArrowUpCircleIcon,
    ChartBarIcon,
    CalendarIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

interface NavItem {
    name: string;
    href: string;
    icon: Component;
    description?: string;
}

interface NavGroup {
    name: string;
    items: NavItem[];
}

interface Props {
    modelValue: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();

const page = usePage();
const { light } = useHaptics();

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

// Navigation groups
const navGroups: NavGroup[] = [
    {
        name: 'Business',
        items: [
            { name: 'Posts', href: '/bizboost/posts', icon: DocumentTextIcon, description: 'Create & schedule posts' },
            { name: 'Calendar', href: '/bizboost/calendar', icon: CalendarIcon, description: 'View your schedule' },
            { name: 'Analytics', href: '/bizboost/analytics', icon: ChartBarIcon, description: 'Track performance' },
        ],
    },
    {
        name: 'Tools',
        items: [
            { name: 'AI Content', href: '/bizboost/ai', icon: SparklesIcon, description: 'Generate content with AI' },
            { name: 'Templates', href: '/bizboost/templates', icon: PhotoIcon, description: 'Design templates' },
            { name: 'Campaigns', href: '/bizboost/campaigns', icon: RocketLaunchIcon, description: 'Marketing campaigns' },
            { name: 'WhatsApp', href: '/bizboost/whatsapp/broadcasts', icon: ChatBubbleLeftRightIcon, description: 'Broadcast messages' },
            { name: 'AI Advisor', href: '/bizboost/advisor', icon: LightBulbIcon, description: 'Get business advice' },
            { name: 'Reminders', href: '/bizboost/reminders', icon: ClockIcon, description: 'Follow-up reminders' },
        ],
    },
    {
        name: 'Advanced',
        items: [
            { name: 'Business Profile', href: '/bizboost/business/profile', icon: BuildingStorefrontIcon },
            { name: 'Mini-Website', href: '/bizboost/business/mini-website', icon: QrCodeIcon },
            { name: 'Integrations', href: '/bizboost/integrations', icon: LinkIcon },
            { name: 'Industry Kits', href: '/bizboost/industry-kits', icon: RectangleStackIcon },
            { name: 'Learning Hub', href: '/bizboost/learning', icon: AcademicCapIcon },
            { name: 'Team', href: '/bizboost/team', icon: UserGroupIcon },
            { name: 'Locations', href: '/bizboost/locations', icon: MapPinIcon },
            { name: 'Marketplace', href: '/bizboost/marketplace', icon: ShoppingCartIcon },
        ],
    },
];

const isActive = (href: string): boolean => {
    return page.url.startsWith(href);
};

const handleNavClick = () => {
    light();
    isOpen.value = false;
};
</script>

<template>
    <BottomSheet v-model="isOpen" title="More" :max-height="0.85">
        <div class="pb-safe">
            <!-- Navigation groups -->
            <div v-for="group in navGroups" :key="group.name" class="py-3">
                <h3 class="px-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                    {{ group.name }}
                </h3>
                <div class="grid grid-cols-3 gap-2 px-3">
                    <Link
                        v-for="item in group.items"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            'flex flex-col items-center p-3 rounded-xl transition-colors',
                            isActive(item.href)
                                ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                        ]"
                        @click="handleNavClick"
                    >
                        <component
                            :is="item.icon"
                            class="h-6 w-6 mb-1"
                            aria-hidden="true"
                        />
                        <span class="text-xs font-medium text-center leading-tight">
                            {{ item.name }}
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-slate-200 dark:border-slate-700 my-2" />

            <!-- Settings & Upgrade -->
            <div class="px-3 pb-4 space-y-2">
                <Link
                    href="/bizboost/business/settings"
                    class="flex items-center gap-3 p-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    @click="handleNavClick"
                >
                    <Cog6ToothIcon class="h-6 w-6" aria-hidden="true" />
                    <span class="font-medium">Settings</span>
                </Link>
                
                <Link
                    href="/bizboost/upgrade"
                    class="flex items-center gap-3 p-3 rounded-xl bg-gradient-to-r from-violet-600 to-violet-700 text-white shadow-lg shadow-violet-500/30 transition-all hover:shadow-violet-500/40"
                    @click="handleNavClick"
                >
                    <ArrowUpCircleIcon class="h-6 w-6" aria-hidden="true" />
                    <span class="font-medium">Upgrade Plan</span>
                </Link>
            </div>
        </div>
    </BottomSheet>
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
