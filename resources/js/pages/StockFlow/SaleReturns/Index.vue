<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ returns: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const form = useForm({ sa_sale_id: '', reason: '', total_refund: 0, return_date: '', refund_method: 'cash', notes: '', items: [{ sa_item_id: '', quantity: 0, unit_price: 0, subtotal: 0, condition: '' }] });
function addItem() { form.items.push({ sa_item_id: '', quantity: 0, unit_price: 0, subtotal: 0, condition: '' }); }
function removeItem(i: number) { form.items.splice(i, 1); }
function reset() { form.reset(); form.items = [{ sa_item_id: '', quantity: 0, unit_price: 0, subtotal: 0, condition: '' }]; showForm.value = false; }
function submit() { form.post(sf('sale-returns.store'), { onSuccess: () => { reset(); success('Return processed.'); } }); }
</script>

<template>
    <Head title="Sales Returns" />
    <StockFlowLayout title="Sales Returns">
        <div class="max-w-5xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Sales Returns</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded">{{ showForm ? 'Cancel' : 'New Return' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label>Sale ID</label><input v-model="form.sa_sale_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                        <div><label>Reason</label><input v-model="form.reason" class="w-full border rounded px-3 py-2" /></div>
                        <div><label>Total Refund</label><input v-model="form.total_refund" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                        <div><label>Refund Method</label><select v-model="form.refund_method" class="w-full border rounded px-3 py-2"><option value="cash">Cash</option><option value="mobile_money">Mobile Money</option><option value="card">Card</option><option value="transfer">Transfer</option></select></div>
                        <div><label>Return Date</label><input v-model="form.return_date" type="date" class="w-full border rounded px-3 py-2" /></div>
                    </div>
                    <div><label>Notes</label><textarea v-model="form.notes" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="font-medium">Items</div>
                    <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-5 gap-2 items-end">
                        <div><label>Item ID</label><input v-model="item.sa_item_id" type="number" class="w-full border rounded px-2 py-1" /></div>
                        <div><label>Qty</label><input v-model="item.quantity" type="number" step="0.01" class="w-full border rounded px-2 py-1" /></div>
                        <div><label>Unit Price</label><input v-model="item.unit_price" type="number" step="0.01" class="w-full border rounded px-2 py-1" /></div>
                        <div><label>Condition</label><input v-model="item.condition" class="w-full border rounded px-2 py-1" /></div>
                        <button type="button" @click="removeItem(i)" class="text-red-600 text-sm">Remove</button>
                    </div>
                    <button type="button" @click="addItem" class="text-blue-600 text-sm">+ Add Item</button>
                    <div><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Process Return</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Sale</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Reason</th><th class="px-4 py-3 text-left">Refund</th></tr></thead>
                    <tbody>
                        <tr v-for="r in returns" :key="r.id" class="border-t">
                            <td class="px-4 py-3">{{ r.return_number }}</td>
                            <td class="px-4 py-3">{{ r.sa_sale_id }}</td>
                            <td class="px-4 py-3">{{ r.return_date }}</td>
                            <td class="px-4 py-3">{{ r.reason }}</td>
                            <td class="px-4 py-3">{{ r.total_refund }}</td>
                        </tr>
                        <tr v-if="!returns.length"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No returns.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
