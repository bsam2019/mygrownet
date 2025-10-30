<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
</script>

<template>
    <Head title="Venture Analytics" />

    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Venture Analytics</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Analytics and reporting for ventures
                </p>

                <div class="mt-6 rounded-lg bg-white p-12 text-center shadow">
                    <p class="text-gray-500">Analytics dashboard coming soon...</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
