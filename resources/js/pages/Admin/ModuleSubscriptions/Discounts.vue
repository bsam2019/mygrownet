<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { 
    ArrowLeftIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    TagIcon
} from '@heroicons/vue/24/outline';

interface Discount {
    id: number;
    module_id: string | null;
    name: string;
    description: string | null;
    discount_type: 'percentage' | 'fixed';
    discount_value: number;
    applies_to: string;
    tier_keys: string[] | null;
    code: string | null;
    starts_at: string | null;
    ends_at: string | null;
    max_uses: number | null;
    current_uses: number;
    min_purchase_amount: number | null;
    is_active: boolean;
    status: string;
    creator?: { name: string };
}

interface Module {
    id: string;
    name: string;
}

const props = defineProps<{
    discounts: { data: Discount[] };
    modules: Module[];
}>();

const showModal = ref(false);
const editingDiscount = ref<Discount | null>(null);

const form = useForm({
    module_id: null as string | null,
    name: '',
    description: '',
    discount_type: 'percentage' as 'percentage' | 'fixed',
    discount_value: 0,
    applies_to: 'all_tiers',
    tier_keys: [] as string[],
    code: '',
    starts_at: '',
    ends_at: '',
    max_uses: null as number | null,
    min_purchase_amount: null as number | null,
    is_active: true,
});

const openModal = (discount?: Discount) => {
    if (discount) {
        editingDiscount.value = discount;
        form.module_id = discount.module_id;
        form.name = discount.name;
        form.description = discount.description || '';
        form.discount_type = discount.discount_type;
        form.discount_value = discount.discount_value;
        form.applies_to = discount.applies_to;
        form.tier_keys = discount.tier_keys || [];
        form.code = discount.code || '';
        form.starts_at = discount.starts_at?.split('T')[0] || '';
        form.ends_at = discount.ends_at?.split('T')[0] || '';
        form.max_uses = discount.max_uses;
        form.min_purchase_amount = discount.min_purchase_amount;
        form.is_active = discount.is_active;
    } else {
        editingDiscount.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingDiscount.value = null;
    form.reset();
};

const save = () => {
    if (editingDiscount.value) {
        form.put(route('admin.module-subscriptions.discounts.update', editingDiscount.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.module-subscriptions.discounts.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteDiscount = (discount: Discount) => {
    if (confirm(`Delete discount "${discount.name}"?`)) {
        router.delete(route('admin.module-subscriptions.discounts.destroy', discount.id));
    }
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        scheduled: 'bg-blue-100 text-blue-800',
        expired: 'bg-gray-100 text-gray-800',
        exhausted: 'bg-amber-100 text-amber-800',
        inactive: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AdminLayout>
        <Head title="Discounts - Module Subscriptions" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.module-subscriptions.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Discounts</h1>
                        <p class="text-sm text-gray-500">Manage promotional discounts for module subscriptions</p>
                    </div>
                </div>
                <button @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Discount
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="discount in discounts.data" :key="discount.id">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ discount.name }}</div>
                                    <div class="text-sm text-gray-500">{{ discount.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-green-600">
                                        {{ discount.discount_type === 'percentage' ? `${discount.discount_value}%` : `K${discount.discount_value}` }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ discount.module_id || 'All Modules' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code v-if="discount.code" class="px-2 py-1 bg-gray-100 rounded text-sm">{{ discount.code }}</code>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ discount.current_uses }} / {{ discount.max_uses || '∞' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusColor(discount.status)" class="px-2 py-1 text-xs font-medium rounded-full capitalize">{{ discount.status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button @click="openModal(discount)" class="p-1 text-gray-400 hover:text-blue-600">
                                        <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                    <button @click="deleteDiscount(discount)" class="p-1 text-gray-400 hover:text-red-600">
                                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="discounts.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <TagIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                                    <p class="mt-2 text-gray-500">No discounts created yet</p>
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
                    <h3 class="text-lg font-semibold mb-4">{{ editingDiscount ? 'Edit Discount' : 'Add Discount' }}</h3>
                    <form @submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Module (optional)</label>
                            <select v-model="form.module_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option :value="null">All Modules</option>
                                <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Discount Type</label>
                                <select v-model="form.discount_type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount (K)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Value</label>
                                <input v-model.number="form.discount_value" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Applies To</label>
                            <select v-model="form.applies_to" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="all_tiers">All Tiers</option>
                                <option value="annual_only">Annual Plans Only</option>
                                <option value="monthly_only">Monthly Plans Only</option>
                                <option value="specific_tiers">Specific Tiers</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Promo Code (optional)</label>
                            <input v-model="form.code" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., SAVE20" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input v-model="form.starts_at" type="date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input v-model="form.ends_at" type="date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Max Uses</label>
                                <input v-model.number="form.max_uses" type="number" min="1" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Unlimited" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Min Purchase (K)</label>
                                <input v-model.number="form.min_purchase_amount" type="number" min="0" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="No minimum" />
                            </div>
                        </div>
                        <label class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ form.processing ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
