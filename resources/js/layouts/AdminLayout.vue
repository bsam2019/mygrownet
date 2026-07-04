<script setup lang="ts">
import { watch } from 'vue';
import AppLayout from '@/layouts/admin/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    title: 'Admin',
});

watch(() => props.title, (t) => {
    document.title = t ? `${t} - MyGrowNet` : 'MyGrowNet';
}, { immediate: true });
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>

