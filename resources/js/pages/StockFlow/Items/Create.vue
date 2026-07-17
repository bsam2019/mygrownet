<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import BarcodeScanner from '@/components/StockFlow/BarcodeScanner.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { useNotifications } from '@/composables/useNotifications';
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const { route } = useStockflowRoute();

interface Bin {
    id: number;
    name: string;
    label: string | null;
}

interface Props {
    bins: Bin[];
}

defineProps<Props>();

const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);

const form = ref({
    name: '',
    sku: '',
    description: '',
    unit_price: 0,
    unit: 'pcs',
    system_quantity: 0,
    sa_bin_id: '',
    category: '',
    is_expirable: false,
    expiry_date: '',
    notes: '',
});

const { success } = useNotifications();

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const onBarcodeScanned = (value: string) => {
    form.value.sku = value;
    success('Barcode scanned: ' + value);
};

const onImageSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    imageFile.value = input.files[0];
    const reader = new FileReader();
    reader.onload = (e) => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(input.files[0]);
};

const submit = () => {
    processing.value = true;
    const payload = new FormData();
    Object.entries(form.value).forEach(([key, value]) => {
        payload.append(key, String(value));
    });
    if (imageFile.value) {
        payload.append('image', imageFile.value);
    }
    router.post(route('stockflow.sub.items.store'), payload, {
        onSuccess: () => { processing.value = false; },
        onError: (err) => { errors.value = err; processing.value = false; },
    });
};
</script>

<template>
    <StockFlowLayout title="Create Item">
        <Head title="Create Item - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.items.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Items</Link>
                </div>

                <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                            <PlusIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-white">Create Item</h1>
                            <p class="text-sm text-emerald-100">Add a new product to your inventory</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold text-gray-900">Item Details</h2>
                        </div>
                        <div class="px-6 py-5">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name *</label>
                                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SKU</label>
                                    <div class="mt-1 flex gap-2">
                                        <input v-model="form.sku" type="text" class="block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                        <BarcodeScanner @scanned="onBarcodeScanned" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <input v-model="form.category" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unit</label>
                                    <select v-model="form.unit" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                        <option value="pcs">Pieces (pcs)</option>
                                        <option value="kg">Kilograms (kg)</option>
                                        <option value="g">Grams (g)</option>
                                        <option value="l">Liters (l)</option>
                                        <option value="ml">Milliliters (ml)</option>
                                        <option value="box">Box</option>
                                        <option value="pack">Pack</option>
                                        <option value="bottle">Bottle</option>
                                        <option value="unit">Unit</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unit Price *</label>
                                    <input v-model.number="form.unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    <p v-if="errors.unit_price" class="mt-1 text-sm text-red-600">{{ errors.unit_price }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">System Quantity *</label>
                                    <input v-model.number="form.system_quantity" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    <p v-if="errors.system_quantity" class="mt-1 text-sm text-red-600">{{ errors.system_quantity }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bin</label>
                                    <select v-model="form.sa_bin_id" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                        <option value="">No bin</option>
                                        <option v-for="bin in bins" :key="bin.id" :value="bin.id">{{ bin.label || bin.name }}</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.is_expirable" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                        <span class="text-sm font-medium text-gray-700">Expirable item</span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="form.is_expirable" class="mt-6">
                                <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input v-model="form.expiry_date" type="date" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600"></textarea>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700">Image</label>
                                <input type="file" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" @change="onImageSelect" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100" />
                                <div v-if="imagePreview" class="mt-2">
                                    <img :src="imagePreview" class="h-24 w-24 rounded-lg object-cover border border-gray-200" />
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
                            <Link :href="route('stockflow.sub.items.index')" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Cancel</Link>
                            <button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ processing ? 'Creating...' : 'Create Item' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
