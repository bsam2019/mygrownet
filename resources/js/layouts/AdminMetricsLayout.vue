<template>
    <AdminLayout>
        <div class="space-y-6">
            <PageHeader>
                <PageTitle>{{ title }}</PageTitle>
                <div class="flex items-center gap-4">
                    <slot name="actions" />
                </div>
            </PageHeader>
            
            <div class="grid gap-6">
                <slot />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { PageHeader, PageTitle } from '@/components/ui/page-header';

defineProps<{
    title: string;
}>();
</script>