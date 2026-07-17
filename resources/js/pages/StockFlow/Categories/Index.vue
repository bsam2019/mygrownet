<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ categories: any[], flatCategories: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();
const showForm = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ name: '', description: '', parent_id: '' as any, sort_order: 0 });

function edit(c: any) { editingId.value = c.id; form.name = c.name; form.description = c.description || ''; form.parent_id = c.parent_id ?? ''; form.sort_order = c.sort_order; showForm.value = true; }
function reset() { form.reset(); editingId.value = null; showForm.value = false; }
function submit() {
    const route = editingId.value ? sf('categories.update', { id: editingId.value }) : sf('categories.store');
    form[editingId.value ? 'put' : 'post'](route, { onSuccess: () => { reset(); success('Category saved.'); } });
}
function remove(id: number) { if (confirm('Delete category and all children?')) router.delete(sf('categories.destroy', { id }), { preserveState: true, onSuccess: () => success('Category deleted.') }); }

function renderTree(nodes: any[], depth = 0): any[] {
    return nodes.flatMap((n: any) => {
        const items = [{ ...n, depth }];
        if (n.children?.length) items.push(...renderTree(n.children, depth + 1));
        return items;
    });
}
const flat = ref(renderTree(props.categories));
</script>

<template>
    <Head title="Categories" />
    <StockFlowLayout title="Categories">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Product Categories</h1>
                <button @click="reset(); showForm = !showForm" class="px-4 py-2 bg-emerald-600 text-white rounded">{{ showForm ? 'Cancel' : 'Add Category' }}</button>
            </div>
            <div v-if="showForm" class="bg-white rounded-lg shadow p-6 mb-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div><label class="block text-sm font-medium">Name</label><input v-model="form.name" class="w-full border rounded px-3 py-2" /></div>
                    <div><label class="block text-sm font-medium">Parent Category</label>
                        <select v-model="form.parent_id" class="w-full border rounded px-3 py-2">
                            <option :value="''">None (Root)</option>
                            <option v-for="c in flatCategories" :key="c.id" :value="c.id" :disabled="c.id === editingId">{{ c.name }}</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium">Description</label><textarea v-model="form.description" class="w-full border rounded px-3 py-2"></textarea></div>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">{{ editingId ? 'Update' : 'Create' }}</button>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow">
                <div v-for="c in flat" :key="c.id" class="flex items-center px-4 py-3 border-t" :style="{ paddingLeft: (16 + c.depth * 24) + 'px' }">
                    <span class="flex-1">{{ c.name }}</span>
                    <span class="text-sm text-gray-500 mr-4">{{ c.description || '' }}</span>
                    <button @click="edit(c)" class="text-blue-600 hover:underline mr-2">Edit</button>
                    <button @click="remove(c.id)" class="text-red-600 hover:underline">Delete</button>
                </div>
                <div v-if="!flat.length" class="px-4 py-8 text-center text-gray-500">No categories.</div>
            </div>
        </div>
    </StockFlowLayout>
</template>
