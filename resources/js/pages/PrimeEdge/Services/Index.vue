<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    services: Array<{
        id: string;
        name: string;
        description: string;
        is_active: boolean;
    }>;
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Services - PrimeEdge Advisory" />
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Services</h1>
                <p class="text-gray-600 mt-1">Available advisory services.</p>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="service in services" :key="service.id" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <h3 class="font-semibold text-gray-900">{{ service.name }}</h3>
                <p class="text-sm text-gray-600 mt-2">{{ service.description }}</p>
                <span class="inline-block mt-3 text-xs px-2 py-1 rounded-full" :class="service.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'">{{ service.is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </PrimeEdgeAppLayout>
</template>
