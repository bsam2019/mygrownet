<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    MegaphoneIcon,
    LinkIcon,
    PlusIcon,
    QrCodeIcon,
    CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

interface Campaign {
    id: number;
    name: string;
    objective: string;
    status: string;
    spend_zmw: number;
    attributed_revenue_zmw: number;
    marketing_roi_ratio: number;
    created_at: string;
}

interface TrackableLink {
    id: number;
    name: string;
    hash: string;
    destination_type: string;
    target_url: string;
    clicks_count: number;
    conversions_count: number;
    created_at: string;
}

interface Props {
    business: {
        id: number;
        name: string;
        slug: string;
    };
    campaigns: Campaign[];
    trackableLinks: TrackableLink[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    target_url: '',
    destination_type: 'whatsapp',
});

const submitLink = () => {
    form.post(route('bizboost.links.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <BizBoostLayout title="Omnichannel Campaigns & Trackable Links">
        <Head title="Campaigns & ROI | BizBoost" />

        <div class="px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <MegaphoneIcon class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
                        Omnichannel Campaigns & Revenue Attribution
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Generate trackable WhatsApp short links, launch SMS campaigns, and measure offline/online ROI.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Create Trackable Link Card -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <LinkIcon class="w-5 h-5 text-indigo-500" />
                        Generate Trackable Link
                    </h2>
                    
                    <form @submit.prevent="submitLink" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Campaign Name</label>
                            <input v-model="form.name" type="text" placeholder="e.g. Solar Promo WhatsApp" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" required />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Target WhatsApp / Phone Number or URL</label>
                            <input v-model="form.target_url" type="text" placeholder="e.g. +260971234567" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" required />
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition-colors">
                            <PlusIcon class="w-4 h-4" /> Create Trackable Link
                        </button>
                    </form>
                </div>

                <!-- Trackable Links Table -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-4">
                        Active Trackable Short Links
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700/50 text-gray-500">
                                <tr>
                                    <th class="p-3">Name</th>
                                    <th class="p-3">Short URL</th>
                                    <th class="p-3">Clicks</th>
                                    <th class="p-3">Conversions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="link in props.trackableLinks" :key="link.id">
                                    <td class="p-3 font-medium text-gray-900 dark:text-white">{{ link.name }}</td>
                                    <td class="p-3 font-mono text-indigo-600 dark:text-indigo-400 text-xs">/bizboost/link/{{ link.hash }}</td>
                                    <td class="p-3">{{ link.clicks_count }}</td>
                                    <td class="p-3 font-semibold text-emerald-600">{{ link.conversions_count }}</td>
                                </tr>
                                <tr v-if="props.trackableLinks.length === 0">
                                    <td colspan="4" class="p-4 text-center text-gray-400 text-xs">No trackable links created yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
