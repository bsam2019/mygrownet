<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, PlusIcon, TrashIcon, PhotoIcon,
    DocumentArrowDownIcon, EnvelopeIcon, ChatBubbleLeftIcon,
    XMarkIcon, CheckCircleIcon, PencilIcon, BookmarkIcon, DocumentTextIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Currency { code: string; symbol: string; name: string; }
interface LineItem { id: string; description: string; quantity: number; unit: string; unit_price: number; }
interface SavedProfile { id: number; name: string; address: string | null; phone: string | null; email: string | null; logo: string | null; signature: string | null; tax_number: string | null; }

const props = defineProps<{
    documentType: string;
    currencies: Currency[];
    savedProfile?: SavedProfile;
    initialTemplate?: string;
}>();

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const documentTypeLabels: Record<string, string> = { invoice: 'Invoice', delivery_note: 'Delivery Note', quotation: 'Quotation', receipt: 'Receipt' };

const isLoading = ref(false);
const showShareModal = ref(false);
const showSetupWizard = ref(false);
const showEditProfile = ref(false);
const shareData = ref<any>(null);
const hasSavedProfile = ref(false);
const logoPreview = ref<string | null>(null);
const signaturePreview = ref<string | null>(null);
const errors = ref<Record<string, string>>({});
const successMessage = ref('');

const selectedTemplate = ref(props.initialTemplate || 'classic');
const primaryColor = ref('#2563eb');
const businessName = ref('');
const businessAddress = ref('');
const businessPhone = ref('');
const businessEmail = ref('');
const businessLogo = ref<string | null>(null);
const businessTaxNumber = ref('');
const signature = ref<string | null>(null);
const clientName = ref('');
const clientAddress = ref('');
const clientPhone = ref('');
const clientEmail = ref('');
const documentNumber = ref('');
const issueDate = ref(new Date().toISOString().split('T')[0]);
const dueDate = ref('');
const currency = ref('ZMW');
const taxRate = ref(0);
const discountRate = ref(0);
const notes = ref('');
const terms = ref('');
const items = ref<LineItem[]>([{ id: crypto.randomUUID(), description: '', quantity: 1, unit: '', unit_price: 0 }]);

onMounted(() => { 
    if (props.savedProfile) {
        loadSavedProfile(props.savedProfile);
        hasSavedProfile.value = true;
    } else if (isAuthenticated.value) {
        // Show setup wizard for logged-in users without a saved profile
        showSetupWizard.value = true;
    }
});

const loadSavedProfile = (profile: SavedProfile) => {
    businessName.value = profile.name || '';
    businessAddress.value = profile.address || '';
    businessPhone.value = profile.phone || '';
    businessEmail.value = profile.email || '';
    businessTaxNumber.value = profile.tax_number || '';
    if (profile.logo) { businessLogo.value = profile.logo; logoPreview.value = profile.logo; }
    if (profile.signature) { signature.value = profile.signature; signaturePreview.value = profile.signature; }
};

const openEditProfile = () => {
    showEditProfile.value = true;
};

const currencySymbol = computed(() => props.currencies.find(c => c.code === currency.value)?.symbol || 'K');
const subtotal = computed(() => items.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0));
const discountAmount = computed(() => subtotal.value * (discountRate.value / 100));
const taxableAmount = computed(() => subtotal.value - discountAmount.value);
const taxAmount = computed(() => taxableAmount.value * (taxRate.value / 100));
const total = computed(() => taxableAmount.value + taxAmount.value);
const formatCurrency = (amount: number) => `${currencySymbol.value} ${amount.toFixed(2)}`;

const addItem = () => { items.value.push({ id: crypto.randomUUID(), description: '', quantity: 1, unit: '', unit_price: 0 }); };
const removeItem = (id: string) => { if (items.value.length > 1) items.value = items.value.filter(item => item.id !== id); };

const handleLogoUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = (e) => { logoPreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
    const formData = new FormData();
    formData.append('logo', file);
    try {
        const response = await axios.post(route('quick-invoice.upload-logo'), formData);
        businessLogo.value = response.data.url;
    } catch (error: any) { errors.value.logo = error.response?.data?.message || 'Failed to upload logo'; }
};
const removeLogo = () => { logoPreview.value = null; businessLogo.value = null; };

const handleSignatureUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = (e) => { signaturePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
    const formData = new FormData();
    formData.append('signature', file);
    try {
        const response = await axios.post(route('quick-invoice.upload-signature'), formData);
        signature.value = response.data.url;
    } catch (error: any) { errors.value.signature = error.response?.data?.message || 'Failed to upload signature'; }
};
const removeSignature = () => { signaturePreview.value = null; signature.value = null; };

const validateForm = (): boolean => {
    errors.value = {};
    if (!businessName.value.trim()) errors.value.business_name = 'Business name is required';
    if (!clientName.value.trim()) errors.value.client_name = 'Client name is required';
    if (items.value.filter(item => item.description.trim()).length === 0) errors.value.items = 'At least one item is required';
    return Object.keys(errors.value).length === 0;
};

const generateDocument = async () => {
    if (!validateForm()) { document.querySelector('.text-red-600')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
    isLoading.value = true; errors.value = {}; successMessage.value = '';
    try {
        const response = await axios.post(route('quick-invoice.generate'), {
            document_type: props.documentType, document_number: documentNumber.value || undefined,
            business_name: businessName.value, business_address: businessAddress.value || undefined,
            business_phone: businessPhone.value || undefined, business_email: businessEmail.value || undefined,
            business_logo: businessLogo.value || undefined, business_tax_number: businessTaxNumber.value || undefined,
            client_name: clientName.value, client_address: clientAddress.value || undefined,
            client_phone: clientPhone.value || undefined, client_email: clientEmail.value || undefined,
            issue_date: issueDate.value, due_date: dueDate.value || undefined, currency: currency.value,
            tax_rate: taxRate.value, discount_rate: discountRate.value, notes: notes.value || undefined,
            terms: terms.value || undefined, template: selectedTemplate.value, colors: { primary: primaryColor.value },
            signature: signature.value || undefined, save_document: true,
            items: items.value.filter(item => item.description.trim()).map(item => ({
                description: item.description, quantity: item.quantity, unit: item.unit || undefined, unit_price: item.unit_price
            })),
        });
        shareData.value = response.data.share; showShareModal.value = true; successMessage.value = 'Document generated!';
    } catch (error: any) {
        if (error.response?.data?.errors) {
            for (const [key, messages] of Object.entries(error.response.data.errors)) {
                errors.value[key] = Array.isArray(messages) ? messages[0] : messages as string;
            }
        } else { errors.value.general = error.response?.data?.message || 'Failed to generate document.'; }
    } finally { isLoading.value = false; }
};

const downloadPdf = () => { if (shareData.value?.pdf_url) window.open(shareData.value.pdf_url, '_blank'); };
const shareWhatsApp = () => { if (shareData.value?.whatsapp_link) window.open(shareData.value.whatsapp_link, '_blank'); };

const sendEmail = async () => {
    if (!clientEmail.value) { errors.value.client_email = 'Client email is required'; return; }
    isLoading.value = true;
    try {
        await axios.post(route('quick-invoice.send-email'), {
            document_type: props.documentType, business_name: businessName.value, business_address: businessAddress.value,
            business_phone: businessPhone.value, business_email: businessEmail.value, business_logo: businessLogo.value,
            client_name: clientName.value, client_address: clientAddress.value, client_phone: clientPhone.value,
            client_email: clientEmail.value, issue_date: issueDate.value, due_date: dueDate.value, currency: currency.value,
            tax_rate: taxRate.value, discount_rate: discountRate.value, notes: notes.value, terms: terms.value,
            template: selectedTemplate.value, colors: { primary: primaryColor.value }, signature: signature.value,
            items: items.value.filter(item => item.description.trim())
        });
        alert('Email sent successfully!');
    } catch (error: any) { alert(error.response?.data?.message || 'Failed to send email'); }
    finally { isLoading.value = false; }
};

const saveBusinessProfile = async () => {
    if (!isAuthenticated.value) { alert('Please log in to save your business profile'); return; }
    try {
        await axios.post(route('quick-invoice.save-profile'), {
            name: businessName.value, address: businessAddress.value, phone: businessPhone.value,
            email: businessEmail.value, logo: businessLogo.value, signature: signature.value, tax_number: businessTaxNumber.value,
        });
        successMessage.value = 'Business profile saved!'; setTimeout(() => successMessage.value = '', 3000);
        showSetupWizard.value = false;
        showEditProfile.value = false;
        hasSavedProfile.value = true;
    } catch (error: any) { errors.value.general = 'Failed to save profile'; }
};

const skipSetupWizard = () => {
    showSetupWizard.value = false;
};
</script>

<template>
    <Head :title="`Create ${documentTypeLabels[documentType]}`" />
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link :href="route('quick-invoice.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Go back">
                            <ArrowLeftIcon class="w-5 h-5 text-gray-600" aria-hidden="true" />
                        </Link>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Create {{ documentTypeLabels[documentType] }}</h1>
                            <p class="text-sm text-gray-500">Fill in the details below</p>
                        </div>
                    </div>
                    <button @click="generateDocument" :disabled="isLoading" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50">
                        {{ isLoading ? 'Generating...' : `Generate ${documentTypeLabels[documentType]}` }}
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="successMessage" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-700 flex items-center gap-2"><CheckCircleIcon class="w-5 h-5" aria-hidden="true" />{{ successMessage }}</p>
            </div>
            <div v-if="errors.general" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-600">{{ errors.general }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Template & Color -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Style & Template</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
                                <select v-model="selectedTemplate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="classic">Classic - Traditional</option>
                                    <option value="modern">Modern - Contemporary</option>
                                    <option value="minimal">Minimal - Clean</option>
                                    <option value="professional">Professional - Corporate</option>
                                    <option value="bold">Bold - Eye-catching</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Brand Color</label>
                                <div class="flex items-center gap-3">
                                    <input v-model="primaryColor" type="color" class="w-12 h-10 rounded border border-gray-300 cursor-pointer" />
                                    <input v-model="primaryColor" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Your Business</h2>
                            <div class="flex items-center gap-2">
                                <button v-if="isAuthenticated && hasSavedProfile" @click="openEditProfile" class="text-sm text-gray-600 hover:text-gray-700 flex items-center gap-1">
                                    <PencilIcon class="w-4 h-4" aria-hidden="true" />Edit Profile
                                </button>
                                <button v-if="isAuthenticated" @click="saveBusinessProfile" class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                    <BookmarkIcon class="w-4 h-4" aria-hidden="true" />Save Profile
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                                <div class="flex items-center gap-3">
                                    <div v-if="logoPreview" class="relative">
                                        <img :src="logoPreview" alt="Logo" class="h-14 w-auto object-contain border rounded" />
                                        <button @click="removeLogo" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full" aria-label="Remove logo"><XMarkIcon class="w-3 h-3" aria-hidden="true" /></button>
                                    </div>
                                    <label v-else class="flex items-center gap-2 px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 text-sm">
                                        <PhotoIcon class="w-5 h-5 text-gray-400" aria-hidden="true" /><span class="text-gray-600">Upload</span>
                                        <input type="file" accept="image/*" @change="handleLogoUpload" class="hidden" />
                                    </label>
                                </div>
                                <p v-if="errors.logo" class="mt-1 text-sm text-red-600">{{ errors.logo }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature</label>
                                <div class="flex items-center gap-3">
                                    <div v-if="signaturePreview" class="relative">
                                        <img :src="signaturePreview" alt="Signature" class="h-14 w-auto object-contain border rounded" />
                                        <button @click="removeSignature" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full" aria-label="Remove signature"><XMarkIcon class="w-3 h-3" aria-hidden="true" /></button>
                                    </div>
                                    <label v-else class="flex items-center gap-2 px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 text-sm">
                                        <PencilIcon class="w-5 h-5 text-gray-400" aria-hidden="true" /><span class="text-gray-600">Upload</span>
                                        <input type="file" accept="image/*" @change="handleSignatureUpload" class="hidden" />
                                    </label>
                                </div>
                                <p v-if="errors.signature" class="mt-1 text-sm text-red-600">{{ errors.signature }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                                <input v-model="businessName" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" :class="errors.business_name ? 'border-red-500' : 'border-gray-300'" placeholder="Your Company Name" />
                                <p v-if="errors.business_name" class="mt-1 text-sm text-red-600">{{ errors.business_name }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea v-model="businessAddress" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Street, City, Country"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input v-model="businessPhone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="+260 97X XXX XXX" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="businessEmail" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="info@company.com" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tax Number (TPIN)</label>
                                <input v-model="businessTaxNumber" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="1234567890" />
                            </div>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Client Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client Name *</label>
                                <input v-model="clientName" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" :class="errors.client_name ? 'border-red-500' : 'border-gray-300'" placeholder="Client or Company Name" />
                                <p v-if="errors.client_name" class="mt-1 text-sm text-red-600">{{ errors.client_name }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea v-model="clientAddress" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Client address"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input v-model="clientPhone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="+260 97X XXX XXX" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="clientEmail" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" :class="errors.client_email ? 'border-red-500' : 'border-gray-300'" placeholder="client@email.com" />
                                <p v-if="errors.client_email" class="mt-1 text-sm text-red-600">{{ errors.client_email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Document Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Document Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ documentTypeLabels[documentType] }} #</label>
                                <input v-model="documentNumber" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Auto-generated" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date</label>
                                <input v-model="issueDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                <input v-model="dueDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select v-model="currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option v-for="c in currencies" :key="c.code" :value="c.code">{{ c.symbol }} - {{ c.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Items</h2>
                            <button @click="addItem" type="button" class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                <PlusIcon class="w-4 h-4" aria-hidden="true" />Add Item
                            </button>
                        </div>
                        <p v-if="errors.items" class="mb-3 text-sm text-red-600">{{ errors.items }}</p>
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-sm text-gray-500 border-b">
                                        <th class="pb-2 font-medium">Description</th>
                                        <th class="pb-2 font-medium w-20">Qty</th>
                                        <th class="pb-2 font-medium w-24">Unit</th>
                                        <th class="pb-2 font-medium w-28">Price</th>
                                        <th class="pb-2 font-medium w-28 text-right">Amount</th>
                                        <th class="pb-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in items" :key="item.id">
                                        <td class="py-2 pr-2"><input v-model="item.description" type="text" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" placeholder="Item description" /></td>
                                        <td class="py-2 pr-2"><input v-model.number="item.quantity" type="number" min="1" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" /></td>
                                        <td class="py-2 pr-2"><input v-model="item.unit" type="text" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" placeholder="pcs" /></td>
                                        <td class="py-2 pr-2"><input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" /></td>
                                        <td class="py-2 pr-2 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.quantity * item.unit_price) }}</td>
                                        <td class="py-2"><button @click="removeItem(item.id)" :disabled="items.length === 1" class="p-1 text-gray-400 hover:text-red-500 disabled:opacity-30" aria-label="Remove item"><TrashIcon class="w-4 h-4" aria-hidden="true" /></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="md:hidden space-y-4">
                            <div v-for="(item, index) in items" :key="item.id" class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-sm font-medium text-gray-500">Item {{ index + 1 }}</span>
                                    <button @click="removeItem(item.id)" :disabled="items.length === 1" class="p-1 text-gray-400 hover:text-red-500 disabled:opacity-30" aria-label="Remove item"><TrashIcon class="w-4 h-4" aria-hidden="true" /></button>
                                </div>
                                <div class="space-y-3">
                                    <input v-model="item.description" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Description" />
                                    <div class="grid grid-cols-3 gap-2">
                                        <div><label class="text-xs text-gray-500">Qty</label><input v-model.number="item.quantity" type="number" min="1" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" /></div>
                                        <div><label class="text-xs text-gray-500">Unit</label><input v-model="item.unit" type="text" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="pcs" /></div>
                                        <div><label class="text-xs text-gray-500">Price</label><input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" /></div>
                                    </div>
                                    <div class="text-right text-sm font-medium text-gray-900">Amount: {{ formatCurrency(item.quantity * item.unit_price) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tax, Discount & Notes -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Discount (%)</label>
                                <input v-model.number="discountRate" type="number" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                                <input v-model.number="taxRate" type="number" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea v-model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Additional notes for the client"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                                <textarea v-model="terms" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Payment terms, delivery conditions, etc."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Summary</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="font-medium">{{ formatCurrency(subtotal) }}</span></div>
                            <div v-if="discountRate > 0" class="flex justify-between text-green-600"><span>Discount ({{ discountRate }}%)</span><span>-{{ formatCurrency(discountAmount) }}</span></div>
                            <div v-if="taxRate > 0" class="flex justify-between"><span class="text-gray-600">Tax ({{ taxRate }}%)</span><span class="font-medium">{{ formatCurrency(taxAmount) }}</span></div>
                            <div class="border-t pt-3 flex justify-between text-lg font-bold"><span>Total</span><span class="text-blue-600">{{ formatCurrency(total) }}</span></div>
                        </div>
                        <div class="mt-6">
                            <button @click="generateDocument" :disabled="isLoading" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2">
                                <DocumentArrowDownIcon class="w-5 h-5" aria-hidden="true" />{{ isLoading ? 'Generating...' : 'Generate Document' }}
                            </button>
                        </div>
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Quick Tips</h3>
                            <ul class="text-xs text-gray-500 space-y-1">
                                <li>• Business name and client name are required</li>
                                <li>• Add at least one item with description</li>
                                <li>• Upload your logo for professional look</li>
                                <li>• Save your profile to reuse details</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Share Modal -->
        <div v-if="showShareModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showShareModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <button @click="showShareModal = false" class="absolute top-4 right-4 p-2 hover:bg-gray-100 rounded-full" aria-label="Close modal"><XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" /></button>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"><CheckCircleIcon class="w-8 h-8 text-green-600" aria-hidden="true" /></div>
                        <h3 class="text-xl font-bold text-gray-900">Document Ready!</h3>
                        <p class="text-gray-500 mt-1">Your {{ documentTypeLabels[documentType] }} has been generated</p>
                    </div>
                    <div class="space-y-3">
                        <button @click="downloadPdf" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 flex items-center justify-center gap-2"><DocumentArrowDownIcon class="w-5 h-5" aria-hidden="true" />Download PDF</button>
                        <button @click="shareWhatsApp" class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 flex items-center justify-center gap-2"><ChatBubbleLeftIcon class="w-5 h-5" aria-hidden="true" />Share via WhatsApp</button>
                        <button @click="sendEmail" :disabled="!clientEmail || isLoading" class="w-full py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 disabled:opacity-50 flex items-center justify-center gap-2"><EnvelopeIcon class="w-5 h-5" aria-hidden="true" />{{ clientEmail ? 'Send via Email' : 'Add client email to send' }}</button>
                    </div>
                    <div class="mt-6 text-center"><Link :href="route('quick-invoice.history')" class="text-sm text-blue-600 hover:text-blue-700">View Document History →</Link></div>
                </div>
            </div>
        </div>

        <!-- Setup Wizard Modal -->
        <div v-if="showSetupWizard" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black/50"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <DocumentTextIcon class="w-8 h-8 text-blue-600" aria-hidden="true" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Set Up Your Business Profile</h3>
                        <p class="text-gray-500 mt-1">Save your details once, use them every time</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Logo Upload in Wizard -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Logo</label>
                            <div class="flex items-center gap-3">
                                <div v-if="logoPreview" class="relative">
                                    <img :src="logoPreview" alt="Logo" class="h-16 w-auto object-contain border rounded" />
                                    <button @click="removeLogo" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full" aria-label="Remove logo">
                                        <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                    </button>
                                </div>
                                <label v-else class="flex items-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400">
                                    <PhotoIcon class="w-6 h-6 text-gray-400" aria-hidden="true" />
                                    <span class="text-gray-600">Upload Logo</span>
                                    <input type="file" accept="image/*" @change="handleLogoUpload" class="hidden" />
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                            <input v-model="businessName" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Your Company Name" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea v-model="businessAddress" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Street, City, Country"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input v-model="businessPhone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="+260 97X XXX XXX" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="businessEmail" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="info@company.com" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tax Number (TPIN)</label>
                            <input v-model="businessTaxNumber" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Optional" />
                        </div>

                        <!-- Signature Upload in Wizard -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Signature (Optional)</label>
                            <div class="flex items-center gap-3">
                                <div v-if="signaturePreview" class="relative">
                                    <img :src="signaturePreview" alt="Signature" class="h-16 w-auto object-contain border rounded" />
                                    <button @click="removeSignature" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full" aria-label="Remove signature">
                                        <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                    </button>
                                </div>
                                <label v-else class="flex items-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400">
                                    <PencilIcon class="w-6 h-6 text-gray-400" aria-hidden="true" />
                                    <span class="text-gray-600">Upload Signature</span>
                                    <input type="file" accept="image/*" @change="handleSignatureUpload" class="hidden" />
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Add your signature to appear on documents</p>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button @click="skipSetupWizard" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                            Skip for Now
                        </button>
                        <button @click="saveBusinessProfile" :disabled="!businessName.trim()" class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <BookmarkIcon class="w-5 h-5" aria-hidden="true" />
                            Save Profile
                        </button>
                    </div>

                    <p class="mt-4 text-xs text-gray-400 text-center">
                        Your profile will be saved and auto-filled for future documents
                    </p>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div v-if="showEditProfile" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black/50" @click="showEditProfile = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                    <button @click="showEditProfile = false" class="absolute top-4 right-4 p-2 hover:bg-gray-100 rounded-full" aria-label="Close modal">
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                    
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <PencilIcon class="w-8 h-8 text-blue-600" aria-hidden="true" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Business Profile</h3>
                        <p class="text-gray-500 mt-1">Update your saved business details</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Logo</label>
                            <div class="flex items-center gap-3">
                                <div v-if="logoPreview" class="relative">
                                    <img :src="logoPreview" alt="Logo" class="h-16 w-auto object-contain border rounded" />
                                    <button @click="removeLogo" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full" aria-label="Remove logo">
                                        <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                    </button>
                                </div>
                                <label v-else class="flex items-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400">
                                    <PhotoIcon class="w-6 h-6 text-gray-400" aria-hidden="true" />
                                    <span class="text-gray-600">Upload Logo</span>
                                    <input type="file" accept="image/*" @change="handleLogoUpload" class="hidden" />
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                            <input v-model="businessName" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Your Company Name" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea v-model="businessAddress" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Street, City, Country"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input v-model="businessPhone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="+260 97X XXX XXX" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="businessEmail" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="info@company.com" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tax Number (TPIN)</label>
                            <input v-model="businessTaxNumber" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Optional" />
                        </div>

                        <!-- Signature Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Signature (Optional)</label>
                            <div class="flex items-center gap-3">
                                <div v-if="signaturePreview" class="relative">
                                    <img :src="signaturePreview" alt="Signature" class="h-16 w-auto object-contain border rounded" />
                                    <button @click="removeSignature" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full" aria-label="Remove signature">
                                        <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                    </button>
                                </div>
                                <label v-else class="flex items-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400">
                                    <PencilIcon class="w-6 h-6 text-gray-400" aria-hidden="true" />
                                    <span class="text-gray-600">Upload Signature</span>
                                    <input type="file" accept="image/*" @change="handleSignatureUpload" class="hidden" />
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Add your signature to appear on documents</p>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button @click="showEditProfile = false" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="saveBusinessProfile" :disabled="!businessName.trim()" class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <BookmarkIcon class="w-5 h-5" aria-hidden="true" />
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
