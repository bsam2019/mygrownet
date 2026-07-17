<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ transactions: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const form = useForm({ sa_item_id: '', transaction_type: 'received', quantity: 0, patient_name: '', patient_id_number: '', prescription_number: '', sa_lot_id: '', notes: '' });
function reset() { form.reset(); showForm.value = false; }
function submit() { form.post(sf('controlled-medicines.store'), { onSuccess: () => { reset(); success('Recorded.'); } }); }
const typeBadge = (t: string) => t === 'received' ? 'bg-green-100 text-green-800' : t === 'issued' ? 'bg-blue-100 text-blue-800' : t === 'returned' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800';
</script>

<template>
    <Head title="Controlled Medicine Register" />
    <StockFlowLayout title="Controlled Medicine Register">
        <div class="max-w-6xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Controlled Medicine Register</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded">{{ showForm ? 'Cancel' : 'Record Transaction' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div><label>Item ID</label><input v-model="form.sa_item_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Type</label><select v-model="form.transaction_type" class="w-full border rounded px-3 py-2"><option value="received">Received</option><option value="issued">Issued</option><option value="returned">Returned</option><option value="disposed">Disposed</option></select></div>
                    <div><label>Quantity</label><input v-model="form.quantity" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Lot ID</label><input v-model="form.sa_lot_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Patient Name</label><input v-model="form.patient_name" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Patient ID #</label><input v-model="form.patient_id_number" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Prescription #</label><input v-model="form.prescription_number" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Notes</label><textarea v-model="form.notes" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Record</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Item ID</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Qty</th><th class="px-4 py-3 text-left">Balance</th><th class="px-4 py-3 text-left">Patient</th><th class="px-4 py-3 text-left">Prescription</th><th class="px-4 py-3 text-left">Staff</th></tr></thead>
                    <tbody>
                        <tr v-for="t in transactions" :key="t.id" class="border-t">
                            <td class="px-4 py-3 text-sm">{{ t.created_at }}</td>
                            <td class="px-4 py-3">{{ t.sa_item_id }}</td>
                            <td class="px-4 py-3"><span :class="typeBadge(t.transaction_type)" class="px-2 py-0.5 rounded text-xs capitalize">{{ t.transaction_type }}</span></td>
                            <td class="px-4 py-3">{{ t.quantity }}</td>
                            <td class="px-4 py-3">{{ t.balance_after }}</td>
                            <td class="px-4 py-3">{{ t.patient_name || '-' }}</td>
                            <td class="px-4 py-3">{{ t.prescription_number || '-' }}</td>
                            <td class="px-4 py-3">{{ t.staff_user_id }}</td>
                        </tr>
                        <tr v-if="!transactions.length"><td colspan="8" class="px-4 py-8 text-center text-gray-500">No transactions recorded.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
