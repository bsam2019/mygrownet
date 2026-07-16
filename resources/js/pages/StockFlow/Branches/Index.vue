<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ branches: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ name: '', code: '', phone: '', email: '', address: '', city: '', country: '', is_head_office: false, is_active: true });

function edit(b: any) { editingId.value = b.id; form.name = b.name; form.code = b.code || ''; form.phone = b.phone || ''; form.email = b.email || ''; form.address = b.address || ''; form.city = b.city || ''; form.country = b.country || ''; form.is_head_office = b.is_head_office; form.is_active = b.is_active; showForm.value = true; }
function reset() { form.reset(); editingId.value = null; showForm.value = false; }
function submit() {
    const route = editingId.value ? sf('branches.update', { id: editingId.value }) : sf('branches.store');
    form[editingId.value ? 'put' : 'post'](route, { onSuccess: () => { reset(); success.value = 'Branch saved.'; } });
}
function remove(id: number) { if (confirm('Delete?')) router.delete(sf('branches.destroy', { id }), { preserveState: true, onSuccess: () => success.value = 'Branch deleted.' }); }
</script>

<template>
    <Head title="Branches" />
    <StockFlowLayout title="Branches">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Branches</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-blue-600 text-white rounded">{{ showForm ? 'Cancel' : 'Add Branch' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label>Name</label><input v-model="form.name" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Code</label><input v-model="form.code" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Phone</label><input v-model="form.phone" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Email</label><input v-model="form.email" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>City</label><input v-model="form.city" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Country</label><input v-model="form.country" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Address</label><textarea v-model="form.address" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="col-span-2 flex gap-6">
                        <label><input v-model="form.is_head_office" type="checkbox" /> Head Office</label>
                        <label><input v-model="form.is_active" type="checkbox" /> Active</label>
                    </div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">{{ editingId ? 'Update' : 'Create' }}</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">City</th><th class="px-4 py-3 text-left">Head Office</th><th class="px-4 py-3 text-left">Active</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="b in branches" :key="b.id" class="border-t">
                            <td class="px-4 py-3">{{ b.name }}</td><td class="px-4 py-3">{{ b.code }}</td><td class="px-4 py-3">{{ b.city }}</td><td class="px-4 py-3">{{ b.is_head_office ? '✓' : '' }}</td><td class="px-4 py-3">{{ b.is_active ? '✓' : '' }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button @click="edit(b)" class="text-blue-600">Edit</button>
                                <button @click="remove(b.id)" class="text-red-600">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!branches.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">No branches configured.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
