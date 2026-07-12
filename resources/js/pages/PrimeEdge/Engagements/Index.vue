<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    engagements: Array<{
        id: string;
        typeLabel: string;
        status: string;
        agreedFee: { amount: number; currency: string } | null;
        startedAt: string | null;
        createdAt: string;
    }>;
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Engagements - PrimeEdge Advisory" />
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">My Engagements</h1>
            <p class="text-gray-600 mt-1">Active and past advisory engagements.</p>
        </div>
        <div v-if="engagements?.length" class="space-y-4">
            <div v-for="eng in engagements" :key="eng.id" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold text-gray-900">{{ eng.typeLabel }}</h3>
                            <span class="text-xs px-2 py-1 rounded-full font-medium" :class="eng.status === 'active' || eng.status === 'in_progress' ? 'bg-emerald-100 text-emerald-800' : eng.status === 'completed' ? 'bg-blue-100 text-blue-800' : eng.status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'">{{ eng.status }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Created {{ eng.createdAt }}</p>
                        <p v-if="eng.startedAt" class="text-sm text-gray-500 mt-1">Started {{ eng.startedAt }}</p>
                    </div>
                    <div class="text-right">
                        <p v-if="eng.agreedFee" class="text-lg font-bold text-emerald-700">{{ eng.agreedFee.currency }} {{ eng.agreedFee.amount.toLocaleString() }}</p>
                        <Link :href="route('primeedge.engagements.show', eng.id)" class="text-sm text-emerald-700 hover:underline">View details</Link>
                    </div>
                </div>
            </div>
        </div>
        <p v-else class="text-center text-gray-500 py-12">No engagements yet. Once your inquiry is approved, an engagement will be created.</p>
    </PrimeEdgeAppLayout>
</template>
