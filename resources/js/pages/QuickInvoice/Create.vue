<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, PlusIcon, TrashIcon, PhotoIcon,
    DocumentArrowDownIcon, EnvelopeIcon, ChatBubbleLeftIcon,
    XMarkIcon, CheckCircleIcon, PencilIcon, BookmarkIcon, DocumentTextIcon
} from '@heroicons/vue/24/outline';
// Use global axios with CSRF token configured
const axios = (window as any).axios || require('axios').default;

interface Currency { code: string; symbol: string; name: string; }
interface LineItem { 
    id: string; 
    description: string; 
    quantity: number; 
    unit: string; 
    unit_price: number;
    // Area calculation fields for constructors
    useAreaCalc: boolean;
    length: number;
    width: number;
}
interface SavedProfile { 
    id: number; 
    name: string; 
    address: string | null; 
    phone: string | null; 
    email: string | null; 
    logo: string | null; 
    signature: string | null; 
    tax_number: string | null;
    default_tax_rate: number | null;
    default_discount_rate: number | null;
    default_notes: string | null;
    default_terms: string | null;
}

interface EditDocument {
    id: string;
    type: string;
    document_number: string;
    business_info: {
        name: string;
        address?: string;
        phone?: string;
        email?: string;
        logo?: string;
        tax_number?: string;
    };
    client_info: {
        name: string;
        address?: string;
        phone?: string;
        email?: string;
    };
    issue_date: string;
    due_date?: string;
    currency: string;
    items: Array<{
        id?: string;
        description: string;
        quantity: number;
        unit?: string;
        unit_price: number;
    }>;
    tax_rate: number;
    discount_rate: number;
    notes?: string;
    terms?: string;
    template: string;
    colors: { primary: string };
    signature?: string;
    prepared_by?: string;
}

// Template preview data
const templatePreviews: Record<string, { name: string; description: string; style: string }> = {
    classic: { name: 'Classic', description: 'Traditional business style with clean lines', style: 'border-blue-600' },
    modern: { name: 'Modern', description: 'Contemporary design with gradient header', style: 'border-indigo-600' },
    minimal: { name: 'Minimal', description: 'Clean and simple, content-focused', style: 'border-gray-600' },
    professional: { name: 'Professional', description: 'Corporate look with sidebar accent', style: 'border-slate-600' },
    bold: { name: 'Bold', description: 'Eye-catching with strong colors', style: 'border-orange-600' },
};

const props = defineProps<{
    documentType: string;
    currencies: Currency[];
    savedProfile?: SavedProfile;
    initialTemplate?: string;
    editDocument?: EditDocument | null;
}>();

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const isEditing = computed(() => !!props.editDocument);
const documentTypeLabels: Record<string, string> = { invoice: 'Invoice', delivery_note: 'Delivery Note', quotation: 'Quotation', receipt: 'Receipt' };

const isLoading = ref(false);
const isSavingProfile = ref(false);
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
const preparedByName = ref('');
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
const items = ref<LineItem[]>([{ id: crypto.randomUUID(), description: '', quantity: 1, unit: '', unit_price: 0, useAreaCalc: false, length: 0, width: 0 }]);

onMounted(() => { 
    if (props.editDocument) {
        loadEditDocument(props.editDocument);
    } else if (props.savedProfile) {
        loadSavedProfile(props.savedProfile);
        hasSavedProfile.value = true;
    } else if (isAuthenticated.value) {
        // Show setup wizard for logged-in users without a saved profile
        showSetupWizard.value = true;
    }
});

const loadEditDocument = (doc: EditDocument) => {
    // Load business info
    businessName.value = doc.business_info.name || '';
    businessAddress.value = doc.business_info.address || '';
    businessPhone.value = doc.business_info.phone || '';
    businessEmail.value = doc.business_info.email || '';
    businessTaxNumber.value = doc.business_info.tax_number || '';
    if (doc.business_info.logo) { 
        businessLogo.value = doc.business_info.logo; 
        logoPreview.value = doc.business_info.logo; 
    }
    
    // Load client info
    clientName.value = doc.client_info.name || '';
    clientAddress.value = doc.client_info.address || '';
    clientPhone.value = doc.client_info.phone || '';
    clientEmail.value = doc.client_info.email || '';
    
    // Load document details
    documentNumber.value = doc.document_number || '';
    issueDate.value = doc.issue_date || new Date().toISOString().split('T')[0];
    dueDate.value = doc.due_date || '';
    currency.value = doc.currency || 'ZMW';
    taxRate.value = doc.tax_rate || 0;
    discountRate.value = doc.discount_rate || 0;
    notes.value = doc.notes || '';
    terms.value = doc.terms || '';
    
    // Load template & styling
    selectedTemplate.value = doc.template || 'classic';
    if (doc.colors?.primary) {
        primaryColor.value = doc.colors.primary;
    }
    if (doc.signature) {
        signature.value = doc.signature;
        signaturePreview.value = doc.signature;
    }
    preparedByName.value = doc.prepared_by || '';
    
    // Load items
    if (doc.items && doc.items.length > 0) {
        items.value = doc.items.map(item => ({
            id: item.id || crypto.randomUUID(),
            description: item.description || '',
            quantity: item.quantity || 1,
            unit: item.unit || '',
            unit_price: item.unit_price || 0,
            useAreaCalc: false,
            length: 0,
            width: 0,
        }));
    }
};

const loadSavedProfile = (profile: SavedProfile) => {
    businessName.value = profile.name || '';
    businessAddress.value = profile.address || '';
    businessPhone.value = profile.phone || '';
    businessEmail.value = profile.email || '';
    businessTaxNumber.value = profile.tax_number || '';
    if (profile.logo) { businessLogo.value = profile.logo; logoPreview.value = profile.logo; }
    if (profile.signature) { signature.value = profile.signature; signaturePreview.value = profile.signature; }
    // Load default settings
    if (profile.default_tax_rate) { taxRate.value = profile.default_tax_rate; }
    if (profile.default_discount_rate) { discountRate.value = profile.default_discount_rate; }
    if (profile.default_notes) { notes.value = profile.default_notes; }
    if (profile.default_terms) { terms.value = profile.default_terms; }
};

const openEditProfile = () => {
    showEditProfile.value = true;
};

const currencySymbol = computed(() => props.currencies.find(c => c.code === currency.value)?.symbol || 'K');
const subtotal = computed(() => items.value.reduce((sum, item) => sum + getItemAmount(item), 0));
const discountAmount = computed(() => subtotal.value * (discountRate.value / 100));
const taxableAmount = computed(() => subtotal.value - discountAmount.value);
const taxAmount = computed(() => taxableAmount.value * (taxRate.value / 100));
const total = computed(() => taxableAmount.value + taxAmount.value);
const formatCurrency = (amount: number) => `${currencySymbol.value} ${amount.toFixed(2)}`;

const addItem = () => { items.value.push({ id: crypto.randomUUID(), description: '', quantity: 1, unit: '', unit_price: 0, useAreaCalc: false, length: 0, width: 0 }); };
const removeItem = (id: string) => { if (items.value.length > 1) items.value = items.value.filter(item => item.id !== id); };

// Calculate effective quantity (handles area calculation for constructors)
const getEffectiveQuantity = (item: LineItem): number => {
    if (item.useAreaCalc && item.length > 0 && item.width > 0) {
        return item.length * item.width;
    }
    return item.quantity;
};

// Calculate item amount
const getItemAmount = (item: LineItem): number => {
    return getEffectiveQuantity(item) * item.unit_price;
};

// Toggle area calculation mode for an item
const toggleAreaCalc = (item: LineItem) => {
    item.useAreaCalc = !item.useAreaCalc;
    if (item.useAreaCalc) {
        item.unit = 'm²'; // Default to square meters
        item.length = item.length || 1;
        item.width = item.width || 1;
    }
};

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
        const response = await axios.post(route('quick-invoice.upload-logo'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        businessLogo.value = response.data.url;
        console.log('Logo uploaded successfully:', response.data.url);
    } catch (error: any) { 
        console.error('Logo upload failed:', error.response?.data || error.message);
        errors.value.logo = error.response?.data?.message || 'Failed to upload logo'; 
    }
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
        const response = await axios.post(route('quick-invoice.upload-signature'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        signature.value = response.data.url;
        console.log('Signature uploaded successfully:', response.data.url);
    } catch (error: any) { 
        console.error('Signature upload failed:', error.response?.data || error.message);
        errors.value.signature = error.response?.data?.message || 'Failed to upload signature'; 
    }
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
        const payload: Record<string, any> = {
            document_type: props.documentType, document_number: documentNumber.value || undefined,
            business_name: businessName.value, business_address: businessAddress.value || undefined,
            business_phone: businessPhone.value || undefined, business_email: businessEmail.value || undefined,
            business_logo: businessLogo.value || undefined, business_tax_number: businessTaxNumber.value || undefined,
            client_name: clientName.value, client_address: clientAddress.value || undefined,
            client_phone: clientPhone.value || undefined, client_email: clientEmail.value || undefined,
            issue_date: issueDate.value, due_date: dueDate.value || undefined, currency: currency.value,
            tax_rate: taxRate.value, discount_rate: discountRate.value, notes: notes.value || undefined,
            terms: terms.value || undefined, template: selectedTemplate.value, colors: { primary: primaryColor.value },
            signature: signature.value || undefined, prepared_by: preparedByName.value || undefined, save_document: true,
            items: items.value.filter(item => item.description.trim()).map(item => ({
                description: item.description, 
                quantity: getEffectiveQuantity(item), 
                unit: item.unit || undefined, 
                unit_price: item.unit_price,
                // Include area calc details for display purposes
                area_calc: item.useAreaCalc ? { length: item.length, width: item.width } : undefined
            })),
        };
        
        // If editing, include the document ID to update instead of create
        if (isEditing.value && props.editDocument) {
            payload.document_id = props.editDocument.id;
        }
        
        const response = await axios.post(route('quick-invoice.generate'), payload);
        shareData.value = response.data.share; showShareModal.value = true; successMessage.value = isEditing.value ? 'Document updated!' : 'Document generated!';
    } catch (error: any) {
        if (error.response?.data?.errors) {
            for (const [key, messages] of Object.entries(error.response.data.errors)) {
                errors.value[key] = Array.isArray(messages) ? messages[0] : messages as string;
            }
        } else { errors.value.general = error.response?.data?.message || 'Failed to generate document.'; }
    } finally { isLoading.value = false; }
};

const downloadPdf = () => { 
    if (shareData.value?.pdf_url) {
        // Open in new window - this works better for server-generated PDFs
        window.open(shareData.value.pdf_url, '_blank');
    }
};

const shareWhatsApp = async () => { 
    if (!shareData.value?.document_id) return;
    
    // For WhatsApp, we need to get a temporary URL since WhatsApp needs a public link
    if (shareData.value?.can_whatsapp) {
        try {
            const response = await axios.get(route('quick-invoice.whatsapp', shareData.value.document_id));
            if (response.data.whatsapp_link) {
                window.open(response.data.whatsapp_link, '_blank');
            }
        } catch (error) {
            // Fallback: open WhatsApp with just the message (no PDF link)
            const phone = clientPhone.value?.replace(/[^0-9]/g, '') || '';
            const message = encodeURIComponent(`Hi ${clientName.value}, please find your ${props.documentType} #${shareData.value.document_number}. Total: ${shareData.value.total}`);
            window.open(`https://wa.me/${phone}?text=${message}`, '_blank');
        }
    }
};

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
    isSavingProfile.value = true;
    errors.value = {};
    
    // Ensure UI updates before making request
    await nextTick();
    
    try {
        const response = await axios.post(route('quick-invoice.save-profile'), {
            name: businessName.value, address: businessAddress.value, phone: businessPhone.value,
            email: businessEmail.value, logo: businessLogo.value, signature: signature.value, tax_number: businessTaxNumber.value,
            default_tax_rate: taxRate.value, default_discount_rate: discountRate.value,
            default_notes: notes.value, default_terms: terms.value,
        });
        if (response.data.success) {
            successMessage.value = 'Business profile saved!'; 
            setTimeout(() => successMessage.value = '', 3000);
            showSetupWizard.value = false;
            showEditProfile.value = false;
            hasSavedProfile.value = true;
        } else {
            errors.value.general = response.data.message || 'Failed to save profile';
        }
    } catch (error: any) { 
        const message = error.response?.data?.message || error.response?.data?.errors?.name?.[0] || 'Failed to save profile';
        errors.value.general = message;
        console.error('Save profile error:', error.response?.data);
    } finally {
        isSavingProfile.value = false;
    }
};

const skipSetupWizard = () => {
    showSetupWizard.value = false;
};
</script>

<template>
    <Head :title="`${isEditing ? 'Edit' : 'Create'} ${documentTypeLabels[documentType]}`" />
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link :href="isEditing ? route('quick-invoice.history') : route('quick-invoice.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Go back">
                            <ArrowLeftIcon class="w-5 h-5 text-gray-600" aria-hidden="true" />
                        </Link>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ isEditing ? 'Edit' : 'Create' }} {{ documentTypeLabels[documentType] }}</h1>
                            <p class="text-sm text-gray-500">{{ isEditing ? 'Update the details below' : 'Fill in the details below' }}</p>
                        </div>
                    </div>
                    <button @click="generateDocument" :disabled="isLoading" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50">
                        {{ isLoading ? 'Generating...' : (isEditing ? `Update ${documentTypeLabels[documentType]}` : `Generate ${documentTypeLabels[documentType]}`) }}
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
                        
                        <!-- Template Selection with Previews -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Choose Template</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                <button
                                    v-for="(preview, key) in templatePreviews"
                                    :key="key"
                                    @click="selectedTemplate = key"
                                    type="button"
                                    class="relative p-3 rounded-lg border-2 transition-all text-left"
                                    :class="selectedTemplate === key ? `${preview.style} bg-gray-50` : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <!-- Mini Preview -->
                                    <div class="aspect-[3/4] mb-2 rounded border bg-white overflow-hidden">
                                        <div v-if="key === 'classic'" class="h-full flex flex-col">
                                            <div class="h-1.5 bg-blue-600"></div>
                                            <div class="flex-1 p-1.5">
                                                <div class="h-1 w-8 bg-gray-300 mb-1"></div>
                                                <div class="h-0.5 w-6 bg-gray-200 mb-2"></div>
                                                <div class="space-y-0.5">
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="key === 'modern'" class="h-full flex flex-col">
                                            <div class="h-4 bg-gradient-to-r from-indigo-600 to-blue-600"></div>
                                            <div class="flex-1 p-1.5">
                                                <div class="h-1 w-6 bg-indigo-200 rounded-full mb-2"></div>
                                                <div class="space-y-0.5">
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="key === 'minimal'" class="h-full flex flex-col p-1.5">
                                            <div class="h-1 w-8 bg-gray-400 mb-1"></div>
                                            <div class="h-0.5 w-6 bg-gray-200 mb-2"></div>
                                            <div class="flex-1 border-t pt-1">
                                                <div class="space-y-0.5">
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="key === 'professional'" class="h-full flex">
                                            <div class="w-1.5 bg-slate-700"></div>
                                            <div class="flex-1 p-1.5">
                                                <div class="h-1 w-8 bg-gray-300 mb-1"></div>
                                                <div class="h-0.5 w-6 bg-gray-200 mb-2"></div>
                                                <div class="space-y-0.5">
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="key === 'bold'" class="h-full flex flex-col">
                                            <div class="h-5 bg-orange-500 flex items-center justify-center">
                                                <div class="h-1 w-4 bg-white/50 rounded"></div>
                                            </div>
                                            <div class="flex-1 p-1.5">
                                                <div class="space-y-0.5">
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                    <div class="h-0.5 bg-gray-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs font-medium text-gray-900">{{ preview.name }}</div>
                                    <div class="text-[10px] text-gray-500 leading-tight hidden sm:block">{{ preview.description }}</div>
                                    <!-- Selected indicator -->
                                    <div v-if="selectedTemplate === key" class="absolute top-1 right-1 w-4 h-4 bg-blue-600 rounded-full flex items-center justify-center">
                                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Brand Color</label>
                            <div class="flex items-center gap-3">
                                <input v-model="primaryColor" type="color" class="w-12 h-10 rounded border border-gray-300 cursor-pointer" />
                                <input v-model="primaryColor" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                <!-- Quick color presets -->
                                <div class="flex gap-1">
                                    <button type="button" @click="primaryColor = '#2563eb'" class="w-6 h-6 rounded bg-blue-600 border-2" :class="primaryColor === '#2563eb' ? 'border-gray-900' : 'border-transparent'" aria-label="Blue"></button>
                                    <button type="button" @click="primaryColor = '#059669'" class="w-6 h-6 rounded bg-emerald-600 border-2" :class="primaryColor === '#059669' ? 'border-gray-900' : 'border-transparent'" aria-label="Green"></button>
                                    <button type="button" @click="primaryColor = '#7c3aed'" class="w-6 h-6 rounded bg-violet-600 border-2" :class="primaryColor === '#7c3aed' ? 'border-gray-900' : 'border-transparent'" aria-label="Purple"></button>
                                    <button type="button" @click="primaryColor = '#dc2626'" class="w-6 h-6 rounded bg-red-600 border-2" :class="primaryColor === '#dc2626' ? 'border-gray-900' : 'border-transparent'" aria-label="Red"></button>
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
                                <button v-if="isAuthenticated" @click="saveBusinessProfile" :disabled="isSavingProfile" class="text-sm text-blue-600 hover:text-blue-700 disabled:opacity-50 flex items-center gap-1">
                                    <BookmarkIcon class="w-4 h-4" aria-hidden="true" />{{ isSavingProfile ? 'Saving...' : 'Save Profile' }}
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prepared By</label>
                                <input v-model="preparedByName" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Your name" />
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
                            <div v-if="documentType === 'invoice'">
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
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Items</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Use area calc (L×W) for construction/flooring work</p>
                            </div>
                            <button @click="addItem" type="button" class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                <PlusIcon class="w-4 h-4" aria-hidden="true" />Add Item
                            </button>
                        </div>
                        <p v-if="errors.items" class="mb-3 text-sm text-red-600">{{ errors.items }}</p>
                        
                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-sm text-gray-500 border-b">
                                        <th class="pb-2 font-medium">Description</th>
                                        <th class="pb-2 font-medium w-36">Quantity / Area</th>
                                        <th class="pb-2 font-medium w-24">Unit</th>
                                        <th class="pb-2 font-medium w-28">Unit Price</th>
                                        <th class="pb-2 font-medium w-28 text-right">Amount</th>
                                        <th class="pb-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in items" :key="item.id">
                                        <td class="py-2 pr-2">
                                            <input v-model="item.description" type="text" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" placeholder="Item description" />
                                        </td>
                                        <td class="py-2 pr-2">
                                            <!-- Toggle between simple qty and area calc -->
                                            <div v-if="!item.useAreaCalc">
                                                <div class="flex items-center gap-1">
                                                    <input v-model.number="item.quantity" type="number" min="0.01" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" />
                                                    <button @click="toggleAreaCalc(item)" type="button" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="Switch to area calculation (L×W)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div v-else class="space-y-1">
                                                <div class="flex items-center gap-1">
                                                    <input v-model.number="item.length" type="number" min="0.01" step="0.01" class="w-14 px-1.5 py-1 border border-gray-300 rounded text-xs focus:ring-2 focus:ring-blue-500" placeholder="L" />
                                                    <span class="text-gray-400 text-xs">×</span>
                                                    <input v-model.number="item.width" type="number" min="0.01" step="0.01" class="w-14 px-1.5 py-1 border border-gray-300 rounded text-xs focus:ring-2 focus:ring-blue-500" placeholder="W" />
                                                    <button @click="toggleAreaCalc(item)" type="button" class="p-1 text-blue-600 hover:text-gray-600 hover:bg-gray-50 rounded" title="Switch to simple quantity">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>
                                                <div class="text-xs text-blue-600 font-medium">= {{ (item.length * item.width).toFixed(2) }} {{ item.unit || 'm²' }}</div>
                                            </div>
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input v-model="item.unit" type="text" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" :placeholder="item.useAreaCalc ? 'm²' : 'pcs, kg, m'" />
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" />
                                        </td>
                                        <td class="py-2 pr-2 text-right text-sm font-medium text-gray-900">{{ formatCurrency(getItemAmount(item)) }}</td>
                                        <td class="py-2">
                                            <button @click="removeItem(item.id)" :disabled="items.length === 1" class="p-1 text-gray-400 hover:text-red-500 disabled:opacity-30" aria-label="Remove item">
                                                <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4">
                            <div v-for="(item, index) in items" :key="item.id" class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-sm font-medium text-gray-500">Item {{ index + 1 }}</span>
                                    <div class="flex items-center gap-2">
                                        <button @click="toggleAreaCalc(item)" type="button" class="text-xs px-2 py-1 rounded" :class="item.useAreaCalc ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'">
                                            {{ item.useAreaCalc ? 'Area (L×W)' : 'Simple Qty' }}
                                        </button>
                                        <button @click="removeItem(item.id)" :disabled="items.length === 1" class="p-1 text-gray-400 hover:text-red-500 disabled:opacity-30" aria-label="Remove item">
                                            <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <input v-model="item.description" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Description" />
                                    
                                    <!-- Quantity or Area inputs -->
                                    <div v-if="!item.useAreaCalc" class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="text-xs text-gray-500">Qty</label>
                                            <input v-model.number="item.quantity" type="number" min="0.01" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Unit</label>
                                            <input v-model="item.unit" type="text" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="pcs" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Price</label>
                                            <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" />
                                        </div>
                                    </div>
                                    <div v-else class="space-y-2">
                                        <div class="grid grid-cols-4 gap-2">
                                            <div>
                                                <label class="text-xs text-gray-500">Length</label>
                                                <input v-model.number="item.length" type="number" min="0.01" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" />
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-500">Width</label>
                                                <input v-model.number="item.width" type="number" min="0.01" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" />
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-500">Unit</label>
                                                <input v-model="item.unit" type="text" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="m²" />
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-500">Price/{{ item.unit || 'm²' }}</label>
                                                <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" />
                                            </div>
                                        </div>
                                        <div class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                            Area: {{ item.length }} × {{ item.width }} = <span class="font-semibold">{{ (item.length * item.width).toFixed(2) }} {{ item.unit || 'm²' }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right text-sm font-medium text-gray-900">Amount: {{ formatCurrency(getItemAmount(item)) }}</div>
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
                                <li>• Use area calc (L×W) for flooring/construction</li>
                                <li>• Unit field shows measurement type (pcs, m², kg)</li>
                                <li>• Upload your logo for professional look</li>
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

                        <!-- Default Settings Section -->
                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Default Invoice Settings</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Tax Rate (%)</label>
                                    <input v-model.number="taxRate" type="number" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="16" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Discount (%)</label>
                                    <input v-model.number="discountRate" type="number" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="0" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Notes</label>
                                <textarea v-model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Thank you for your business!"></textarea>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Terms & Conditions</label>
                                <textarea v-model="terms" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Payment due within 30 days"></textarea>
                            </div>
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
                        <button @click="saveBusinessProfile" :disabled="!businessName.trim() || isSavingProfile" class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <BookmarkIcon class="w-5 h-5" aria-hidden="true" />
                            {{ isSavingProfile ? 'Saving...' : 'Save Profile' }}
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

                        <!-- Default Settings Section -->
                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Default Invoice Settings</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Tax Rate (%)</label>
                                    <input v-model.number="taxRate" type="number" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="16" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Discount (%)</label>
                                    <input v-model.number="discountRate" type="number" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="0" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Notes</label>
                                <textarea v-model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Thank you for your business!"></textarea>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Terms & Conditions</label>
                                <textarea v-model="terms" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Payment due within 30 days"></textarea>
                            </div>
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
                        <button @click="showEditProfile = false" :disabled="isSavingProfile" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 disabled:opacity-50">
                            Cancel
                        </button>
                        <button @click="saveBusinessProfile" :disabled="!businessName.trim() || isSavingProfile" class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <BookmarkIcon class="w-5 h-5" aria-hidden="true" />
                            {{ isSavingProfile ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
