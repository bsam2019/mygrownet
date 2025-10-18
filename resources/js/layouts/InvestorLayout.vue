<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
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
    <AppLayout :breadcrumbs="breadcrumbs" :footer-nav-items="footerNavItems">
        <slot />
    </AppLayout>
</template>
