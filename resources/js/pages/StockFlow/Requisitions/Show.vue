<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ requisition: any }>();
const { sf } = useStockflowRoute();
</script>

<template>
    <Head title="Requisition Details" />
    <StockFlowLayout title="Requisition Details">
        <div class="max-w-3xl mx-auto py-6 px-4">
            <Link :href="sf('requisitions.index')" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back</Link>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">{{ requisition.requisition_number }}</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500">Status</dt><dd class="capitalize font-medium">{{ requisition.status }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Date Required</dt><dd>{{ requisition.date_required || 'N/A' }}</dd></div>
                    <div class="col-span-2"><dt class="text-sm text-gray-500">Notes</dt><dd>{{ requisition.notes || 'None' }}</dd></div>
                </dl>
            </div>
        </div>
    </StockFlowLayout>
</template>
