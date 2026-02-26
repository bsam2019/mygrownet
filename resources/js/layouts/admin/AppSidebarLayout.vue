<script setup lang="ts">
import AdminSidebar from '@/components/AdminSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { ref, onMounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => []
});

const isMobile = ref(false);
const savedState = localStorage.getItem('admin.sidebarCollapsed');
const sidebarCollapsed = ref(savedState === 'true');

const handleSidebarToggle = (collapsed: boolean) => {
    sidebarCollapsed.value = collapsed;
};

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="flex min-h-screen w-full">
        <AdminSidebar @update:collapsed="handleSidebarToggle" />
        <div 
            class="flex-1 flex flex-col transition-all duration-300" 
            :class="isMobile ? 'ml-0' : (sidebarCollapsed ? 'ml-16' : 'ml-64')"
        >
            <div class="flex h-full w-full flex-col">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <main class="flex-1 p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
