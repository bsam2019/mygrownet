<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ recipes: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const form = useForm({ sa_item_id: '', name: '', yield_quantity: 1, yield_uom: 'portions', ingredients: [] as any[], instructions: '' });
function reset() { form.reset(); showForm.value = false; }
function submit() { form.post(sf('recipes.store'), { onSuccess: () => { reset(); success('Recipe created.'); } }); }
</script>

<template>
    <Head title="Recipes" />
    <StockFlowLayout title="Recipes">
        <div class="max-w-6xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Recipes</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-emerald-600 text-white rounded">{{ showForm ? 'Cancel' : 'New Recipe' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
                    <div><label>Item ID</label><input v-model="form.sa_item_id" type="number" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Name</label><input v-model="form.name" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Yield Qty</label><input v-model="form.yield_quantity" type="number" step="0.01" class="w-full border rounded px-3 py-2" /></div>
                    <div><label>Yield UOM</label><input v-model="form.yield_uom" class="w-full border rounded px-3 py-2" /></div>
                    <div class="col-span-2"><label>Instructions</label><textarea v-model="form.instructions" class="w-full border rounded px-3 py-2"></textarea></div>
                    <div class="col-span-2"><button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">Save</button></div>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Item ID</th><th class="px-4 py-3 text-left">Yield</th><th class="px-4 py-3 text-left">Difficulty</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
                    <tbody>
                        <tr v-for="r in recipes" :key="r.id" class="border-t">
                            <td class="px-4 py-3">{{ r.name }}</td>
                            <td class="px-4 py-3">{{ r.sa_item_id }}</td>
                            <td class="px-4 py-3">{{ r.yield_quantity }} {{ r.yield_uom }}</td>
                            <td class="px-4 py-3 capitalize">{{ r.difficulty }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs capitalize" :class="r.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100'">{{ r.status }}</span></td>
                        </tr>
                        <tr v-if="!recipes.length"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No recipes yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
