<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ rates: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ from_currency: 'ZMW', to_currency: 'USD', rate: 0, effective_date: '' });

function edit(r: any) {
    editingId.value = r.id; form.from_currency = r.from_currency; form.to_currency = r.to_currency; form.rate = r.rate; form.effective_date = r.effective_date; showForm.value = true;
}

function reset() { form.reset(); editingId.value = null; showForm.value = false; }

function submit() {
    const route = editingId.value ? sf('exchange-rates.update', { id: editingId.value }) : sf('exchange-rates.store');
    form[editingId.value ? 'put' : 'post'](route, { onSuccess: () => { reset(); success('Rate saved.'); } });
}

function remove(id: number) {
    if (confirm('Delete?')) router.delete(sf('exchange-rates.destroy', { id }), { preserveState: true, onSuccess: () => success('Rate deleted.') });
}
</script>

<template>
    <Head title="Exchange Rates" />
    <StockFlowLayout title="Exchange Rates">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Exchange Rates</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-emerald-600 text-white rounded">{{ showForm ? 'Cancel' : 'Add Rate' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div><label>From</label><input v-model="form.from_currency" class="w-full border rounded px-3 py-2" maxlength="3" /></div>
                    <div><label>To</label><input v-model="form.to_currency" class="w-full border rounded px-3 py-2" maxlength="3" /></div>
                    <div><label>Rate</label><input v-model="form.rate" type="number" step="0.000001" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Effective Date</label><input v-model="form.effective_date" type="date" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">{{ editingId ? 'Update' : 'Create' }}</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">From</th><th class="px-4 py-3 text-left">To</th><th class="px-4 py-3 text-left">Rate</th><th class="px-4 py-3 text-left">Date</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="r in rates" :key="r.id" class="border-t">
                            <td class="px-4 py-3">{{ r.from_currency }}</td>
                            <td class="px-4 py-3">{{ r.to_currency }}</td>
                            <td class="px-4 py-3">{{ r.rate }}</td>
                            <td class="px-4 py-3">{{ r.effective_date }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button @click="edit(r)" class="text-blue-600">Edit</button>
                                <button @click="remove(r.id)" class="text-red-600">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!rates.length"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No exchange rates.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
