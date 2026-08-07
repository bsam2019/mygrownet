<template>
    <CreatorStudioLayout title="Payouts &amp; Earnings - GrowStream">
        <main class="flex-1 px-margin-mobile md:px-margin-desktop max-w-4xl mx-auto mt-4 md:mt-8 space-y-8 pb-24">
            <!-- Summary Header -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                <!-- Total Balance -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm hover:border-outline transition-colors">
                    <h3 class="font-label-md text-label-md text-on-surface-variant mb-1">Total Balance</h3>
                    <p class="font-display-lg text-display-lg text-primary">K {{ formatMoney(summary.total_balance) }}</p>
                    <p v-if="summary.pending > 0" class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ formatMoney(summary.pending) }} pending</p>
                    <button class="mt-4 bg-primary text-on-primary font-label-md text-label-md px-6 py-2 rounded-full hover:bg-surface-tint w-full transition-colors active:scale-95">
                        Request Payout
                    </button>
                </div>
                <!-- Last Payout -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm flex flex-col justify-center">
                    <h3 class="font-label-md text-label-md text-on-surface-variant mb-1">Last Payout</h3>
                    <p class="font-headline-lg text-headline-lg text-on-surface">K {{ formatMoney(summary.last_payout_amount) }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs text-primary" aria-hidden="true">check_circle</span>
                        {{ formatDate(summary.last_payout_date) || 'No payouts yet' }}
                    </p>
                </div>
                <!-- Next Eligibility -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm flex flex-col justify-center">
                    <h3 class="font-label-md text-label-md text-on-surface-variant mb-1">Next Eligibility</h3>
                    <p class="font-headline-lg text-headline-lg text-on-surface">K {{ formatMoney(summary.threshold) }} Threshold</p>
                    <div class="w-full bg-surface-variant rounded-full h-2 mt-4">
                        <div class="bg-primary h-2 rounded-full transition-all" :style="{ width: thresholdPct + '%' }"></div>
                    </div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-1 text-right">{{ thresholdPct >= 100 ? 'Target Met' : thresholdPct.toFixed(0) + '%' }}</p>
                </div>
            </section>

            <!-- Payout History -->
            <section v-if="payouts.length > 0">
                <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Payout History</h3>
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
                    <div class="flex flex-col">
                        <div
                            v-for="p in payouts"
                            :key="p.id"
                            class="flex items-center justify-between px-5 py-4 border-b border-outline-variant last:border-b-0"
                        >
                            <div>
                                <p class="font-label-md text-label-md text-on-surface">K {{ formatMoney(p.amount) }}</p>
                                <p class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">{{ formatDate(p.paid_at || p.created_at) }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full font-label-sm text-label-sm capitalize" :class="p.status === 'paid' || p.status === 'completed' ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant'">
                                {{ p.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Empty state -->
            <section v-else class="flex flex-col items-center gap-4 py-16 text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                    <span class="material-symbols-outlined text-4xl text-primary" aria-hidden="true">account_balance_wallet</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface">No payouts yet</h3>
                <p class="font-label-sm text-label-sm text-on-surface-variant max-w-sm">Earnings will appear here once you reach the payout threshold.</p>
            </section>
        </main>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';

interface PayoutRow { id: number; amount: number; status: string; paid_at?: string | null; created_at?: string; }
interface Props {
    summary?: { total_balance: number; total_paid: number; last_payout_amount: number; last_payout_date?: string | null; threshold: number; pending: number };
    payouts?: PayoutRow[];
}

const props = withDefaults(defineProps<Props>(), {
    summary: () => ({ total_balance: 0, total_paid: 0, last_payout_amount: 0, last_payout_date: null, threshold: 100, pending: 0 }),
    payouts: () => [],
});

const thresholdPct = computed(() => {
    if (props.summary.threshold <= 0) return 100;
    return Math.min((props.summary.total_balance / props.summary.threshold) * 100, 100);
});

const formatMoney = (n: number): string => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });

const formatDate = (date?: string | null): string => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>
