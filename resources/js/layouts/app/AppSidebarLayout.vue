<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    footerNavItems?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    footerNavItems: () => []
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :footer-nav-items="footerNavItems" />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
