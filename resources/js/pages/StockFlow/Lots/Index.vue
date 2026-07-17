<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ lots: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const form = useForm({ sa_item_id: '', lot_number: '', quantity: 0, manufacturing_date: '', expiry_date: '', received_date: '' });

function reset() { form.reset(); showForm.value = false; }
function submit() {
    form.post(sf('lots.store'), { onSuccess: () => { reset(); success('Lot created.'); } });
}
function remove(id: number) { if (confirm('Delete?')) router.delete(sf('lots.destroy', { id }), { preserveState: true, onSuccess: () => success('Lot deleted.') }); }
</script>

<template>
    <Head title="Lots / Batch Tracking" />
    <StockFlowLayout title="Lots / Batch Tracking">
        <div class="max-w-5xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Lot / Batch Tracking</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-emerald-600 text-white rounded">{{ showForm ? 'Cancel' : 'Add Lot' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div><label>Item ID</label><input v-model="form.sa_item_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Lot Number</label><input v-model="form.lot_number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Quantity</label><input v-model="form.quantity" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Manufacturing Date</label><input v-model="form.manufacturing_date" type="date" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Expiry Date</label><input v-model="form.expiry_date" type="date" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Received Date</label><input v-model="form.received_date" type="date" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">Create Lot</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Lot #</th><th class="px-4 py-3 text-left">Item ID</th><th class="px-4 py-3 text-left">Qty</th><th class="px-4 py-3 text-left">Expiry</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="l in lots" :key="l.id" class="border-t">
                            <td class="px-4 py-3">{{ l.lot_number }}</td>
                            <td class="px-4 py-3">{{ l.sa_item_id }}</td>
                            <td class="px-4 py-3">{{ l.current_quantity }} / {{ l.initial_quantity }}</td>
                            <td class="px-4 py-3">{{ l.expiry_date }}</td>
                            <td class="px-4 py-3"><span :class="l.status === 'active' ? 'text-green-600' : 'text-red-600'" class="capitalize">{{ l.status }}</span></td>
                            <td class="px-4 py-3 text-right"><button @click="remove(l.id)" class="text-red-600">Delete</button></td>
                        </tr>
                        <tr v-if="!lots.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">No lots tracked.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
