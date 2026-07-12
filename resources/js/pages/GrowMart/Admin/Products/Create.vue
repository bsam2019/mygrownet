<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
}

interface Warehouse {
    id: number;
    name: string;
}

interface Props {
    categories: Category[];
    warehouses: Warehouse[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    unit: 'kg',
    price: '',
    compare_price: '',
    category_id: '',
    status: 'active',
    images: [] as string[],
    warehouse_id: '',
    initial_stock: 0,
});

const imagePreviews = ref<string[]>([]);

const handleImageUpload = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;

    for (const file of Array.from(input.files)) {
        if (file.size > 2 * 1024 * 1024) continue;
        const reader = new FileReader();
        reader.onload = (ev) => {
            const dataUrl = ev.target?.result as string;
            imagePreviews.value.push(dataUrl);
            form.images.push(dataUrl);
        };
        reader.readAsDataURL(file);
    }
    input.value = '';
};

const removeImage = (index: number) => {
    imagePreviews.value.splice(index, 1);
    form.images.splice(index, 1);
};

const submit = () => {
    form.post(route('admin.growmart.products.store'));
};

const units = ['kg', 'g', 'piece', 'bundle', 'litre', 'ml', 'packet', 'crate', 'dozen'];
</script>

<template>
    <Head title="Create Product - GrowMart Admin" />

    <AdminLayout title="Create Product">
        <div class="mb-6">
            <Link :href="route('admin.growmart.products.index')" class="text-sm text-emerald-600 hover:text-emerald-700">← Back to Products</Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-3xl">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select v-model="form.category_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Select category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
                        <select v-model="form.unit" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (K) *</label>
                        <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price (K)</label>
                        <input v-model="form.compare_price" type="number" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select v-model="form.status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="active">Active</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="discontinued">Discontinued</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    <div class="col-span-2 border-t border-gray-200 pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">Inventory</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                                <select v-model="form.warehouse_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Select warehouse</option>
                                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock</label>
                                <input v-model.number="form.initial_stock" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            </div>
                        </div>
                    </div>

                    <div class="col-span-2 border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Images</label>
                        <div class="flex gap-3 flex-wrap mb-3">
                            <div v-for="(preview, idx) in imagePreviews" :key="idx" class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200">
                                <img :src="preview" class="w-full h-full object-cover" />
                                <button @click="removeImage(idx)" type="button" class="absolute top-0 right-0 bg-red-500 text-white rounded-bl-lg p-0.5">
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                            <label class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-emerald-400">
                                <PhotoIcon class="h-6 w-6 text-gray-400" />
                                <input type="file" accept="image/*" multiple class="hidden" @change="handleImageUpload" />
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">Max 2MB per image. Upload up to 8 images.</p>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 font-medium">
                        {{ form.processing ? 'Creating...' : 'Create Product' }}
                    </button>
                    <Link :href="route('admin.growmart.products.index')" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
