<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Tier {
    id: number;
    name: string;
    price: number;
    currency: string;
    formatted_price: string;
    documents_per_month: number;
    features: Record<string, any>;
    is_active: boolean;
    subscriber_count: number;
    created_at: string;
}

const props = defineProps<{ tiers: Tier[] }>();

const deleting = ref<number | null>(null);

const deleteTier = async (tier: Tier) => {
    if (!confirm(`Delete "${tier.name}"? This cannot be undone if there are no active subscribers.`)) return;
    deleting.value = tier.id;
    try {
        await router.delete(route('admin.quick-invoice.tiers.destroy', tier.id));
    } finally { deleting.value = null; }
};

const documentLimitLabel = (n: number) => n === -1 ? 'Unlimited' : `${n}/month`;
</script>

<template>
    <AdminLayout title="Subscription Plans">
        <Head title="Subscription Plans - Admin" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
                        <p class="text-gray-600">Manage Quick Invoice subscription tiers</p>
                    </div>
                    <Link :href="route('admin.quick-invoice.tiers.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        <PlusIcon class="h-4 w-4" />
                        Add Plan
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div v-for="tier in tiers" :key="tier.id"
                        class="bg-white rounded-xl border shadow-sm overflow-hidden transition hover:shadow-md"
                        :class="tier.is_active ? 'border-gray-200' : 'border-gray-200 opacity-60'">
                        <!-- Header -->
                        <div class="p-5 border-b border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ tier.name }}</h3>
                                <span v-if="!tier.is_active" class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-extrabold text-gray-900">{{ tier.formatted_price }}</span>
                                <span v-if="tier.price > 0" class="text-sm text-gray-500">/month</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ documentLimitLabel(tier.documents_per_month) }}</p>
                        </div>

                        <!-- Features -->
                        <div class="p-5 space-y-2">
                            <div v-for="(value, key) in tier.features" :key="key" class="flex items-center gap-2 text-sm">
                                <span class="w-2 h-2 rounded-full" :class="value ? 'bg-green-400' : 'bg-gray-300'"></span>
                                <span :class="value ? 'text-gray-700' : 'text-gray-400'">{{ key }}</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ tier.subscriber_count }} subscriber(s)</span>
                            <div class="flex items-center gap-2">
                                <Link :href="route('admin.quick-invoice.tiers.edit', tier.id)"
                                    class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                                    <PencilIcon class="h-4 w-4" />
                                </Link>
                                <button @click="deleteTier(tier)" :disabled="deleting === tier.id"
                                    class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition disabled:opacity-50">
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
