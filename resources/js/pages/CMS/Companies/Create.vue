<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { route } from 'ziggy-js';
import axios from 'axios';
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline';
import PhoneInput from '@/Components/PhoneInput.vue';
import CountrySelect from '@/Components/CountrySelect.vue';

interface Preset {
    id: number;
    code: string;
    name: string;
    description: string;
    icon: string;
}

const props = defineProps<{ presets: Preset[] }>();

const STORAGE_KEY = 'cms_company_wizard';

const form = useForm({
    name:          '',
    industry_type: '',
    preset_code:   '',
    phone:         '',
    phoneCountryCode: '+260',
    email:         '',
    address:       '',
    city:          'Lusaka',
    country:       'Zambia',
});

const step = ref<1 | 2>(1);

// ── Persist wizard state to localStorage ─────────────────────────────────────
function saveToStorage() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
        name:          form.name,
        industry_type: form.industry_type,
        preset_code:   form.preset_code,
        phone:         form.phone,
        phoneCountryCode: form.phoneCountryCode,
        email:         form.email,
        address:       form.address,
        city:          form.city,
        country:       form.country,
        step:          step.value,
    }));
}

function loadFromStorage() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (!saved) return;
    try {
        const data = JSON.parse(saved);
        form.name          = data.name          ?? '';
        form.industry_type = data.industry_type ?? '';
        form.preset_code   = data.preset_code   ?? '';
        form.phone         = data.phone         ?? '';
        form.phoneCountryCode = data.phoneCountryCode ?? '+260';
        form.email         = data.email         ?? '';
        form.address       = data.address       ?? '';
        form.city          = data.city          ?? 'Lusaka';
        form.country       = data.country       ?? 'Zambia';
        step.value         = data.step          ?? 1;
    } catch {}
}

function clearStorage() {
    localStorage.removeItem(STORAGE_KEY);
}

// Watch all form fields and persist on change
watch(() => [form.name, form.phone, form.phoneCountryCode, form.email, form.address, form.city, form.country, form.preset_code, step.value], saveToStorage, { deep: true });

onMounted(loadFromStorage);

// ── CSRF refresh helper ───────────────────────────────────────────────────────
async function refreshCsrf() {
    await axios.get('/sanctum/csrf-cookie').catch(() => {});
}

// ── Step navigation ───────────────────────────────────────────────────────────
function nextStep() {
    if (!form.name.trim()) {
        form.setError('name', 'Company name is required.');
        return;
    }
    form.clearErrors('name');
    step.value = 2;
    // Refresh CSRF token when user reaches step 2 so it's fresh for submission
    refreshCsrf();
}

function selectPreset(code: string) {
    form.preset_code   = form.preset_code === code ? '' : code;
    form.industry_type = form.preset_code ? code : '';
}

// ── Submit ────────────────────────────────────────────────────────────────────
async function submit() {
    // Refresh CSRF token right before submitting to prevent 419
    await refreshCsrf();

    // Combine country code with phone number
    const fullPhone = form.phone ? `${form.phoneCountryCode} ${form.phone}` : '';

    form.transform((data) => ({
        ...data,
        phone: fullPhone,
    })).post(route('cms.companies.store'), {
        onSuccess: () => clearStorage(),
        onError: (errors) => {
            // If 419 somehow still happens, reload the page to get a fresh token
            if (Object.keys(errors).includes('_token')) {
                window.location.reload();
            }
        },
    });
}

// ── Icon map ──────────────────────────────────────────────────────────────────
const iconMap: Record<string, string> = {
    scissors:           '✂',
    printer:            '🖨',
    building:           '🏗',
    'shopping-bag':     '🛍',
    briefcase:          '💼',
    wrench:             '🔧',
    utensils:           '🍽',
    'building-office':  '🏢',
    truck:              '🚛',
    heart:              '🏥',
    academic:           '🎓',
    camera:             '📸',
    computer:           '💻',
    scissors2:          '💇',
    home:               '🏠',
    leaf:               '🌿',
    bolt:               '⚡',
    phone:              '📱',
    music:              '🎵',
    shield:             '🛡',
    chart:              '📊',
    hammer:             '🔨',
    fish:               '🐟',
    tractor:            '🚜',
    factory:            '🏭',
};
</script>

<template>
    <Head title="Create Company - CMS" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-2xl">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center mb-4">
                    <img src="/logo.png" alt="MyGrowNet" class="h-12 object-contain" />
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Create Your Company</h1>
                <p class="mt-2 text-gray-500">Set up your business in under a minute.</p>
            </div>

            <!-- Step indicator -->
            <div class="flex items-center justify-center gap-3 mb-8">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                        :class="step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'">1</div>
                    <span class="text-sm font-medium" :class="step >= 1 ? 'text-blue-600' : 'text-gray-400'">Company Details</span>
                </div>
                <div class="w-8 h-0.5" :class="step >= 2 ? 'bg-blue-600' : 'bg-gray-200'"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                        :class="step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'">2</div>
                    <span class="text-sm font-medium" :class="step >= 2 ? 'text-blue-600' : 'text-gray-400'">Industry</span>
                </div>
            </div>

            <!-- Saved state notice -->
            <div v-if="step === 1 && form.name" class="mb-4 flex items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                <CheckIcon class="h-4 w-4 flex-shrink-0" aria-hidden="true" />
                Progress saved — you can safely close this tab and come back.
            </div>

            <form @submit.prevent="submit">
                <!-- Step 1: Company Details -->
                <div v-if="step === 1" class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Company Name <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Geopamu Aluminium & Construction"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            :class="{ 'border-red-300': form.errors.name }"
                            autofocus
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Phone</label>
                        <PhoneInput
                            v-model="form.phone"
                            v-model:country-code="form.phoneCountryCode"
                            placeholder="97X XXX XXX"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Business Email</label>
                        <input v-model="form.email" type="email" placeholder="info@company.com"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Address</label>
                        <input v-model="form.address" type="text" placeholder="Plot 123, Cairo Road"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">City</label>
                            <input v-model="form.city" type="text" placeholder="Lusaka"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Country</label>
                            <CountrySelect v-model="form.country" />
                        </div>
                    </div>

                    <button type="button" @click="nextStep"
                        class="w-full bg-blue-600 text-white py-3.5 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg">
                        Next: Choose Industry →
                    </button>
                </div>

                <!-- Step 2: Industry Preset -->
                <div v-if="step === 2" class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                    <div class="flex items-center gap-2 mb-2">
                        <button type="button" @click="step = 1" class="text-gray-400 hover:text-gray-600 transition">
                            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                        <h2 class="text-base font-semibold text-gray-900">What type of business is <span class="text-blue-600">{{ form.name }}</span>?</h2>
                    </div>
                    <p class="text-sm text-gray-500 mb-5 ml-7">This pre-configures roles, expense categories, and settings. You can change it later.</p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-6 max-h-96 overflow-y-auto pr-1">
                        <button
                            v-for="preset in presets"
                            :key="preset.code"
                            type="button"
                            @click="selectPreset(preset.code)"
                            class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 text-center transition"
                            :class="form.preset_code === preset.code
                                ? 'border-blue-500 bg-blue-50'
                                : 'border-gray-200 hover:border-gray-300 bg-white'"
                        >
                            <span class="text-2xl">{{ iconMap[preset.icon] ?? '🏢' }}</span>
                            <span class="text-xs font-semibold text-gray-800 leading-tight">{{ preset.name }}</span>
                            <CheckIcon v-if="form.preset_code === preset.code" class="h-4 w-4 text-blue-600" aria-hidden="true" />
                        </button>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="form.preset_code = ''; submit()"
                            class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition text-sm">
                            Skip for now
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 transition shadow-lg text-sm">
                            {{ form.processing ? 'Creating…' : '🚀 Create Company' }}
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-5">
                <Link :href="route('cms.companies.hub')" class="text-sm text-gray-500 hover:text-gray-700 transition">
                    ← Back to My Companies
                </Link>
            </div>
        </div>
    </div>
</template>
