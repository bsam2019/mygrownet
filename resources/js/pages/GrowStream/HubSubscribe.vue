<template>
    <div class="min-h-screen bg-[#110e0c] text-on-surface antialiased font-body-md selection:bg-primary selection:text-on-primary">
        <Head title="Subscribe & Launch Your Creator Hub - GrowStream" />

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-[#18120e]/90 backdrop-blur-xl border-b border-outline-variant/40">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link :href="route('growstream.pages.hub')" class="font-headline-lg text-2xl font-black text-primary tracking-tight">
                    GrowStream <span class="text-on-surface font-light text-xl">Hub</span>
                </Link>
                <div class="text-xs text-on-surface-variant font-label-md">
                    Logged in as <span class="text-on-surface font-semibold">{{ user.name }}</span> ({{ user.email }})
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-6 py-12">
            <div class="text-center mb-10">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/10 border border-primary/30 text-primary text-xs font-semibold uppercase tracking-widest mb-4">
                    <span class="material-symbols-outlined text-sm">rocket_launch</span> Setup &amp; Subscription
                </span>
                <h1 class="font-display-lg text-3xl md:text-5xl font-black tracking-tight text-on-surface">
                    Launch Your <span class="text-primary">Creator Hub Platform</span>
                </h1>
                <p class="font-body-lg text-on-surface-variant mt-2 text-base max-w-xl mx-auto">
                    Set up your branded video academy, domain name, and subscription package.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Selected Plan Card (Col 1) -->
                <div class="bg-surface-container rounded-3xl p-6 border border-primary/40 shadow-xl relative flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="bg-primary/20 text-primary px-3 py-1 rounded-full text-xs font-bold uppercase">Selected Plan</span>
                        </div>
                        <h2 class="font-headline-sm text-2xl font-bold text-on-surface mb-1">{{ plan.name }}</h2>
                        <p class="font-body-md text-xs text-on-surface-variant mb-4">{{ plan.description }}</p>

                        <div class="mb-6">
                            <span class="font-display-md text-4xl font-black text-on-surface">{{ plan.price }}</span>
                            <span class="font-body-md text-xs text-on-surface-variant ml-1">{{ plan.period }}</span>
                        </div>

                        <ul class="space-y-3 font-body-md text-xs text-on-surface-variant">
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ plan.storage }}
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ plan.bandwidth }}
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ plan.domain }}
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ plan.byop ? 'BYOP Direct Settlement' : 'Platform Payment Settlement' }}
                            </li>
                        </ul>
                    </div>

                    <!-- Change Plan Options -->
                    <div class="mt-8 pt-4 border-t border-outline-variant/40">
                        <label class="block text-xs font-label-md text-on-surface-variant mb-2">Switch Subscription Plan:</label>
                        <div class="space-y-2">
                            <button
                                v-for="t in pricingTiers"
                                :key="t.name"
                                type="button"
                                @click="selectPlan(t)"
                                :class="[
                                    'w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between border transition-all',
                                    plan.name === t.name ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-outline-variant/40 bg-surface-container-low text-on-surface-variant hover:border-outline-variant'
                                ]"
                            >
                                <span>{{ t.name }}</span>
                                <span class="font-mono">{{ t.price }}{{ t.period }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Section (Col 2) -->
                <div class="md:col-span-2 bg-surface-container/60 rounded-3xl p-8 border border-outline-variant/60">
                    <h2 class="font-headline-sm text-xl font-bold text-on-surface mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">domain</span> Configure Platform Details
                    </h2>

                    <form @submit.prevent="submitSubscription" class="space-y-6">
                        <div>
                            <label class="block font-label-md text-sm text-on-surface mb-2">Platform / Academy Brand Name</label>
                            <input
                                v-model="form.brand_name"
                                type="text"
                                placeholder="e.g. Acme Online Tuition Academy"
                                class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:outline-none"
                                required
                            />
                            <p class="text-xs text-on-surface-variant mt-1">This will be displayed to your students and clients.</p>
                        </div>

                        <div>
                            <label class="block font-label-md text-sm text-on-surface mb-2">Hosted Platform Subdomain</label>
                            <div class="flex items-center">
                                <input
                                    v-model="form.subdomain"
                                    type="text"
                                    placeholder="acme"
                                    class="w-full px-4 py-3 rounded-l-xl border border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:outline-none font-mono"
                                    required
                                />
                                <span class="bg-surface-container-high px-4 py-3 rounded-r-xl border border-l-0 border-outline-variant text-xs text-on-surface-variant font-mono">.growstream.app</span>
                            </div>
                            <p v-if="form.errors.subdomain" class="text-xs text-red-400 mt-1">{{ form.errors.subdomain }}</p>
                        </div>

                        <div>
                            <label class="block font-label-md text-sm text-on-surface mb-2">Payment Method</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label
                                    :class="[
                                        'p-4 rounded-xl border cursor-pointer flex items-center gap-3 transition-all',
                                        form.payment_method === 'momo' ? 'border-primary bg-primary/10 text-on-surface' : 'border-outline-variant/60 bg-surface-container-low text-on-surface-variant'
                                    ]"
                                >
                                    <input type="radio" v-model="form.payment_method" value="momo" class="sr-only" />
                                    <span class="material-symbols-outlined text-primary">smartphone</span>
                                    <div>
                                        <p class="font-bold text-xs">MTN / Airtel Mobile Money</p>
                                        <p class="text-[10px] opacity-80">Instant Local Settlement</p>
                                    </div>
                                </label>

                                <label
                                    :class="[
                                        'p-4 rounded-xl border cursor-pointer flex items-center gap-3 transition-all',
                                        form.payment_method === 'card' ? 'border-primary bg-primary/10 text-on-surface' : 'border-outline-variant/60 bg-surface-container-low text-on-surface-variant'
                                    ]"
                                >
                                    <input type="radio" v-model="form.payment_method" value="card" class="sr-only" />
                                    <span class="material-symbols-outlined text-primary">credit_card</span>
                                    <div>
                                        <p class="font-bold text-xs">Paystack Card / Bank Transfer</p>
                                        <p class="text-[10px] opacity-80">Debit &amp; Credit Cards</p>
                                    </div>
                                </label>

                                <!-- Admin Bypass Option -->
                                <label
                                    v-if="user.is_admin"
                                    :class="[
                                        'sm:col-span-2 p-4 rounded-xl border cursor-pointer flex items-center gap-3 transition-all',
                                        form.payment_method === 'admin_bypass' ? 'border-amber-500 bg-amber-500/10 text-on-surface' : 'border-outline-variant/60 bg-surface-container-low text-on-surface-variant'
                                    ]"
                                >
                                    <input type="radio" v-model="form.payment_method" value="admin_bypass" class="sr-only" />
                                    <span class="material-symbols-outlined text-amber-500">admin_panel_settings</span>
                                    <div>
                                        <p class="font-bold text-xs text-amber-500">Instant Admin Activation (Free Override)</p>
                                        <p class="text-[10px] opacity-80">Platform Administrator Direct Provisioning</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-outline-variant/40 flex items-center justify-between">
                            <Link :href="route('growstream.pages.hub')" class="text-xs text-on-surface-variant hover:text-on-surface">
                                ← Back to Hub Overview
                            </Link>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-primary text-on-primary px-8 py-3.5 rounded-full font-label-md text-sm font-bold hover:bg-[#c94918] transition-all shadow-xl shadow-primary/20"
                            >
                                {{ form.payment_method === 'admin_bypass' ? 'Activate Platform (Admin)' : `Pay ${plan.price} & Launch Platform` }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
        is_admin: boolean;
    };
    selectedPlan: any;
    pricingTiers: any[];
}

const props = defineProps<Props>();

const plan = ref(props.selectedPlan);

const form = useForm({
    brand_name: props.user.name + ' Academy',
    subdomain: props.user.name.toLowerCase().replace(/[^a-z0-9]/g, ''),
    plan: plan.value.name.toLowerCase().replace(' hub', '').replace(/\s+/g, ''),
    payment_method: props.user.is_admin ? 'admin_bypass' : 'momo',
});

const selectPlan = (newPlan: any) => {
    plan.value = newPlan;
    form.plan = newPlan.name.toLowerCase().replace(' hub', '').replace(/\s+/g, '');
};

const submitSubscription = () => {
    form.post(route('growstream.hub.subscribe.process'));
};
</script>
