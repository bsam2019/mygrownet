<template>
    <GrowStreamLayout title="Creator Platform Settings - GrowStream Studio">
        <div class="max-w-4xl mx-auto py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-headline-lg text-headline-lg font-bold text-on-surface">Creator Platform Hub</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                        Manage your independent branded platform, custom domains, BYOP payment keys, and storage quotas.
                    </p>
                </div>
            </div>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Main Form (Col 2) -->
                <div class="md:col-span-2 space-y-8">
                    <!-- Branding & Domain Card -->
                    <div class="bg-surface-container rounded-2xl p-6 border border-outline-variant/60 shadow-lg">
                        <h2 class="font-headline-sm text-headline-sm font-semibold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">domain</span> Platform Branding &amp; Domain
                        </h2>

                        <form @submit.prevent="saveSettings" class="space-y-4">
                            <div>
                                <label class="block font-label-md text-label-md text-on-surface mb-1">Platform Category</label>
                                <select
                                    v-model="form.category"
                                    class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface"
                                >
                                    <option v-for="cat in allCategories" :key="cat.category" :value="cat.category">
                                        {{ cat.category_name }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-label-md text-label-md text-on-surface mb-1">Hosted Subdomain</label>
                                    <div class="flex items-center">
                                        <input
                                            v-model="form.subdomain"
                                            type="text"
                                            placeholder="acme"
                                            class="w-full px-4 py-2.5 rounded-l-xl border border-outline-variant bg-surface-container-lowest text-on-surface"
                                        />
                                        <span class="bg-surface-container-high px-3 py-2.5 rounded-r-xl border border-l-0 border-outline-variant text-xs text-on-surface-variant font-mono">.growstream.app</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-label-md text-label-md text-on-surface mb-1">Custom Domain</label>
                                    <input
                                        v-model="form.custom_domain"
                                        type="text"
                                        placeholder="www.acmeacademy.com"
                                        class="w-full px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block font-label-md text-label-md text-on-surface mb-1">Brand Accent Color</label>
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model="form.brand_color"
                                        type="color"
                                        class="h-10 w-12 rounded cursor-pointer border border-outline-variant bg-transparent p-1"
                                    />
                                    <input
                                        v-model="form.brand_color"
                                        type="text"
                                        class="w-36 px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface font-mono text-sm"
                                    />
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-primary text-on-primary px-6 py-2.5 rounded-full font-label-md text-label-md hover:bg-[#c94918] transition-colors"
                                >
                                    Save Platform Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- BYOP Gateway Card -->
                    <div class="bg-surface-container rounded-2xl p-6 border border-outline-variant/60 shadow-lg">
                        <h2 class="font-headline-sm text-headline-sm font-semibold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">payments</span> Bring Your Own Payment (BYOP)
                        </h2>
                        <p class="font-body-md text-body-md text-on-surface-variant mb-4">
                            Connect your own Paystack, Flutterwave, or local gateway API keys to receive student payments directly into your account.
                        </p>

                        <div class="space-y-3">
                            <div v-for="gw in availableGateways" :key="gw.slug" class="flex items-center justify-between p-4 rounded-xl bg-surface-container-low border border-outline-variant/40">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary">credit_card</span>
                                    <div>
                                        <p class="font-label-md text-label-md text-on-surface">{{ gw.name }}</p>
                                        <p class="font-label-sm text-xs text-on-surface-variant">{{ gw.description }}</p>
                                    </div>
                                </div>
                                <span class="bg-primary/10 text-primary px-3 py-1 rounded-full font-label-sm text-xs font-semibold">Configured</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quota Sidebar (Col 1) -->
                <div class="space-y-6">
                    <div class="bg-surface-container rounded-2xl p-6 border border-outline-variant/60 shadow-lg">
                        <h3 class="font-headline-sm text-headline-sm font-semibold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">donut_large</span> Quota &amp; Usage
                        </h3>

                        <div class="space-y-5">
                            <!-- Storage -->
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-on-surface-variant font-label-sm">Video Storage</span>
                                    <span class="font-mono text-xs text-primary font-bold">{{ quota.current_storage_minutes }} / {{ quota.storage_minutes_limit }} min</span>
                                </div>
                                <div class="h-2 rounded-full bg-surface-container-highest overflow-hidden">
                                    <div class="h-full bg-primary transition-all duration-500" :style="{ width: `${quota.storage_percentage}%` }"></div>
                                </div>
                            </div>

                            <!-- Bandwidth -->
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-on-surface-variant font-label-sm">Streaming Delivery</span>
                                    <span class="font-mono text-xs text-primary font-bold">{{ quota.current_delivery_gb }} / {{ quota.delivery_gb_limit }} GB</span>
                                </div>
                                <div class="h-2 rounded-full bg-surface-container-highest overflow-hidden">
                                    <div class="h-full bg-amber-500 transition-all duration-500" :style="{ width: `${quota.delivery_percentage}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';

interface Props {
    platform?: any;
    quotaSummary?: any;
    allCategories?: any[];
}

const props = withDefaults(defineProps<Props>(), {
    platform: () => ({
        brand_name: '',
        category: 'education',
        subdomain: '',
        custom_domain: '',
        brand_color: '#e2571f',
    }),
    allCategories: () => [
        { category: 'education', category_name: 'Education & Academies' },
        { category: 'business', category_name: 'Corporate & Internal Training' },
        { category: 'media', category_name: 'Media & Entertainment Studios' },
        { category: 'creator', category_name: 'Independent Content Creator' },
    ],
    quotaSummary: () => ({
        current_storage_minutes: 120,
        storage_minutes_limit: 1000,
        storage_percentage: 12,
        current_delivery_gb: 15,
        delivery_gb_limit: 100,
        delivery_percentage: 15,
    }),
});

const form = useForm({
    brand_name: props.platform.brand_name || '',
    category: props.platform.category || 'education',
    subdomain: props.platform.subdomain || '',
    custom_domain: props.platform.custom_domain || '',
    brand_color: props.platform.brand_color || '#e2571f',
});

const quota = ref(props.quotaSummary);

const availableGateways = [
    { slug: 'paystack', name: 'Paystack Direct', description: 'Direct card and mobile money settlement' },
    { slug: 'flutterwave', name: 'Flutterwave', description: 'Pan-African payments & payouts' },
    { slug: 'pawapay', name: 'PawaPay Mobile Money', description: 'Direct MTN / Airtel / Zamtel mobile money' },
];

const saveSettings = () => {
    form.put(route('growstream.creator.platform.update'), {
        preserveScroll: true,
    });
};
</script>
