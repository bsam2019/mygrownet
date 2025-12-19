<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { ArrowLeftIcon, PlusIcon, PencilIcon, TrashIcon, GiftIcon, StarIcon } from '@heroicons/vue/24/outline';

interface Offer {
    id: number;
    name: string;
    description: string | null;
    offer_type: string;
    module_ids: string[];
    tier_key: string | null;
    original_price: number;
    offer_price: number;
    savings_display: string | null;
    billing_cycle: string;
    starts_at: string;
    ends_at: string;
    max_purchases: number | null;
    current_purchases: number;
    is_featured: boolean;
    is_active: boolean;
    status: string;
}

interface Module { id: string; name: string; }

const props = defineProps<{
    offers: { data: Offer[] };
    modules: Module[];
}>();

const showModal = ref(false);
const editingOffer = ref<Offer | null>(null);

const form = useForm({
    name: '',
    description: '',
    offer_type: 'bundle',
    module_ids: [] as string[],
    tier_key: '',
    original_price: 0,
    offer_price: 0,
    savings_display: '',
    billing_cycle: 'annual',
    starts_at: '',
    ends_at: '',
    max_purchases: null as number | null,
    is_featured: false,
    is_active: true,
});

const formatCurrency = (amount: number) => new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount);

const openModal = (offer?: Offer) => {
    if (offer) {
        editingOffer.value = offer;
        form.name = offer.name;
        form.description = offer.description || '';
        form.offer_type = offer.offer_type;
        form.module_ids = offer.module_ids;
        form.tier_key = offer.tier_key || '';
        form.original_price = offer.original_price;
        form.offer_price = offer.offer_price;
        form.savings_display = offer.savings_display || '';
        form.billing_cycle = offer.billing_cycle;
        form.starts_at = offer.starts_at?.split('T')[0] || '';
        form.ends_at = offer.ends_at?.split('T')[0] || '';
        form.max_purchases = offer.max_purchases;
        form.is_featured = offer.is_featured;
        form.is_active = offer.is_active;
    } else {
        editingOffer.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; editingOffer.value = null; form.reset(); };

const save = () => {
    if (editingOffer.value) {
        form.put(route('admin.module-subscriptions.offers.update', editingOffer.value.id), { onSuccess: () => closeModal() });
    } else {
        form.post(route('admin.module-subscriptions.offers.store'), { onSuccess: () => closeModal() });
    }
};

const deleteOffer = (offer: Offer) => {
    if (confirm(`Delete offer "${offer.name}"?`)) {
        router.delete(route('admin.module-subscriptions.offers.destroy', offer.id));
    }
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = { active: 'bg-green-100 text-green-800', scheduled: 'bg-blue-100 text-blue-800', expired: 'bg-gray-100 text-gray-800', sold_out: 'bg-amber-100 text-amber-800', inactive: 'bg-red-100 text-red-800' };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AdminLayout>
        <Head title="Special Offers - Module Subscriptions" />
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.module-subscriptions.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Special Offers</h1>
                        <p class="text-sm text-gray-500">Create time-limited offers and bundles</p>
                    </div>
                </div>
                <button @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    <PlusIcon class="h-5 w-5" aria-hidden="true" /> Add Offer
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Offer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pricing</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="offer in offers.data" :key="offer.id">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <StarIcon v-if="offer.is_featured" class="h-5 w-5 text-amber-500" aria-hidden="true" />
                                        <div>
                                            <div class="font-medium text-gray-900">{{ offer.name }}</div>
                                            <div class="text-sm text-gray-500">{{ offer.description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-400 line-through">{{ formatCurrency(offer.original_price) }}</div>
                                    <div class="font-semibold text-green-600">{{ formatCurrency(offer.offer_price) }}</div>
                                    <div v-if="offer.savings_display" class="text-xs text-green-500">{{ offer.savings_display }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ offer.offer_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ offer.starts_at?.split('T')[0] }}</div>
                                    <div>to {{ offer.ends_at?.split('T')[0] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ offer.current_purchases }} / {{ offer.max_purchases || '∞' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusColor(offer.status)" class="px-2 py-1 text-xs font-medium rounded-full capitalize">{{ offer.status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button @click="openModal(offer)" class="p-1 text-gray-400 hover:text-blue-600"><PencilIcon class="h-5 w-5" aria-hidden="true" /></button>
                                    <button @click="deleteOffer(offer)" class="p-1 text-gray-400 hover:text-red-600"><TrashIcon class="h-5 w-5" aria-hidden="true" /></button>
                                </td>
                            </tr>
                            <tr v-if="offers.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <GiftIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                                    <p class="mt-2 text-gray-500">No special offers created yet</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold mb-4">{{ editingOffer ? 'Edit Offer' : 'Add Offer' }}</h3>
                    <form @submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Offer Type</label>
                                <select v-model="form.offer_type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="bundle">Bundle</option>
                                    <option value="upgrade">Upgrade</option>
                                    <option value="trial_extension">Trial Extension</option>
                                    <option value="bonus_feature">Bonus Feature</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Billing Cycle</label>
                                <select v-model="form.billing_cycle" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="monthly">Monthly</option>
                                    <option value="annual">Annual</option>
                                    <option value="one_time">One-time</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Modules</label>
                            <div class="mt-2 space-y-2 max-h-32 overflow-y-auto">
                                <label v-for="m in modules" :key="m.id" class="flex items-center gap-2">
                                    <input v-model="form.module_ids" :value="m.id" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                    <span class="text-sm text-gray-700">{{ m.name }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Original Price (K)</label>
                                <input v-model.number="form.original_price" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Offer Price (K)</label>
                                <input v-model.number="form.offer_price" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Savings Display</label>
                            <input v-model="form.savings_display" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., Save K200 or 50% off" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input v-model="form.starts_at" type="date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input v-model="form.ends_at" type="date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Purchases</label>
                            <input v-model.number="form.max_purchases" type="number" min="1" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Unlimited" />
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Featured</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">{{ form.processing ? 'Saving...' : 'Save' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
