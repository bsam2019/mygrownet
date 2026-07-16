<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ warehouses: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ name: '', code: '', address: '', city: '', country: '', contact_person: '', phone: '', is_default: false });

function edit(w: any) { editingId.value = w.id; form.name = w.name; form.code = w.code || ''; form.address = w.address || ''; form.city = w.city || ''; form.country = w.country || ''; form.contact_person = w.contact_person || ''; form.phone = w.phone || ''; form.is_default = w.is_default; showForm.value = true; }
function reset() { form.reset(); editingId.value = null; showForm.value = false; }
function submit() {
    const route = editingId.value ? sf('warehouses.update', { id: editingId.value }) : sf('warehouses.store');
    form[editingId.value ? 'put' : 'post'](route, { onSuccess: () => { reset(); success.value = 'Warehouse saved.'; } });
}
function remove(id: number) { if (confirm('Delete?')) router.delete(sf('warehouses.destroy', { id }), { preserveState: true, onSuccess: () => success.value = 'Warehouse deleted.' }); }
</script>

<template>
    <Head title="Warehouses" />
    <StockFlowLayout title="Warehouses">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Warehouses</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded">{{ showForm ? 'Cancel' : 'Add Warehouse' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label>Name</label><input v-model="form.name" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Code</label><input v-model="form.code" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Phone</label><input v-model="form.phone" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>City</label><input v-model="form.city" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Country</label><input v-model="form.country" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Address</label><textarea v-model="form.address" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="col-span-2"><label>Contact Person</label><input v-model="form.contact_person" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label><input v-model="form.is_default" type="checkbox" /> Set as default</label></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">{{ editingId ? 'Update' : 'Create' }}</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">City</th><th class="px-4 py-3 text-left">Default</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="w in warehouses" :key="w.id" class="border-t">
                            <td class="px-4 py-3">{{ w.name }}</td><td class="px-4 py-3">{{ w.code }}</td><td class="px-4 py-3">{{ w.city }}</td><td class="px-4 py-3">{{ w.is_default ? '✓' : '' }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button @click="edit(w)" class="text-blue-600">Edit</button>
                                <button @click="remove(w.id)" class="text-red-600">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!warehouses.length"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No warehouses configured.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
