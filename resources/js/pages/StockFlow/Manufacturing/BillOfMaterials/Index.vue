<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ boms: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const form = useForm({ sa_item_id: '', name: '', quantity: 1, uom: 'each', materials: [] as any[], notes: '' });
function reset() { form.reset(); showForm.value = false; }
function submit() { form.post(sf('boms.store'), { onSuccess: () => { reset(); success('BOM created.'); } }); }
</script>

<template>
    <Head title="Bill of Materials" />
    <StockFlowLayout title="Bill of Materials">
        <div class="max-w-6xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Bill of Materials</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded">{{ showForm ? 'Cancel' : 'New BOM' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div><label>Item ID</label><input v-model="form.sa_item_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Name</label><input v-model="form.name" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Output Quantity</label><input v-model="form.quantity" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>UOM</label><input v-model="form.uom" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Notes</label><textarea v-model="form.notes" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Item ID</th><th class="px-4 py-3 text-left">Qty</th><th class="px-4 py-3 text-left">UOM</th><th class="px-4 py-3 text-left">Version</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
                    <tbody>
                        <tr v-for="b in boms" :key="b.id" class="border-t">
                            <td class="px-4 py-3">{{ b.name }}</td>
                            <td class="px-4 py-3">{{ b.sa_item_id }}</td>
                            <td class="px-4 py-3">{{ b.quantity }}</td>
                            <td class="px-4 py-3">{{ b.uom }}</td>
                            <td class="px-4 py-3">{{ b.version }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs capitalize bg-gray-100">{{ b.status }}</span></td>
                        </tr>
                        <tr v-if="!boms.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">No BOMs created yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
