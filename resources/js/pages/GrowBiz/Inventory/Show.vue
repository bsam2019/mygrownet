<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    CubeIcon,
    PencilIcon,
    TrashIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ClockIcon,
    MapPinIcon,
    TagIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

interface InventoryItem {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    category_id: number | null;
    unit: string;
    cost_price: number;
    selling_price: number;
    current_stock: number;
    low_stock_threshold: number;
    location: string | null;
    is_low_stock: boolean;
    is_out_of_stock: boolean;
    stock_value: number;
    potential_revenue: number;
    profit_margin: number;
    created_at: string;
}

interface Movement {
    id: number;
    type: string;
    quantity: number;
    stock_before: number;
    stock_after: number;
    unit_cost: number | null;
    notes: string | null;
    user_name: string | null;
    created_at: string;
}

interface Props {
    item: InventoryItem;
    movements: Movement[];
    categories: { id: number; name: string }[];
    movementTypes: { value: string; label: string; direction: string }[];
}

const props = defineProps<Props>();

defineOptions({ layout: GrowBizLayout });

const showAdjustModal = ref(false);
const adjustmentForm = ref({
    type: 'purchase',
    quantity: 1,
    unit_cost: props.item.cost_price,
    notes: '',
});

const adjustStock = async () => {
    try {
        const response = await fetch(route('growbiz.inventory.adjust', props.item.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(adjustmentForm.value),
        });
        
        if (response.ok) {
            showAdjustModal.value = false;
            router.reload();
        }
    } catch (e) {
        console.error('Failed to adjust stock', e);
    }
};

const deleteItem = () => {
    if (!confirm(`Delete "${props.item.name}"? This cannot be undone.`)) return;
    router.delete(route('growbiz.inventory.destroy', props.item.id), {
        onSuccess: () => router.visit(route('growbiz.inventory.index')),
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getMovementTypeLabel = (type: string) => {
    const found = props.movementTypes.find(t => t.value === type);
    return found?.label || type;
};

const getMovementColor = (type: string) => {
    const incoming = ['purchase', 'return', 'initial', 'adjustment'];
    return incoming.includes(type) ? 'text-emerald-600' : 'text-red-600';
};

const getCategoryName = (categoryId: number | null) => {
    if (!categoryId) return 'Uncategorized';
    const cat = props.categories.find(c => c.id === categoryId);
    return cat?.name || 'Uncategorized';
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link :href="route('growbiz.inventory.index')" class="p-2 -ml-2 rounded-xl hover:bg-gray-100">
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-900">{{ item.name }}</h1>
                <p v-if="item.sku" class="text-sm text-gray-500">SKU: {{ item.sku }}</p>
            </div>
            <button @click="deleteItem" class="p-2 text-gray-400 hover:text-red-600 rounded-xl hover:bg-red-50">
                <TrashIcon class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>

        <!-- Stock Status Card -->
        <div :class="[
            'rounded-xl p-4',
            item.is_out_of_stock ? 'bg-red-50' : item.is_low_stock ? 'bg-amber-50' : 'bg-emerald-50'
        ]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" :class="item.is_out_of_stock ? 'text-red-600' : item.is_low_stock ? 'text-amber-600' : 'text-emerald-600'">
                        {{ item.is_out_of_stock ? 'Out of Stock' : item.is_low_stock ? 'Low Stock' : 'In Stock' }}
                    </p>
                    <p class="text-3xl font-bold text-gray-900">{{ item.current_stock }} <span class="text-lg font-normal text-gray-500">{{ item.unit }}s</span></p>
                </div>
                <button
                    @click="showAdjustModal = true"
                    class="px-4 py-2 bg-white text-gray-700 rounded-xl font-medium shadow-sm hover:shadow-md transition-shadow"
                >
                    Adjust Stock
                </button>
            </div>
            <p class="text-sm text-gray-500 mt-2">Alert threshold: {{ item.low_stock_threshold }} {{ item.unit }}s</p>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-4">
                <div class="flex items-center gap-2 text-gray-500 mb-1">
                    <CurrencyDollarIcon class="h-4 w-4" aria-hidden="true" />
                    <span class="text-xs font-medium uppercase">Cost Price</span>
                </div>
                <p class="text-lg font-bold text-gray-900">{{ formatCurrency(item.cost_price) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4">
                <div class="flex items-center gap-2 text-gray-500 mb-1">
                    <TagIcon class="h-4 w-4" aria-hidden="true" />
                    <span class="text-xs font-medium uppercase">Selling Price</span>
                </div>
                <p class="text-lg font-bold text-gray-900">{{ formatCurrency(item.selling_price) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4">
                <div class="flex items-center gap-2 text-gray-500 mb-1">
                    <ChartBarIcon class="h-4 w-4" aria-hidden="true" />
                    <span class="text-xs font-medium uppercase">Stock Value</span>
                </div>
                <p class="text-lg font-bold text-gray-900">{{ formatCurrency(item.stock_value) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4">
                <div class="flex items-center gap-2 text-gray-500 mb-1">
                    <ChartBarIcon class="h-4 w-4" aria-hidden="true" />
                    <span class="text-xs font-medium uppercase">Profit Margin</span>
                </div>
                <p class="text-lg font-bold" :class="item.profit_margin > 0 ? 'text-emerald-600' : 'text-red-600'">{{ item.profit_margin }}%</p>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="bg-white rounded-xl p-4 space-y-3">
            <div v-if="item.description">
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Description</p>
                <p class="text-gray-700">{{ item.description }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div v-if="item.location" class="flex items-center gap-2 text-gray-600">
                    <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                    <span class="text-sm">{{ item.location }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <CubeIcon class="h-4 w-4" aria-hidden="true" />
                    <span class="text-sm">{{ getCategoryName(item.category_id) }}</span>
                </div>
            </div>
        </div>

        <!-- Stock History -->
        <div class="bg-white rounded-xl p-4">
            <h3 class="font-semibold text-gray-900 mb-3">Stock History</h3>
            
            <div v-if="movements.length === 0" class="text-center py-6 text-gray-500">
                No stock movements yet
            </div>

            <div v-else class="space-y-3">
                <div v-for="movement in movements" :key="movement.id" class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0">
                    <div :class="[
                        'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                        movement.quantity > 0 ? 'bg-emerald-100' : 'bg-red-100'
                    ]">
                        <ArrowUpIcon v-if="movement.quantity > 0" class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                        <ArrowDownIcon v-else class="h-4 w-4 text-red-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-900">{{ getMovementTypeLabel(movement.type) }}</p>
                            <p :class="['font-bold', getMovementColor(movement.type)]">
                                {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                            </p>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ movement.stock_before }} → {{ movement.stock_after }}
                        </p>
                        <p v-if="movement.notes" class="text-sm text-gray-600 mt-1">{{ movement.notes }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ formatDate(movement.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150">
            <div v-if="showAdjustModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="showAdjustModal = false" />
        </Transition>

        <Transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="translate-y-full" leave-active-class="transition-transform duration-200 ease-in">
            <div v-if="showAdjustModal" class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl">
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>

                <div class="px-6 pb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Adjust Stock</h2>
                    <p class="text-sm text-gray-500 mb-4">Current stock: {{ item.current_stock }} {{ item.unit }}s</p>

                    <form @submit.prevent="adjustStock" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select v-model="adjustmentForm.type" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                <option v-for="t in movementTypes.filter(m => m.value !== 'initial')" :key="t.value" :value="t.value">
                                    {{ t.label }} ({{ t.direction === 'in' ? '+' : t.direction === 'out' ? '-' : '±' }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input v-model.number="adjustmentForm.quantity" type="number" min="1" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="adjustmentForm.notes" rows="2" placeholder="Optional notes..." class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showAdjustModal = false" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancel</button>
                            <button type="submit" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-medium">Adjust Stock</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
