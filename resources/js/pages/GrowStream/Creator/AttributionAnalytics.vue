<template>
    <GrowStreamLayout title="Social Attribution Analytics - Creator Studio">
        <div class="max-w-5xl mx-auto py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-headline-lg text-headline-lg font-bold text-on-surface">Traffic &amp; Conversion Analytics</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                        Track subscriber acquisitions and watch minutes generated across your social media channel links.
                    </p>
                </div>
            </div>

            <!-- Shareable URL Box -->
            <div class="bg-surface-container rounded-2xl p-6 border border-outline-variant/60 shadow-lg mb-8">
                <h2 class="font-headline-sm text-headline-sm font-semibold text-on-surface mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">link</span> Your Shareable Channel Link
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mb-4">Append <code class="bg-surface-container-high px-2 py-0.5 rounded text-xs font-mono text-primary">?src=facebook</code> or <code class="bg-surface-container-high px-2 py-0.5 rounded text-xs font-mono text-primary">?src=tiktok</code> to track specific traffic sources.</p>

                <div class="flex items-center gap-3">
                    <input
                        :value="shareableUrl"
                        readonly
                        class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface font-mono text-sm"
                    />
                    <button
                        @click="copyUrl"
                        class="bg-primary text-on-primary px-5 py-2.5 rounded-xl font-label-md text-label-md hover:bg-[#c94918] transition-colors shrink-0 flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-sm">content_copy</span> {{ copied ? 'Copied!' : 'Copy' }}
                    </button>
                </div>
            </div>

            <!-- Stats Overview Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <span class="font-label-md text-label-md text-on-surface-variant">Total Link Referrals</span>
                    <p class="font-display-md text-display-md font-extrabold text-on-surface mt-2">{{ summary.total_referrals }}</p>
                </div>
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <span class="font-label-md text-label-md text-on-surface-variant">Subscribers Converted</span>
                    <p class="font-display-md text-display-md font-extrabold text-primary mt-2">{{ summary.total_conversions }}</p>
                </div>
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <span class="font-label-md text-label-md text-on-surface-variant">Overall Conversion Rate</span>
                    <p class="font-display-md text-display-md font-extrabold text-emerald-400 mt-2">{{ summary.conversion_rate }}%</p>
                </div>
            </div>

            <!-- Breakdown Table -->
            <div class="bg-surface-container rounded-2xl p-6 border border-outline-variant/60 shadow-lg">
                <h2 class="font-headline-sm text-headline-sm font-semibold text-on-surface mb-4">Traffic Breakdown by Social Platform</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-on-surface-variant border-b border-outline-variant/60">
                            <tr>
                                <th class="pb-3 px-3 font-semibold">Social Platform</th>
                                <th class="pb-3 px-3 font-semibold">Link Clicks</th>
                                <th class="pb-3 px-3 font-semibold">New Subscribers</th>
                                <th class="pb-3 px-3 font-semibold">Attributed Watch Mins</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            <tr v-for="src in summary.sources" :key="src.source" class="hover:bg-surface-container-high/40 transition-colors">
                                <td class="py-3.5 px-3 font-medium text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-base">share</span>
                                    {{ src.source }}
                                </td>
                                <td class="py-3.5 px-3 font-mono text-on-surface-variant">{{ src.clicks }}</td>
                                <td class="py-3.5 px-3 font-mono font-bold text-primary">{{ src.conversions }}</td>
                                <td class="py-3.5 px-3 font-mono text-on-surface-variant">{{ src.watch_minutes }} min</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';

interface Props {
    summary: {
        total_referrals: number;
        total_conversions: number;
        conversion_rate: number;
        sources: Array<{ source: string; clicks: number; conversions: number; watch_minutes: number }>;
    };
    shareableUrl: string;
}

const props = defineProps<Props>();
const copied = ref(false);

const copyUrl = () => {
    navigator.clipboard.writeText(props.shareableUrl);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};
</script>
