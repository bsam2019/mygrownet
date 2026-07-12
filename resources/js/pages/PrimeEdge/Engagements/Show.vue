<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    engagement: {
        id: string;
        typeLabel: string;
        description: string;
        scope: string | null;
        status: string;
        agreedFee: { amount: number; currency: string } | null;
        notes: string | null;
        createdAt: string;
        startedAt: string | null;
        completedAt: string | null;
    };
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head :title="engagement.typeLabel + ' - PrimeEdge Advisory'" />
        <div class="mb-6">
            <Link :href="route('primeedge.engagements.index')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Engagements</Link>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ engagement.typeLabel }}</h1>
                <span class="text-xs px-3 py-1 rounded-full font-medium" :class="engagement.status === 'active' || engagement.status === 'in_progress' ? 'bg-emerald-100 text-emerald-800' : engagement.status === 'completed' ? 'bg-blue-100 text-blue-800' : engagement.status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'">{{ engagement.status }}</span>
            </div>
        </div>
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <h2 class="font-semibold text-gray-900 mb-3">Details</h2>
                    <p class="text-sm text-gray-600 mb-2">{{ engagement.description }}</p>
                    <p v-if="engagement.scope" class="text-sm text-gray-600 mb-2"><span class="font-medium">Scope:</span> {{ engagement.scope }}</p>
                    <p class="text-sm text-gray-600 mb-2"><span class="font-medium text-gray-700">Created:</span> {{ engagement.createdAt }}</p>
                    <p class="text-sm text-gray-600 mb-2"><span class="font-medium text-gray-700">Started:</span> {{ engagement.startedAt || 'Not yet started' }}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium text-gray-700">Completed:</span> {{ engagement.completedAt || 'N/A' }}</p>
                    <p v-if="engagement.agreedFee" class="text-sm text-gray-600 mt-2"><span class="font-medium text-gray-700">Fee:</span> {{ engagement.agreedFee.currency }} {{ engagement.agreedFee.amount.toLocaleString() }}</p>
                    <p v-if="engagement.notes" class="text-sm text-gray-600 mt-2"><span class="font-medium text-gray-700">Notes:</span> {{ engagement.notes }}</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <Link :href="route('primeedge.documents.create')" class="block text-sm text-emerald-700 hover:underline">Upload Document</Link>
                        <Link :href="route('primeedge.compliance.index')" class="block text-sm text-emerald-700 hover:underline">View Compliance Tasks</Link>
                        <Link :href="route('primeedge.invoices.index')" class="block text-sm text-emerald-700 hover:underline">View Invoices</Link>
                    </div>
                </div>
            </div>
        </div>
    </PrimeEdgeAppLayout>
</template>
