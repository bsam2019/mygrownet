<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    BuildingOffice2Icon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    ClockIcon,
    CheckBadgeIcon,
    QrCodeIcon,
    GlobeAltIcon,
    CurrencyDollarIcon,
    ShoppingBagIcon,
} from '@heroicons/vue/24/outline';

interface IndustryOption {
    value: string;
    label: string;
}

interface Props {
    site: Record<string, any>;
    profile: Record<string, any>;
    industryOptions: IndustryOption[];
    blueprintOptions: IndustryOption[];
}

const props = defineProps<Props>();

const activeTab = ref<'identity' | 'contact' | 'services' | 'trust' | 'hours'>('identity');

const tabs = [
    { id: 'identity', label: 'Identity', icon: BuildingOffice2Icon },
    { id: 'contact',  label: 'Contact',  icon: PhoneIcon },
    { id: 'services', label: 'Services', icon: ShoppingBagIcon },
    { id: 'trust',    label: 'Trust',    icon: CheckBadgeIcon },
    { id: 'hours',    label: 'Hours',    icon: ClockIcon },
];

const defaultHours = [
    { day: 'Monday',    open: '08:00', close: '17:00', closed: false },
    { day: 'Tuesday',   open: '08:00', close: '17:00', closed: false },
    { day: 'Wednesday', open: '08:00', close: '17:00', closed: false },
    { day: 'Thursday',  open: '08:00', close: '17:00', closed: false },
    { day: 'Friday',    open: '08:00', close: '17:00', closed: false },
    { day: 'Saturday',  open: '08:00', close: '13:00', closed: false },
    { day: 'Sunday',    open: '',      close: '',       closed: true  },
];

const parseJson = (val: any, fallback: any) => {
    if (!val) return fallback;
    if (typeof val === 'object') return val;
    try { return JSON.parse(val); } catch { return fallback; }
};

const form = useForm({
    legal_name:         props.profile?.legal_name ?? '',
    trade_name:         props.profile?.trade_name ?? '',
    tpin:               props.profile?.tpin ?? '',
    pacra_number:       props.profile?.pacra_number ?? '',
    phone:              props.profile?.phone ?? '',
    whatsapp:           props.profile?.whatsapp ?? '',
    email:              props.profile?.email ?? '',
    website:            props.profile?.website ?? '',
    physical_address:   props.profile?.physical_address ?? '',
    city:               props.profile?.city ?? '',
    province:           props.profile?.province ?? '',
    country:            props.profile?.country ?? 'ZM',
    industry:           props.profile?.industry ?? '',
    industry_blueprint: props.profile?.industry_blueprint ?? '',
    tagline:            props.profile?.tagline ?? '',
    description:        props.profile?.description ?? '',
    price_range:        props.profile?.price_range ?? '',
    opening_hours:      parseJson(props.profile?.opening_hours, defaultHours),
    services_json:      parseJson(props.profile?.services_json, []),
    payment_methods:    parseJson(props.profile?.payment_methods, []),
    trust_badges_json:  parseJson(props.profile?.trust_badges_json, []),
    pacra_verified:     !!props.profile?.pacra_verified,
    tpin_verified:      !!props.profile?.tpin_verified,
});

const paymentOptions = [
    { value: 'mtn_momo',      label: 'MTN Mobile Money' },
    { value: 'airtel_money',  label: 'Airtel Money' },
    { value: 'cash',          label: 'Cash' },
    { value: 'card',          label: 'Credit/Debit Card' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'paystack',      label: 'Paystack' },
];

const profileCompletion = computed(() => {
    const fields = [
        form.trade_name, form.phone, form.email, form.physical_address,
        form.city, form.industry, form.description, form.tagline,
    ];
    const filledCount = fields.filter(f => f && String(f).trim().length > 0).length;
    return Math.round((filledCount / fields.length) * 100);
});

function addService() {
    form.services_json = [...(form.services_json as any[]), { name: '', description: '', price: '' }];
}
function removeService(index: number) {
    (form.services_json as any[]).splice(index, 1);
}

function togglePaymentMethod(value: string) {
    const methods = form.payment_methods as string[];
    const idx = methods.indexOf(value);
    if (idx >= 0) methods.splice(idx, 1);
    else methods.push(value);
}

function hasPaymentMethod(value: string) {
    return (form.payment_methods as string[]).includes(value);
}

function submit() {
    form.put(`/dashboard/sites/${props.site.id}/business-profile`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Business Profile — ${site.name}`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 text-white">
        <!-- Header -->
        <div class="border-b border-white/10 bg-white/5 backdrop-blur-sm">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center gap-4">
                <div class="p-2.5 bg-blue-500/20 rounded-xl ring-1 ring-blue-400/30">
                    <BuildingOffice2Icon class="h-6 w-6 text-blue-400" />
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white">Business Profile</h1>
                    <p class="text-xs text-slate-400">{{ site.name }} — structured business identity</p>
                </div>
                <!-- Completion Badge -->
                <div class="ml-auto flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Profile Completion</p>
                        <p class="text-sm font-bold" :class="profileCompletion >= 80 ? 'text-emerald-400' : 'text-amber-400'">
                            {{ profileCompletion }}%
                        </p>
                    </div>
                    <div class="h-10 w-10 rounded-full ring-2 flex items-center justify-center text-xs font-bold"
                         :class="profileCompletion >= 80 ? 'ring-emerald-400 text-emerald-400' : 'ring-amber-400 text-amber-400'">
                        {{ profileCompletion }}
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 py-8">
            <!-- Strategy banner -->
            <div class="mb-6 rounded-xl bg-blue-500/10 border border-blue-400/20 p-4 flex items-start gap-3">
                <GlobeAltIcon class="h-5 w-5 text-blue-400 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-blue-300">Your Business Profile powers everything</p>
                    <p class="text-xs text-slate-400 mt-0.5">AI site generation, JSON-LD SEO schema, WhatsApp ordering, QR codes, and multi-channel publishing all draw from this structured data.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Tab Navigation -->
                <div class="flex gap-1 bg-white/5 rounded-xl p-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        @click="activeTab = tab.id as any"
                        class="flex-1 flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-xs font-medium transition-all duration-200"
                        :class="activeTab === tab.id
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/25'
                            : 'text-slate-400 hover:text-white hover:bg-white/5'"
                    >
                        <component :is="tab.icon" class="h-4 w-4" />
                        {{ tab.label }}
                    </button>
                </div>

                <!-- ── Identity Tab ── -->
                <div v-show="activeTab === 'identity'" class="space-y-4">
                    <div class="rounded-xl bg-white/5 border border-white/10 p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-white">Business Identity</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Legal Name</label>
                                <input v-model="form.legal_name" type="text" placeholder="Taradasi Medics Limited"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Trading / Brand Name</label>
                                <input v-model="form.trade_name" type="text" placeholder="Taradasi Medics"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Industry</label>
                                <select v-model="form.industry"
                                        class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select industry...</option>
                                    <option v-for="opt in industryOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Industry Blueprint</label>
                                <select v-model="form.industry_blueprint"
                                        class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select blueprint...</option>
                                    <option v-for="opt in blueprintOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-slate-400 mb-1">Tagline</label>
                                <input v-model="form.tagline" type="text" placeholder="Your trusted health partner in Lusaka"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-slate-400 mb-1">Description</label>
                                <textarea v-model="form.description" rows="3" placeholder="Tell customers about your business..."
                                          class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Price Range</label>
                                <select v-model="form.price_range"
                                        class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select...</option>
                                    <option value="$">$ — Budget</option>
                                    <option value="$$">$$ — Mid-range</option>
                                    <option value="$$$">$$$ — Premium</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div>
                            <label class="block text-xs text-slate-400 mb-2">Payment Methods Accepted</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="method in paymentOptions"
                                    :key="method.value"
                                    type="button"
                                    @click="togglePaymentMethod(method.value)"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all"
                                    :class="hasPaymentMethod(method.value)
                                        ? 'bg-blue-600 border-blue-500 text-white'
                                        : 'bg-white/5 border-white/20 text-slate-400 hover:border-white/40'"
                                >
                                    {{ method.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Contact Tab ── -->
                <div v-show="activeTab === 'contact'" class="space-y-4">
                    <div class="rounded-xl bg-white/5 border border-white/10 p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-white">Contact & Location</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Phone Number</label>
                                <input v-model="form.phone" type="tel" placeholder="+260977123456"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">WhatsApp Number</label>
                                <input v-model="form.whatsapp" type="tel" placeholder="+260977123456"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <p class="text-xs text-slate-500 mt-1">Powers the WhatsApp ordering button on your site</p>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Email Address</label>
                                <input v-model="form.email" type="email" placeholder="info@taradasimedics.com"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Website (optional)</label>
                                <input v-model="form.website" type="url" placeholder="https://taradasimedics.com"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-slate-400 mb-1">Physical Address</label>
                                <input v-model="form.physical_address" type="text" placeholder="Plot 123, Cairo Road"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">City</label>
                                <input v-model="form.city" type="text" placeholder="Lusaka"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Province</label>
                                <input v-model="form.province" type="text" placeholder="Lusaka Province"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Services Tab ── -->
                <div v-show="activeTab === 'services'" class="space-y-4">
                    <div class="rounded-xl bg-white/5 border border-white/10 p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-white">Services & Offerings</h2>
                            <button type="button" @click="addService"
                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 rounded-lg text-xs font-medium transition-colors">
                                + Add Service
                            </button>
                        </div>
                        <p class="text-xs text-slate-400">Services listed here are used to auto-generate your site's services section and feed the JSON-LD schema for Google.</p>

                        <div v-for="(service, idx) in (form.services_json as any[])" :key="idx"
                             class="rounded-lg bg-white/5 border border-white/10 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 font-medium">Service {{ idx + 1 }}</span>
                                <button type="button" @click="removeService(idx)"
                                        class="text-red-400 hover:text-red-300 text-xs">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <input v-model="service.name" type="text" placeholder="Service name"
                                       class="sm:col-span-2 w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <input v-model="service.price" type="text" placeholder="Price (K)"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <textarea v-model="service.description" rows="2" placeholder="Description..."
                                          class="sm:col-span-3 w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
                            </div>
                        </div>

                        <div v-if="!(form.services_json as any[]).length"
                             class="text-center py-8 text-slate-500 text-sm">
                            No services added yet. Click "Add Service" to start.
                        </div>
                    </div>
                </div>

                <!-- ── Trust Tab ── -->
                <div v-show="activeTab === 'trust'" class="space-y-4">
                    <div class="rounded-xl bg-white/5 border border-white/10 p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-white">Trust & Compliance (Zambia)</h2>
                        <p class="text-xs text-slate-400">TPIN and PACRA trust badges are displayed on your site to increase customer confidence and local SEO ranking.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">TPIN Number</label>
                                <input v-model="form.tpin" type="text" placeholder="1234567890"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">PACRA Registration Number</label>
                                <input v-model="form.pacra_number" type="text" placeholder="120123456789101"
                                       class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.tpin_verified" type="checkbox"
                                       class="h-4 w-4 rounded accent-blue-500" />
                                <span class="text-sm text-slate-300">TPIN verified — display badge on site</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.pacra_verified" type="checkbox"
                                       class="h-4 w-4 rounded accent-blue-500" />
                                <span class="text-sm text-slate-300">PACRA verified — display badge on site</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ── Hours Tab ── -->
                <div v-show="activeTab === 'hours'" class="space-y-4">
                    <div class="rounded-xl bg-white/5 border border-white/10 p-6 space-y-3">
                        <h2 class="text-sm font-semibold text-white">Opening Hours</h2>
                        <p class="text-xs text-slate-400">These hours appear in Google search results via JSON-LD schema and on your site's contact section.</p>
                        <div v-for="entry in (form.opening_hours as any[])" :key="entry.day"
                             class="flex items-center gap-3">
                            <span class="w-24 text-sm text-slate-300 shrink-0">{{ entry.day }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input v-model="entry.closed" type="checkbox" class="h-3.5 w-3.5 accent-red-500" />
                                <span class="text-xs text-slate-400">Closed</span>
                            </label>
                            <template v-if="!entry.closed">
                                <input v-model="entry.open" type="time"
                                       class="rounded-lg bg-white/10 border border-white/20 px-2 py-1.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                <span class="text-slate-500 text-xs">to</span>
                                <input v-model="entry.close" type="time"
                                       class="rounded-lg bg-white/10 border border-white/20 px-2 py-1.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </template>
                            <span v-else class="text-xs text-slate-500 italic">Closed this day</span>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex items-center justify-between pt-2">
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-400 font-medium">✅ Profile saved successfully!</p>
                    <p v-if="form.hasErrors" class="text-sm text-red-400">Please fix the errors above.</p>
                    <div class="ml-auto">
                        <button type="submit" :disabled="form.processing"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 disabled:opacity-60 rounded-xl text-sm font-semibold text-white transition-all duration-200 shadow-lg shadow-blue-500/25">
                            {{ form.processing ? 'Saving...' : 'Save Business Profile' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
