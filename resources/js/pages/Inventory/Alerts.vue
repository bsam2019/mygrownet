<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { ExclamationTriangleIcon, CheckIcon } from '@heroicons/vue/24/outline';

interface Alert { id: number; type: string; threshold_value: number; current_value: number; is_acknowledged: boolean; item: { id: number; name: string; sku: string | null }; }
const props = defineProps<{ alerts: Alert[] }>();

const acknowledge = (alert: Alert) => router.post(route('inventory.alerts.acknowledge', alert.id));
const getAlertColor = (type: string) => type === 'out_of_stock' ? 'bg-red-100 text-red-800 border-red-200' : type === 'low_stock' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-blue-100 text-blue-800 border-blue-200';
</script>

<template>
    <InventoryLayout title="Alerts">
        <Head title="Inventory Alerts" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">Stock Alerts</h1>
                <div class="space-y-3">
                    <div v-for="alert in alerts" :key="alert.id" :class="[getAlertColor(alert.type), 'flex items-center justify-between rounded-xl border p-4']">
                        <div class="flex items-center gap-3">
                            <ExclamationTriangleIcon class="h-6 w-6" aria-hidden="true" />
                            <div>
                                <p class="font-medium">{{ alert.item.name }}</p>
                                <p class="text-sm opacity-75">{{ alert.type.replace('_', ' ') }} - Current: {{ alert.current_value }}, Threshold: {{ alert.threshold_value }}</p>
                            </div>
                        </div>
                        <button v-if="!alert.is_acknowledged" @click="acknowledge(alert)" class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-gray-50">
                            <CheckIcon class="h-4 w-4" aria-hidden="true" /> Acknowledge
                        </button>
                        <span v-else class="text-sm opacity-75">Acknowledged</span>
                    </div>
                    <div v-if="alerts.length === 0" class="rounded-xl bg-white p-12 text-center text-gray-500 shadow-sm">
                        <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                        <p class="mt-2">No alerts</p>
                    </div>
                </div>
            </div>
        </div>
    </InventoryLayout>
</template>
