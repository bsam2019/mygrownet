<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BuildingOfficeIcon, ArrowLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

interface Props {
    businessProfile: any;
}

const props = defineProps<Props>();

const form = useForm({
    business_name: props.businessProfile?.businessName || '',
    address: props.businessProfile?.address || '',
    phone: props.businessProfile?.phone || '',
    email: props.businessProfile?.email || '',
    tpin: props.businessProfile?.tpin || '',
    website: props.businessProfile?.website || '',
    bank_name: props.businessProfile?.bankName || '',
    bank_account: props.businessProfile?.bankAccount || '',
    bank_branch: props.businessProfile?.bankBranch || '',
    default_currency: props.businessProfile?.defaultCurrency || 'ZMW',
    default_tax_rate: props.businessProfile?.defaultTaxRate || 16.00,
    default_terms: props.businessProfile?.defaultTerms || '',
    default_notes: props.businessProfile?.defaultNotes || '',
    default_payment_instructions: props.businessProfile?.defaultPaymentInstructions || '',
    prepared_by: props.businessProfile?.preparedBy || '',
    logo: null as File | null,
    signature: null as File | null,
});

const logoPreview = ref<string | null>(props.businessProfile?.logoUrl || null);
const signaturePreview = ref<string | null>(props.businessProfile?.signatureUrl || null);

const handleLogoChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleSignatureChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        form.signature = file;
        signaturePreview.value = URL.createObjectURL(file);
    }
};

// Auto-format phone number with +260 prefix for Zambian numbers
import { watch } from 'vue';
const formatPhoneNumber = (value: string): string => {
    if (!value) return '';
    
    // Remove all non-digit characters except +
    const cleaned = value.replace(/[^\d+]/g, '');
    
    // If starts with +260, keep as is
    if (cleaned.startsWith('+260')) {
        return cleaned;
    }
    
    // Remove any existing + at the start
    const digits = cleaned.replace(/^\+/, '');
    
    // If starts with 260, add +
    if (digits.startsWith('260')) {
        return '+' + digits;
    }
    
    // If starts with 0 (local format), replace with +260
    if (digits.startsWith('0')) {
        return '+260' + digits.substring(1);
    }
    
    // If starts with 9 or 7 (mobile without 0), add +260
    if (digits.match(/^[97]/)) {
        return '+260' + digits;
    }
    
    // Otherwise, add +260 prefix if there are digits
    return digits ? '+260' + digits : '';
};

watch(() => form.phone, (newValue) => {
    if (newValue && !newValue.startsWith('+260')) {
        const formatted = formatPhoneNumber(newValue);
        if (formatted !== newValue) {
            form.phone = formatted;
        }
    }
});

const submit = () => {
    form.post('/bizdocs/profile', {
        forceFormData: true,
        preserveScroll: true,
        _method: 'PUT',
    });
};
</script>

<template>
    <Head title="Edit Business Profile" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 flex items-start justify-center py-10 px-4">
            <div class="w-full max-w-5xl">

                <!-- Top eyebrow label -->
                <div class="flex items-center gap-2 mb-6">
                    <Link
                        href="/bizdocs/dashboard"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-blue-600 transition-colors group">
                        <ArrowLeftIcon class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" />
                        Back to Dashboard
                    </Link>
                    <span class="text-slate-300">·</span>
                    <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                        <span class="w-5 h-px bg-blue-500 inline-block"></span>
                        Edit Profile
                    </span>
                </div>

                <!-- Main card -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col lg:flex-row">

                    <!-- Left panel -->
                    <aside class="lg:w-64 xl:w-72 bg-slate-900 flex-shrink-0 flex flex-col justify-between p-8">
                        <div>
                            <!-- Brand -->
                            <div class="flex items-center gap-3 mb-10">
                                <div class="w-9 h-9 bg-blue-500 rounded-xl flex items-center justify-center shadow">
                                    <BuildingOfficeIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white leading-none">BizDocs</p>
                                    <p class="text-xs text-slate-500 mt-0.5">MyGrowNet</p>
                                </div>
                            </div>

                            <!-- Heading -->
                            <h1 class="text-2xl font-bold text-white leading-snug mb-2">
                                Edit Business<br />Profile
                            </h1>
                            <p class="text-sm text-slate-400 leading-relaxed mb-10">
                                Update your business information. Changes will reflect on all future documents.
                            </p>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed mt-10">
                            Your existing documents will not be affected by these changes.
                        </p>
                    </aside>

                    <!-- Right panel (form) -->
                    <div class="flex-1 p-8 lg:p-10">
                        <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

                            <!-- Section 01: Business Information -->
                            <section>
                                <div class="flex items-center gap-3 mb-5">
                                    <span class="text-xs font-bold tracking-widest text-blue-500 uppercase tabular-nums">01</span>
                                    <span class="flex-1 h-px bg-slate-100"></span>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Business Information</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                    <div class="sm:col-span-2">
                                        <label for="business_name" class="bizdocs-label">
                                            Business Name <span class="text-red-400">*</span>
                                        </label>
                                        <input
                                            id="business_name"
                                            v-model="form.business_name"
                                            type="text"
                                            required
                                            placeholder="e.g. Acme Trading Ltd"
                                            class="bizdocs-input"
                                            :class="{ 'bizdocs-input-error': form.errors.business_name }" />
                                        <p v-if="form.errors.business_name" class="bizdocs-error">
                                            {{ form.errors.business_name }}
                                        </p>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="address" class="bizdocs-label">
                                            Business Address <span class="text-red-400">*</span>
                                        </label>
                                        <textarea
                                            id="address"
                                            v-model="form.address"
                                            rows="2"
                                            required
                                            placeholder="Street, City, Province"
                                            class="bizdocs-input resize-none"
                                            :class="{ 'bizdocs-input-error': form.errors.address }">
                                        </textarea>
                                        <p v-if="form.errors.address" class="bizdocs-error">
                                            {{ form.errors.address }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="phone" class="bizdocs-label">
                                            Phone <span class="text-red-400">*</span>
                                        </label>
                                        <input
                                            id="phone"
                                            v-model="form.phone"
                                            type="tel"
                                            required
                                            placeholder="+260 97X XXX XXX"
                                            class="bizdocs-input"
                                            :class="{ 'bizdocs-input-error': form.errors.phone }" />
                                        <p v-if="form.errors.phone" class="bizdocs-error">
                                            {{ form.errors.phone }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="email" class="bizdocs-label">Email</label>
                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            placeholder="hello@yourbusiness.com"
                                            class="bizdocs-input" />
                                    </div>

                                    <div>
                                        <label for="tpin" class="bizdocs-label">
                                            TPIN
                                            <span class="ml-1 text-xs font-normal text-slate-400">(Tax ID)</span>
                                        </label>
                                        <input
                                            id="tpin"
                                            v-model="form.tpin"
                                            type="text"
                                            placeholder="1234567890"
                                            class="bizdocs-input" />
                                    </div>

                                    <div>
                                        <label for="website" class="bizdocs-label">Website</label>
                                        <input
                                            id="website"
                                            v-model="form.website"
                                            type="text"
                                            placeholder="yourbusiness.com"
                                            class="bizdocs-input" />
                                        <p class="text-xs text-slate-400 mt-1">https:// will be added automatically</p>
                                    </div>

                                    <!-- Logo Upload -->
                                    <div class="sm:col-span-2">
                                        <label class="bizdocs-label">Business Logo</label>
                                        <div class="flex items-start gap-4">
                                            <div v-if="logoPreview" class="flex-shrink-0">
                                                <img :src="logoPreview" alt="Logo preview" class="w-32 h-32 object-contain border border-slate-200 rounded-lg p-2 bg-white">
                                            </div>
                                            <div class="flex-1">
                                                <label for="logo" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                                    <PhotoIcon class="w-4 h-4" />
                                                    {{ logoPreview ? 'Change Logo' : 'Upload Logo' }}
                                                </label>
                                                <input
                                                    id="logo"
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleLogoChange"
                                                    class="hidden" />
                                                <p class="text-xs text-slate-400 mt-2">PNG, JPG up to 2MB. Recommended: 300x100px</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Signature Upload -->
                                    <div class="sm:col-span-2">
                                        <label class="bizdocs-label">Authorized Signature</label>
                                        <div class="flex items-start gap-4">
                                            <div v-if="signaturePreview" class="flex-shrink-0">
                                                <img :src="signaturePreview" alt="Signature preview" class="w-32 h-20 object-contain border border-slate-200 rounded-lg p-2 bg-white">
                                            </div>
                                            <div class="flex-1">
                                                <label for="signature" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                                    <PhotoIcon class="w-4 h-4" />
                                                    {{ signaturePreview ? 'Change Signature' : 'Upload Signature' }}
                                                </label>
                                                <input
                                                    id="signature"
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleSignatureChange"
                                                    class="hidden" />
                                                <p class="text-xs text-slate-400 mt-2">PNG, JPG up to 1MB. Recommended: 200x80px</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Prepared By -->
                                    <div class="sm:col-span-2">
                                        <label for="prepared_by" class="bizdocs-label">Prepared By</label>
                                        <input
                                            id="prepared_by"
                                            v-model="form.prepared_by"
                                            type="text"
                                            placeholder="e.g. John Doe, Accountant"
                                            class="bizdocs-input" />
                                        <p class="text-xs text-slate-400 mt-1">Name of person who prepares documents</p>
                                    </div>

                                </div>
                            </section>

                            <!-- Section 02: Bank Details -->
                            <section>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="text-xs font-bold tracking-widest text-slate-400 uppercase tabular-nums">02</span>
                                    <span class="flex-1 h-px bg-slate-100"></span>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Bank Details</span>
                                </div>
                                <p class="text-xs text-slate-400 mb-4 mt-2">
                                    Optional — displayed as payment instructions on invoices.
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                    <div>
                                        <label for="bank_name" class="bizdocs-label">Bank Name</label>
                                        <input
                                            id="bank_name"
                                            v-model="form.bank_name"
                                            type="text"
                                            placeholder="e.g. Zanaco, FNB, Standard"
                                            class="bizdocs-input" />
                                    </div>

                                    <div>
                                        <label for="bank_account" class="bizdocs-label">Account Number</label>
                                        <input
                                            id="bank_account"
                                            v-model="form.bank_account"
                                            type="text"
                                            placeholder="000000000000"
                                            class="bizdocs-input" />
                                    </div>

                                    <div>
                                        <label for="bank_branch" class="bizdocs-label">Branch</label>
                                        <input
                                            id="bank_branch"
                                            v-model="form.bank_branch"
                                            type="text"
                                            placeholder="Branch name or code"
                                            class="bizdocs-input" />
                                    </div>

                                    <div>
                                        <label for="default_currency" class="bizdocs-label">Default Currency</label>
                                        <select
                                            id="default_currency"
                                            v-model="form.default_currency"
                                            class="bizdocs-input bg-white">
                                            <option value="ZMW">🇿🇲 ZMW — Zambian Kwacha</option>
                                            <option value="USD">🇺🇸 USD — US Dollar</option>
                                            <option value="EUR">🇪🇺 EUR — Euro</option>
                                            <option value="GBP">🇬🇧 GBP — British Pound</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="default_tax_rate" class="bizdocs-label">
                                            Default Tax Rate (%)
                                            <span class="ml-1 text-xs font-normal text-slate-400">(VAT/Sales Tax)</span>
                                        </label>
                                        <input
                                            id="default_tax_rate"
                                            v-model="form.default_tax_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            placeholder="16.00"
                                            class="bizdocs-input" />
                                        <p class="text-xs text-slate-400 mt-1">Applied to all line items by default</p>
                                    </div>

                                </div>
                            </section>

                            <!-- Section 03: Default Document Content -->
                            <section>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="text-xs font-bold tracking-widest text-slate-400 uppercase tabular-nums">03</span>
                                    <span class="flex-1 h-px bg-slate-100"></span>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Default Document Content</span>
                                </div>
                                <p class="text-xs text-slate-400 mb-4 mt-2">
                                    Optional — pre-fill terms, notes, and payment instructions for all documents.
                                </p>

                                <div class="space-y-4">

                                    <div>
                                        <label for="default_terms" class="bizdocs-label">Default Terms & Conditions</label>
                                        <textarea
                                            id="default_terms"
                                            v-model="form.default_terms"
                                            rows="3"
                                            placeholder="e.g. Payment due within 30 days. Late payments subject to 2% monthly interest."
                                            class="bizdocs-input resize-none" />
                                    </div>

                                    <div>
                                        <label for="default_notes" class="bizdocs-label">Default Notes</label>
                                        <textarea
                                            id="default_notes"
                                            v-model="form.default_notes"
                                            rows="2"
                                            placeholder="e.g. Thank you for your business!"
                                            class="bizdocs-input resize-none" />
                                    </div>

                                    <div>
                                        <label for="default_payment_instructions" class="bizdocs-label">Default Payment Instructions</label>
                                        <textarea
                                            id="default_payment_instructions"
                                            v-model="form.default_payment_instructions"
                                            rows="2"
                                            placeholder="e.g. Bank transfer to account above or mobile money to +260..."
                                            class="bizdocs-input resize-none" />
                                    </div>

                                </div>
                            </section>

                            <!-- Submit row -->
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <Link
                                    href="/bizdocs/dashboard"
                                    class="text-xs font-medium text-slate-500 hover:text-slate-700 transition-colors">
                                    Cancel
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="group inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">

                                    <span v-if="!form.processing">Save Changes</span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                        </svg>
                                        Saving...
                                    </span>

                                    <svg v-if="!form.processing"
                                        class="w-4 h-4"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.bizdocs-label {
    @apply block text-xs font-semibold text-slate-600 mb-1.5 tracking-wide;
}

.bizdocs-input {
    @apply block w-full px-3.5 py-2.5 text-sm text-slate-800 bg-slate-50
           border border-slate-200 rounded-lg placeholder-slate-300
           focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10
           transition-all duration-150 outline-none;
}

.bizdocs-input-error {
    @apply border-red-300 bg-red-50 focus:border-red-400 focus:ring-red-500/10;
}

.bizdocs-error {
    @apply mt-1.5 text-xs text-red-500;
}
</style>
