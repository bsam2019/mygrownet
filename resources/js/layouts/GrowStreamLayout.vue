<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GrowStreamHeader from '@/components/GrowStream/GrowStreamHeader.vue';
import GrowStreamFooter from '@/components/GrowStream/GrowStreamFooter.vue';

interface Props {
    title?: string;
    showPromo?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'GrowStream',
    showPromo: false,
});

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);
const isAuthenticated = computed(() => !!user.value);

const navItems = [
    { label: 'Home', href: () => route('growstream.home'), icon: 'home' },
    { label: 'Discovery', href: () => route('growstream.browse'), icon: 'explore' },
    { label: 'Studio', href: () => route('growstream.creator.dashboard'), icon: 'video_settings' },
    { label: 'Subscriptions', href: () => route('growstream.subscription'), icon: 'subscriptions' },
    { label: 'Profile', href: () => route('growstream.my-videos'), icon: 'person' },
];

const isActiveRoute = (href: string): boolean => {
    if (typeof window === 'undefined') return false;
    return window.location.pathname === new URL(href, window.location.origin).pathname;
};
</script>

<template>
    <Head :title="props.title" />

    <div class="gs-app bg-background text-on-background min-h-screen flex flex-col font-body-md antialiased">
        <GrowStreamHeader :show-promo="props.showPromo" />

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop pt-6 pb-24 md:pb-12">
            <slot />
        </main>

        <GrowStreamFooter />

        <!-- Bottom Nav (mobile only, authenticated) -->
        <nav v-if="isAuthenticated" class="md:hidden fixed bottom-0 w-full bg-surface-container-lowest border-t border-surface-container-highest flex justify-around py-2 z-50">
            <Link
                v-for="item in navItems"
                :key="item.label"
                :href="item.href()"
                class="flex flex-col items-center px-3 py-1"
                :class="isActiveRoute(item.href()) ? 'text-primary' : 'text-on-surface-variant'"
            >
                <span class="material-symbols-outlined text-xl" :data-weight="isActiveRoute(item.href()) ? 'fill' : 'regular'">{{ item.icon }}</span>
                <span class="font-label-sm text-[9px] uppercase tracking-wide mt-0.5">{{ item.label }}</span>
            </Link>
        </nav>
    </div>
</template>
