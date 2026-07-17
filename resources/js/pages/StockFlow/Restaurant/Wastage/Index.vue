<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ records: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const form = useForm({ sa_item_id: '', quantity: 0, reason: 'spoilage', unit_cost: 0, occurred_at: new Date().toISOString().split('T')[0], notes: '' });
function reset() { form.reset(); showForm.value = false; }
function submit() { form.post(sf('wastage.store'), { onSuccess: () => { reset(); success('Wastage recorded.'); } }); }
</script>

<template>
    <Head title="Wastage Tracking" />
    <StockFlowLayout title="Wastage Tracking">
        <div class="max-w-6xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Wastage Records</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded">{{ showForm ? 'Cancel' : 'Record Wastage' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div><label>Item ID</label><input v-model="form.sa_item_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Quantity</label><input v-model="form.quantity" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Reason</label><select v-model="form.reason" class="w-full border rounded px-3 py-2"><option value="spoilage">Spoilage</option><option value="trim">Trim/Prep Loss</option><option value="overproduction">Overproduction</option><option value="expiry">Expiry</option><option value="other">Other</option></select></div>
                    <div><label>Unit Cost</label><input v-model="form.unit_cost" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Date</label><input v-model="form.occurred_at" type="date" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Notes</label><textarea v-model="form.notes" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Record</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Item ID</th><th class="px-4 py-3 text-left">Qty</th><th class="px-4 py-3 text-left">Cost</th><th class="px-4 py-3 text-left">Reason</th><th class="px-4 py-3 text-left">Notes</th></tr></thead>
                    <tbody>
                        <tr v-for="r in records" :key="r.id" class="border-t">
                            <td class="px-4 py-3 text-sm">{{ r.occurred_at }}</td>
                            <td class="px-4 py-3">{{ r.sa_item_id }}</td>
                            <td class="px-4 py-3">{{ r.quantity }}</td>
                            <td class="px-4 py-3">{{ r.unit_cost }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs capitalize bg-yellow-100 text-yellow-800">{{ r.reason }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ r.notes || '-' }}</td>
                        </tr>
                        <tr v-if="!records.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">No wastage recorded.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
