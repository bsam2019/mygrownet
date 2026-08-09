<template>
    <AdminLayout title="Creator Hub Management - GrowStream Admin">
        <div class="mx-auto max-w-7xl py-6">
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--gs-text)]">Creator Hub Tenant Management</h1>
                    <p class="mt-1 text-sm text-[var(--gs-muted)]">
                        Manage B2B independent video academies, custom subdomains, subscription statuses, and streaming quotas.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('growstream.admin.hub_pricing.show')"
                        class="px-4 py-2 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-[#c94918] transition-colors flex items-center gap-1.5 shadow-lg shadow-primary/20"
                    >
                        <span class="material-symbols-outlined text-sm">payments</span> Manage Hub Subscription Tiers
                    </Link>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <p class="text-xs text-on-surface-variant font-label-md">Total Platforms</p>
                    <p class="text-2xl font-black text-on-surface mt-1">{{ hubs.length }}</p>
                </div>
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <p class="text-xs text-on-surface-variant font-label-md">Active Subscriptions</p>
                    <p class="text-2xl font-black text-emerald-400 mt-1">{{ activeCount }}</p>
                </div>
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <p class="text-xs text-on-surface-variant font-label-md">Total Storage Mins Used</p>
                    <p class="text-2xl font-black text-primary mt-1">{{ totalStorageUsed.toLocaleString() }} min</p>
                </div>
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <p class="text-xs text-on-surface-variant font-label-md">Total Watch Mins Delivered</p>
                    <p class="text-2xl font-black text-amber-400 mt-1">{{ totalWatchMinsDelivered.toLocaleString() }} min</p>
                </div>
            </div>

            <!-- Hub Table -->
            <div class="bg-surface-container rounded-2xl border border-outline-variant/60 overflow-hidden shadow-xl">
                <div class="px-6 py-4 border-b border-outline-variant/40 flex items-center justify-between">
                    <h2 class="font-bold text-lg text-on-surface">Registered Creator Platforms</h2>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by brand, subdomain, org ID..."
                        class="px-4 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/60 text-xs text-on-surface w-64"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-surface-container-high/60 text-on-surface-variant font-label-sm uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">Brand &amp; Org</th>
                                <th class="px-6 py-3.5">Subdomain &amp; Domain</th>
                                <th class="px-6 py-3.5">Plan &amp; Status</th>
                                <th class="px-6 py-3.5">Storage Quota</th>
                                <th class="px-6 py-3.5">Watch Delivery Quota</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40 text-on-surface">
                            <tr v-for="hub in filteredHubs" :key="hub.id" class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-sm text-on-surface">{{ hub.brand_name || 'Unnamed Platform' }}</p>
                                    <p class="text-[11px] text-on-surface-variant font-mono">Org #{{ hub.organization_id }} · {{ hub.category || 'education' }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-mono text-xs text-primary font-semibold">{{ hub.subdomain ? `${hub.subdomain}.growstream.app` : '—' }}</p>
                                    <p v-if="hub.custom_domain" class="font-mono text-[11px] text-on-surface-variant">{{ hub.custom_domain }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="capitalize font-bold text-xs uppercase tracking-wider text-amber-400 block mb-1">
                                        {{ hub.subscription_plan || 'starter' }}
                                    </span>
                                    <span :class="[
                                        'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider inline-block',
                                        hub.is_active && hub.subscription_status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'
                                    ]">
                                        {{ hub.is_active && hub.subscription_status === 'active' ? 'Active' : 'Pending Payment' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 font-mono text-xs">
                                    <span>{{ hub.quota?.current_storage_minutes || 0 }} / {{ hub.quota?.storage_minutes_limit || 500 }} min</span>
                                </td>

                                <td class="px-6 py-4 font-mono text-xs">
                                    <span>{{ (hub.quota?.current_delivery_gb || 0) * 50 }} / {{ (hub.quota?.delivery_gb_limit || 100) * 50 }} min</span>
                                </td>

                                <td class="px-6 py-4 text-right space-x-2">
                                    <button
                                        @click="toggleStatus(hub.id)"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                                            hub.is_active ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30'
                                        ]"
                                    >
                                        {{ hub.is_active ? 'Suspend' : 'Activate' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredHubs.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant text-xs">
                                    No Creator Hub platforms found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Hub {
    id: number;
    organization_id: number;
    brand_name: string;
    subdomain: string;
    custom_domain: string | null;
    category: string;
    subscription_plan: string;
    subscription_status: string;
    is_active: boolean;
    quota?: {
        current_storage_minutes: number;
        storage_minutes_limit: number;
        current_delivery_gb: number;
        delivery_gb_limit: number;
    };
}

interface Props {
    hubs: Hub[];
}

const props = defineProps<Props>();

const search = ref('');

const filteredHubs = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) return props.hubs;
    return props.hubs.filter((h) =>
        (h.brand_name || '').toLowerCase().includes(q) ||
        (h.subdomain || '').toLowerCase().includes(q) ||
        String(h.organization_id).includes(q)
    );
});

const activeCount = computed(() => props.hubs.filter((h) => h.is_active).length);
const totalStorageUsed = computed(() => props.hubs.reduce((acc, h) => acc + (h.quota?.current_storage_minutes || 0), 0));
const totalWatchMinsDelivered = computed(() => props.hubs.reduce((acc, h) => acc + ((h.quota?.current_delivery_gb || 0) * 50), 0));

const toggleStatus = (id: number) => {
    router.post(route('growstream.admin.hubs.toggle_status', { id }), {}, { preserveScroll: true });
};
</script>
