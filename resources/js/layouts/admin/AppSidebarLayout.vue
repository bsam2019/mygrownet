<script setup lang="ts">
import AdminSidebar from '@/components/AdminSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ToastContainer from '@/components/Shared/ToastContainer.vue';
import { useToast } from '@/composables/useToast';
import type { BreadcrumbItemType } from '@/types';
import { ref, onMounted, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => []
});

const page = usePage();
const { toast } = useToast();
const isMobile = ref(false);
const savedState = localStorage.getItem('admin.sidebarCollapsed');
const sidebarCollapsed = ref(savedState === 'true');
const flash = computed(() => (page.props as any).flash);

watch(flash, (f) => {
    if (!f) return;
    if (f.success) toast.success(f.success, 5000);
    if (f.error) toast.error(f.error, 8000);
    if (f.warning) toast.warning(f.warning, 5000);
    if (f.info) toast.info(f.info, 5000);
}, { immediate: true, deep: true });

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
    <ToastContainer />
</template>
