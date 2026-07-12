<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    inquiries: Array<{
        id: string;
        serviceDescription: string;
        preferredServiceType: string | null;
        status: string;
        createdAt: string;
    }>;
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Inquiries - PrimeEdge Advisory" />
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Inquiries</h1>
                <p class="text-gray-600 mt-1">Track your submitted inquiries and their status.</p>
            </div>
            <Link :href="route('primeedge.inquiries.create')" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-sm">New Inquiry</Link>
        </div>
        <div v-if="inquiries?.length" class="space-y-4">
            <div v-for="inquiry in inquiries" :key="inquiry.id" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ inquiry.preferredServiceType || 'General Inquiry' }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ inquiry.serviceDescription }}</p>
                        <p class="text-xs text-gray-400 mt-2">{{ inquiry.createdAt }}</p>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full font-medium" :class="inquiry.status === 'pending' ? 'bg-amber-100 text-amber-800' : inquiry.status === 'quoted' ? 'bg-blue-100 text-blue-800' : inquiry.status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">{{ inquiry.status }}</span>
                </div>
            </div>
        </div>
        <p v-else class="text-center text-gray-500 py-12">No inquiries yet. <Link :href="route('primeedge.inquiries.create')" class="text-emerald-700 font-medium hover:underline">Submit your first inquiry</Link>.</p>
    </PrimeEdgeAppLayout>
</template>
