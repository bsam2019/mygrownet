<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { growmartImage } from '@/lib/growmart';

interface Category {
    id: number;
    name: string;
}

interface Warehouse {
    id: number;
    name: string;
}

interface ProductImage {
    id: number;
    path: string;
    sort_order: number;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    unit: string;
    price: number;
    compare_price: number | null;
    category_id: number;
    status: string;
    images: ProductImage[];
    inventory: { warehouse_id: number; quantity: number }[];
}

interface Props {
    product: Product;
    categories: Category[];
    warehouses: Warehouse[];
}

const props = defineProps<Props>();

const existingImages = ref<ProductImage[]>(props.product.images || []);
const newImagePreviews = ref<string[]>([]);
const newImages = ref<string[]>([]);

const form = useForm({
    name: props.product.name,
    slug: props.product.slug,
    description: props.product.description || '',
    unit: props.product.unit,
    price: (props.product.price / 100).toFixed(2),
    compare_price: props.product.compare_price ? (props.product.compare_price / 100).toFixed(2) : '',
    category_id: String(props.product.category_id),
    status: props.product.status,
    images: [] as string[],
});

const handleImageUpload = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    for (const file of Array.from(input.files)) {
        if (file.size > 2 * 1024 * 1024) continue;
        const reader = new FileReader();
        reader.onload = (ev) => {
            const dataUrl = ev.target?.result as string;
            newImagePreviews.value.push(dataUrl);
            newImages.value.push(dataUrl);
            form.images.push(dataUrl);
        };
        reader.readAsDataURL(file);
    }
    input.value = '';
};

const removeNewImage = (index: number) => {
    newImagePreviews.value.splice(index, 1);
    newImages.value.splice(index, 1);
};

const submit = () => {
    form.put(route('admin.growmart.products.update', props.product.id));
};

const units = ['kg', 'g', 'piece', 'bundle', 'litre', 'ml', 'packet', 'crate', 'dozen'];
</script>

<template>
    <Head :title="'Edit ' + product.name + ' - GrowMart Admin'" />

    <AdminLayout :title="'Edit: ' + product.name">
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
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Images</label>
                        <div class="flex gap-3 flex-wrap mb-3">
                            <div v-for="img in existingImages" :key="img.id" class="w-20 h-20 rounded-lg overflow-hidden border border-gray-200">
                                <img :src="growmartImage(img.path)" class="w-full h-full object-cover" />
                            </div>
                            <div v-for="(preview, idx) in newImagePreviews" :key="'new-' + idx" class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200">
                                <img :src="preview" class="w-full h-full object-cover" />
                                <button @click="removeNewImage(idx)" type="button" class="absolute top-0 right-0 bg-red-500 text-white rounded-bl-lg p-0.5">
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                            <label class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-emerald-400">
                                <PhotoIcon class="h-6 w-6 text-gray-400" />
                                <input type="file" accept="image/*" multiple class="hidden" @change="handleImageUpload" />
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 font-medium">
                        {{ form.processing ? 'Updating...' : 'Update Product' }}
                    </button>
                    <Link :href="route('admin.growmart.products.index')" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
