<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { TagIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Coupon {
    id: number;
    code: string;
    type: string;
    value: number;
    min_order_amount: number | null;
    max_discount: number | null;
    buy_quantity: number | null;
    get_quantity: number | null;
    usage_limit: number | null;
    used_count: number;
    starts_at: string | null;
    expires_at: string | null;
    is_active: boolean;
    description: string | null;
    created_at: string;
}

interface PaginatedData<T> {
    data: T[];
    meta: any;
}

const props = defineProps<{ coupons: PaginatedData<Coupon> }>();

function deleteCoupon(coupon: Coupon) {
    if (confirm(`Delete coupon "${coupon.code}"?`)) {
        router.delete(route('admin.growmart.coupons.destroy', coupon.id));
    }
}

function formatValue(coupon: Coupon): string {
    if (coupon.type === 'percentage') return `${coupon.value}%`;
    if (coupon.type === 'bogo') return `Buy ${coupon.buy_quantity ?? '?'} Get ${coupon.get_quantity ?? '?'} (${coupon.value}% off)`;
    return 'K' + (coupon.value / 100).toFixed(2);
}

function statusBadge(coupon: Coupon): { text: string; class: string } {
    if (!coupon.is_active) return { text: 'Inactive', class: 'bg-gray-100 text-gray-600' };
    if (coupon.expires_at && new Date(coupon.expires_at) < new Date()) return { text: 'Expired', class: 'bg-red-100 text-red-700' };
    if (coupon.usage_limit && coupon.used_count >= coupon.usage_limit) return { text: 'Exhausted', class: 'bg-orange-100 text-orange-700' };
    return { text: 'Active', class: 'bg-green-100 text-green-700' };
}
</script>

<template>
    <Head title="Coupons - GrowMart Admin" />

    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <TagIcon class="w-6 h-6 text-gray-500" />
                <h1 class="text-2xl font-bold text-gray-900">Coupons</h1>
            </div>
            <Link :href="route('admin.growmart.coupons.create')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <PlusIcon class="w-5 h-5" />
                New Coupon
            </Link>
        </div>

        <div v-if="coupons.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
            <TagIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-500">No coupons yet.</p>
        </div>

        <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Usage</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Expires</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="coupon in coupons.data" :key="coupon.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono font-medium text-gray-900">{{ coupon.code }}</span>
                            <p v-if="coupon.description" class="text-xs text-gray-500 mt-0.5">{{ coupon.description }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ formatValue(coupon) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ coupon.used_count }}{{ coupon.usage_limit ? ' / ' + coupon.usage_limit : '' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString() : 'Never' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="statusBadge(coupon).class">
                                {{ statusBadge(coupon).text }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('admin.growmart.coupons.edit', coupon.id)"
                                    class="p-1.5 text-gray-400 hover:text-emerald-600 transition-colors">
                                    <PencilIcon class="w-4 h-4" />
                                </Link>
                                <button @click="deleteCoupon(coupon)"
                                    class="p-1.5 text-gray-400 hover:text-red-500 transition-colors">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
