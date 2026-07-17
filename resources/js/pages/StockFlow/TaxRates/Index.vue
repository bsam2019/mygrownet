<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ taxRates: any[] }>();
const { success, error: notifyError } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ name: '', rate: 0, type: 'inclusive', is_default: false });

function edit(rate: any) {
    editingId.value = rate.id;
    form.name = rate.name;
    form.rate = rate.rate;
    form.type = rate.type;
    form.is_default = rate.is_default;
    showForm.value = true;
}

function reset() {
    form.reset();
    editingId.value = null;
    showForm.value = false;
}

function submit() {
    const route = editingId.value ? sf('tax-rates.update', { id: editingId.value }) : sf('tax-rates.store');
    form[editingId.value ? 'put' : 'post'](route, { onSuccess: () => { reset(); success('Tax rate saved.'); } });
}

function remove(id: number) {
    if (confirm('Delete this tax rate?')) {
        router.delete(sf('tax-rates.destroy', { id }), { preserveState: true, onSuccess: () => success('Tax rate deleted.') });
    }
}
</script>

<template>
    <Head title="Tax Rates" />
    <StockFlowLayout title="Tax Rates">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Tax Rates</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    {{ showForm ? 'Cancel' : 'Add Tax Rate' }}
                </button>
            </div>

            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Name</label>
                        <input v-model="form.name" class="w-full border rounded px-3 py-2" placeholder="e.g. VAT 16%" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Rate (%)</label>
                        <input v-model="form.rate" type="number" step="0.01" min="0" max="100" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Type</label>
                        <select v-model="form.type" class="w-full border rounded px-3 py-2">
                            <option value="inclusive">Inclusive</option>
                            <option value="exclusive">Exclusive</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2">
                        <input v-model="form.is_default" type="checkbox" />
                        <span>Set as default</span>
                    </label>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">{{ editingId ? 'Update' : 'Create' }}</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr><th class="px-4 py-3 text-left text-sm font-medium">Name</th><th class="px-4 py-3 text-left text-sm font-medium">Rate</th><th class="px-4 py-3 text-left text-sm font-medium">Type</th><th class="px-4 py-3 text-left text-sm font-medium">Default</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in taxRates" :key="r.id" class="border-t">
                            <td class="px-4 py-3">{{ r.name }}</td>
                            <td class="px-4 py-3">{{ r.rate }}%</td>
                            <td class="px-4 py-3 capitalize">{{ r.type }}</td>
                            <td class="px-4 py-3">{{ r.is_default ? '✓' : '' }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button @click="edit(r)" class="text-blue-600 hover:underline">Edit</button>
                                <button @click="remove(r.id)" class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!taxRates.length"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No tax rates configured.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
