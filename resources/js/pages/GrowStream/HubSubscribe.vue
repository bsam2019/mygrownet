<template>
    <div class="min-h-screen bg-[#110e0c] text-on-surface antialiased font-body-md selection:bg-primary selection:text-on-primary">
        <Head title="Launch Your Creator Hub Platform - GrowStream" />

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-[#18120e]/90 backdrop-blur-xl border-b border-outline-variant/40">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link :href="route('growstream.hub.landing')" class="font-headline-lg text-2xl font-black text-primary tracking-tight">
                    GrowStream <span class="text-on-surface font-light text-xl">Hub</span>
                </Link>
                <div class="text-xs text-on-surface-variant font-label-md flex items-center gap-3">
                    <span>Logged in as <span class="text-on-surface font-semibold">{{ user.name }}</span></span>
                    <Link :href="route('growstream.home')" class="text-xs text-primary hover:underline">Exit to Main Site</Link>
                </div>
            </div>
        </header>

        <!-- Main Onboarding Content -->
        <div class="max-w-4xl mx-auto px-6 py-10">
            <!-- Header Progress Banner -->
            <div class="text-center mb-8">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold uppercase tracking-widest mb-3">
                    <span class="material-symbols-outlined text-sm">rocket_launch</span> Creator Hub Onboarding Wizard
                </span>
                <h1 class="font-display-lg text-3xl md:text-4xl font-black tracking-tight text-on-surface">
                    Launch Your <span class="text-primary">Creator Hub Platform</span>
                </h1>
                <p class="font-body-lg text-on-surface-variant mt-2 text-sm max-w-lg mx-auto">
                    4 quick steps to provision your white-label video academy &amp; custom domain.
                </p>
            </div>

            <!-- Onboarding Stepper Bar -->
            <div class="mb-10 max-w-2xl mx-auto">
                <div class="flex items-center justify-between relative">
                    <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-outline-variant/40 -translate-y-1/2 -z-0"></div>
                    <div
                        v-for="(stepName, index) in wizardSteps"
                        :key="stepName"
                        class="relative z-10 flex flex-col items-center gap-1.5"
                    >
                        <button
                            type="button"
                            @click="currentStep = index"
                            :class="[
                                'w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold transition-all',
                                index === currentStep
                                    ? 'bg-primary text-on-primary ring-4 ring-primary/20 scale-110'
                                    : (index < currentStep ? 'bg-emerald-500 text-white' : 'bg-surface-container-high text-on-surface-variant border border-outline-variant/60')
                            ]"
                        >
                            <span v-if="index < currentStep" class="material-symbols-outlined text-sm">check</span>
                            <span v-else>{{ index + 1 }}</span>
                        </button>
                        <span class="text-[11px] font-semibold tracking-tight" :class="index === currentStep ? 'text-primary' : 'text-on-surface-variant'">
                            {{ stepName }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Step Cards -->
            <form @submit.prevent="submitSubscription" class="bg-surface-container/80 border border-outline-variant/60 rounded-3xl p-6 md:p-10 shadow-2xl space-y-8">
                <!-- STEP 1: Platform Identity & Subdomain -->
                <div v-if="currentStep === 0" class="space-y-6">
                    <div class="border-b border-outline-variant/40 pb-4">
                        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">domain</span> Step 1: Platform Identity &amp; Subdomain
                        </h2>
                        <p class="text-xs text-on-surface-variant mt-1">Name your tuition academy or video platform and pick a web address.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Platform / Academy Brand Name *</label>
                            <input
                                v-model="form.brand_name"
                                type="text"
                                placeholder="e.g. Banda Mathematics Tuition Academy"
                                class="w-full px-4 py-3.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:outline-none text-sm"
                                required
                            />
                            <p class="text-xs text-on-surface-variant mt-1">Displayed on student certificates, invoices, and site header.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Platform Subdomain *</label>
                            <div class="flex items-center">
                                <input
                                    v-model="form.subdomain"
                                    type="text"
                                    placeholder="bandamaths"
                                    class="w-full px-4 py-3.5 rounded-l-xl border border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:outline-none font-mono text-sm"
                                    required
                                />
                                <span class="bg-surface-container-high px-4 py-3.5 rounded-r-xl border border-l-0 border-outline-variant text-xs text-on-surface-variant font-mono shrink-0">.growstream.app</span>
                            </div>
                            <p v-if="form.errors.subdomain" class="text-xs text-red-400 mt-1.5 font-bold">{{ form.errors.subdomain }}</p>
                            <p v-else class="text-xs text-emerald-400 mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">check_circle</span> Web Link: https://{{ form.subdomain || 'yourname' }}.growstream.app
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Primary Category / Purpose *</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label
                                    v-for="cat in categoryOptions"
                                    :key="cat.value"
                                    :class="[
                                        'p-4 rounded-2xl border cursor-pointer transition-all flex items-start gap-3',
                                        form.category === cat.value ? 'border-primary bg-primary/10 text-on-surface' : 'border-outline-variant/60 bg-surface-container-low text-on-surface-variant'
                                    ]"
                                >
                                    <input type="radio" v-model="form.category" :value="cat.value" class="sr-only" />
                                    <span class="material-symbols-outlined text-primary text-2xl mt-0.5">{{ cat.icon }}</span>
                                    <div>
                                        <p class="font-bold text-xs text-on-surface">{{ cat.title }}</p>
                                        <p class="text-[11px] text-on-surface-variant mt-0.5">{{ cat.desc }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Branding & Color Customization -->
                <div v-else-if="currentStep === 1" class="space-y-6">
                    <div class="border-b border-outline-variant/40 pb-4">
                        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">palette</span> Step 2: Brand Identity &amp; Color Theme
                        </h2>
                        <p class="text-xs text-on-surface-variant mt-1">Select your primary accent color and brand theme for student web portals.</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-3">Brand Theme Preset</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <button
                                    v-for="color in colorPresets"
                                    :key="color.hex"
                                    type="button"
                                    @click="form.brand_color = color.hex"
                                    :class="[
                                        'p-4 rounded-2xl border transition-all flex flex-col items-center justify-center gap-2',
                                        form.brand_color === color.hex ? 'border-white ring-2 ring-primary scale-105' : 'border-outline-variant/40 hover:bg-surface-container-high'
                                    ]"
                                >
                                    <div class="w-8 h-8 rounded-full shadow-lg border border-white/20" :style="{ backgroundColor: color.hex }"></div>
                                    <span class="text-xs font-bold text-on-surface">{{ color.name }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Live Portal Preview Banner -->
                        <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 space-y-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Live Student Portal Header Preview</p>
                            <div class="p-4 rounded-xl flex items-center justify-between text-white shadow-xl" :style="{ backgroundColor: form.brand_color }">
                                <div class="font-bold text-sm">{{ form.brand_name || 'My Tuition Academy' }}</div>
                                <div class="text-xs font-semibold px-3 py-1 rounded-full bg-white/20">Student Portal</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Select Pricing Plan -->
                <div v-else-if="currentStep === 2" class="space-y-6">
                    <div class="border-b border-outline-variant/40 pb-4">
                        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">payments</span> Step 3: Select Creator Hub Subscription Plan
                        </h2>
                        <p class="text-xs text-on-surface-variant mt-1">Choose a plan matching your storage, video streaming, and domain requirements.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            v-for="t in pricingTiers"
                            :key="t.name"
                            @click="selectPlan(t)"
                            :class="[
                                'p-6 rounded-3xl border cursor-pointer transition-all flex flex-col justify-between relative',
                                plan.name === t.name ? 'border-primary bg-primary/10 ring-2 ring-primary' : 'border-outline-variant/60 bg-surface-container-low hover:border-outline-variant'
                            ]"
                        >
                            <div v-if="t.is_popular" class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-3 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                Most Popular
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-on-surface mb-1">{{ t.name }}</h3>
                                <p class="text-xs text-on-surface-variant mb-4">{{ t.description }}</p>

                                <div class="mb-5">
                                    <span class="text-3xl font-black text-on-surface">{{ t.price }}</span>
                                    <span class="text-xs text-on-surface-variant">{{ t.period }}</span>
                                </div>

                                <ul class="space-y-2.5 text-xs text-on-surface-variant">
                                    <li class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ t.storage }}
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ t.bandwidth }}
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ t.domain }}
                                    </li>
                                </ul>
                            </div>

                            <button
                                type="button"
                                :class="[
                                    'mt-6 w-full py-2.5 rounded-full text-xs font-bold transition-colors',
                                    plan.name === t.name ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface hover:bg-surface-container-highest'
                                ]"
                            >
                                {{ plan.name === t.name ? 'Selected Plan' : 'Select Plan' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Payment Method & Launch -->
                <div v-else-if="currentStep === 3" class="space-y-6">
                    <div class="border-b border-outline-variant/40 pb-4">
                        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400">task_alt</span> Step 4: Confirm Setup &amp; Activate Platform
                        </h2>
                        <p class="text-xs text-on-surface-variant mt-1">Review your hub setup and select a settlement payment option.</p>
                    </div>

                    <!-- Summary Card -->
                    <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 space-y-3">
                        <div class="flex items-center justify-between text-xs border-b border-outline-variant/40 pb-2">
                            <span class="text-on-surface-variant">Academy Name:</span>
                            <span class="font-bold text-on-surface">{{ form.brand_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs border-b border-outline-variant/40 pb-2">
                            <span class="text-on-surface-variant">Subdomain URL:</span>
                            <span class="font-mono font-bold text-primary">https://{{ form.subdomain }}.growstream.app</span>
                        </div>
                        <div class="flex items-center justify-between text-xs border-b border-outline-variant/40 pb-2">
                            <span class="text-on-surface-variant">Subscription Plan:</span>
                            <span class="font-bold text-on-surface">{{ plan.name }} ({{ plan.price }}{{ plan.period }})</span>
                        </div>
                    </div>

                    <!-- Payment Options -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-3">Select Payment Method</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label
                                :class="[
                                    'p-4 rounded-2xl border cursor-pointer flex items-center gap-3 transition-all',
                                    form.payment_method === 'momo' ? 'border-primary bg-primary/10 text-on-surface' : 'border-outline-variant/60 bg-surface-container-low text-on-surface-variant'
                                ]"
                            >
                                <input type="radio" v-model="form.payment_method" value="momo" class="sr-only" />
                                <span class="material-symbols-outlined text-primary text-2xl">smartphone</span>
                                <div>
                                    <p class="font-bold text-xs text-on-surface">MTN / Airtel Mobile Money</p>
                                    <p class="text-[11px] text-on-surface-variant">Instant Local Settlement</p>
                                </div>
                            </label>

                            <label
                                :class="[
                                    'p-4 rounded-2xl border cursor-pointer flex items-center gap-3 transition-all',
                                    form.payment_method === 'card' ? 'border-primary bg-primary/10 text-on-surface' : 'border-outline-variant/60 bg-surface-container-low text-on-surface-variant'
                                ]"
                            >
                                <input type="radio" v-model="form.payment_method" value="card" class="sr-only" />
                                <span class="material-symbols-outlined text-primary text-2xl">credit_card</span>
                                <div>
                                    <p class="font-bold text-xs text-on-surface">Paystack Debit / Credit Card</p>
                                    <p class="text-[11px] text-on-surface-variant">Visa &amp; MasterCard</p>
                                </div>
                            </label>

                            <!-- Admin Instant Activation Override -->
                            <label
                                v-if="user.is_admin"
                                :class="[
                                    'sm:col-span-2 p-4 rounded-2xl border cursor-pointer flex items-center gap-3 transition-all',
                                    form.payment_method === 'admin_bypass' ? 'border-amber-500 bg-amber-500/10 text-on-surface' : 'border-outline-variant/60 bg-surface-container-low text-on-surface-variant'
                                ]"
                            >
                                <input type="radio" v-model="form.payment_method" value="admin_bypass" class="sr-only" />
                                <span class="material-symbols-outlined text-amber-500 text-2xl">admin_panel_settings</span>
                                <div>
                                    <p class="font-bold text-xs text-amber-400">Instant Admin Provisioning (Free Test Activation)</p>
                                    <p class="text-[11px] text-on-surface-variant">Direct Administrator Bypass</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Wizard Navigation Footer Buttons -->
                <div class="pt-6 border-t border-outline-variant/40 flex items-center justify-between">
                    <button
                        v-if="currentStep > 0"
                        type="button"
                        @click="currentStep--"
                        class="px-5 py-2.5 rounded-full border border-outline-variant/60 text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-colors"
                    >
                        ← Previous Step
                    </button>
                    <div v-else></div>

                    <button
                        v-if="currentStep < 3"
                        type="button"
                        @click="currentStep++"
                        class="px-7 py-3 rounded-full bg-primary text-on-primary text-xs font-bold hover:bg-[#c94918] transition-colors flex items-center gap-1.5 shadow-lg shadow-primary/20"
                    >
                        <span>Continue to Step {{ currentStep + 2 }}</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>

                    <button
                        v-else
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3.5 rounded-full bg-emerald-500 text-black text-sm font-bold hover:bg-emerald-400 transition-all shadow-xl shadow-emerald-500/20 flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined text-lg">rocket_launch</span>
                        <span>{{ form.payment_method === 'admin_bypass' ? 'Provision Platform Now (Admin)' : `Pay ${plan.price} & Launch Platform` }}</span>
                    </button>
                </div>
            </form>
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

const wizardSteps = ['Identity', 'Branding', 'Plan', 'Activation'];
const currentStep = ref(0);

const plan = ref(props.selectedPlan);

const categoryOptions = [
    { value: 'education', icon: 'school', title: 'Tuition & Education', desc: 'Online classes, subject modules & enrolled students' },
    { value: 'business', icon: 'business_center', title: 'Corporate Training', desc: 'Employee onboarding, safety & compliance modules' },
    { value: 'media', icon: 'movie', title: 'VIP Media Channel', desc: 'Exclusive films, series & subscriber passes' },
    { value: 'creator', icon: 'workspace_premium', title: 'Coaching & Mentorship', desc: 'Masterclasses, community & student clients' },
];

const colorPresets = [
    { name: 'Amber Orange', hex: '#e2571f' },
    { name: 'Emerald Green', hex: '#059669' },
    { name: 'Royal Blue', hex: '#0284c7' },
    { name: 'Purple Violet', hex: '#7c3aed' },
];

const form = useForm({
    brand_name: props.user.name + ' Academy',
    subdomain: props.user.name.toLowerCase().replace(/[^a-z0-9]/g, ''),
    category: 'education',
    brand_color: '#e2571f',
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
