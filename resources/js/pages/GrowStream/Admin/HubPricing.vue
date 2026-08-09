<template>
    <AdminLayout title="Creator Hub Pricing Management - GrowStream Admin">
        <div class="mx-auto max-w-6xl py-6">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--gs-text)]">Creator Hub Pricing Tiers</h1>
                    <p class="mt-1 text-sm text-[var(--gs-muted)]">
                        Manage subscription tiers, prices, Cloudflare storage allocations, and watch-minute limits for B2B Creator Hub platforms.
                    </p>
                </div>
            </div>

            <!-- Pricing Form -->
            <form @submit.prevent="savePricing" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="(tier, index) in form.pricingTiers"
                        :key="index"
                        class="gs-card p-6 border border-[var(--gs-border)] relative space-y-4"
                    >
                        <div class="flex items-center justify-between border-b border-[var(--gs-border)] pb-3">
                            <span class="font-bold text-lg text-[var(--gs-primary)]">Tier #{{ index + 1 }}</span>
                            <label class="flex items-center gap-2 text-xs text-[var(--gs-muted)] cursor-pointer">
                                <input type="checkbox" v-model="tier.is_popular" class="rounded text-primary" />
                                Highlight Popular
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Tier Name</label>
                            <input
                                v-model="tier.name"
                                type="text"
                                class="gs-input w-full"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Price (e.g. K195)</label>
                                <input
                                    v-model="tier.price"
                                    type="text"
                                    class="gs-input w-full font-mono font-bold"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Billing Period</label>
                                <input
                                    v-model="tier.period"
                                    type="text"
                                    class="gs-input w-full"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Description</label>
                            <textarea
                                v-model="tier.description"
                                rows="2"
                                class="gs-input w-full text-xs"
                                required
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Video Storage Quota</label>
                            <input
                                v-model="tier.storage"
                                type="text"
                                class="gs-input w-full font-mono text-xs"
                                placeholder="e.g. 500 Mins Video Storage"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Streaming Delivery Quota</label>
                            <input
                                v-model="tier.bandwidth"
                                type="text"
                                class="gs-input w-full font-mono text-xs"
                                placeholder="e.g. 3,000 Streaming Watch Mins / mo"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Domain Type Description</label>
                            <input
                                v-model="tier.domain"
                                type="text"
                                class="gs-input w-full text-xs"
                                required
                            />
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input
                                type="checkbox"
                                :id="`byop-${index}`"
                                v-model="tier.byop"
                                class="rounded text-primary"
                            />
                            <label :for="`byop-${index}`" class="text-xs text-[var(--gs-text)] cursor-pointer">
                                Enable BYOP Gateway Keys (Paystack/Flutterwave)
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--gs-muted)] mb-1">Button CTA Text</label>
                            <input
                                v-model="tier.cta"
                                type="text"
                                class="gs-input w-full text-xs"
                                required
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="gs-btn gs-btn-primary px-8 py-3 font-semibold text-base"
                    >
                        Save Pricing &amp; Tier Changes
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Tier {
    name: string;
    price: string;
    period: string;
    description: string;
    storage: string;
    bandwidth: string;
    domain: string;
    byop: boolean;
    is_popular?: boolean;
    cta: string;
}

interface Props {
    pricingTiers: Tier[];
}

const props = defineProps<Props>();

const form = useForm({
    pricingTiers: JSON.parse(JSON.stringify(props.pricingTiers)),
});

const savePricing = () => {
    form.post(route('growstream.admin.hub_pricing.update'), {
        preserveScroll: true,
    });
};
</script>
