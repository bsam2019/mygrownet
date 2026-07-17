<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ workOrders: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const form = useForm({ sa_item_id: '', order_number: '', quantity: 1, sa_bom_id: '', due_date: '', notes: '' });
function reset() { form.reset(); showForm.value = false; }
function submit() { form.post(sf('work-orders.store'), { onSuccess: () => { reset(); success('Work order created.'); } }); }
</script>

<template>
    <Head title="Work Orders" />
    <StockFlowLayout title="Work Orders">
        <div class="max-w-6xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Work Orders</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded">{{ showForm ? 'Cancel' : 'New Work Order' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div><label>Item ID</label><input v-model="form.sa_item_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Order #</label><input v-model="form.order_number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Quantity</label><input v-model="form.quantity" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>BOM ID</label><input v-model="form.sa_bom_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Due Date</label><input v-model="form.due_date" type="date" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Notes</label><textarea v-model="form.notes" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Order #</th><th class="px-4 py-3 text-left">Item ID</th><th class="px-4 py-3 text-left">Qty</th><th class="px-4 py-3 text-left">Completed</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Due</th></tr></thead>
                    <tbody>
                        <tr v-for="wo in workOrders" :key="wo.id" class="border-t">
                            <td class="px-4 py-3">{{ wo.order_number }}</td>
                            <td class="px-4 py-3">{{ wo.sa_item_id }}</td>
                            <td class="px-4 py-3">{{ wo.quantity }}</td>
                            <td class="px-4 py-3">{{ wo.completed_quantity }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs capitalize" :class="wo.status === 'completed' ? 'bg-green-100 text-green-800' : wo.status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100'">{{ wo.status }}</span></td>
                            <td class="px-4 py-3">{{ wo.due_date || '-' }}</td>
                        </tr>
                        <tr v-if="!workOrders.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">No work orders yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
