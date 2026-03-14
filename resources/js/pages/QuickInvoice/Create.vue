<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import QuickInvoiceLayout from '@/Layouts/QuickInvoiceLayout.vue';
import { 
    ArrowLeftIcon, PlusIcon, TrashIcon, PhotoIcon,
    DocumentArrowDownIcon, EnvelopeIcon, ChatBubbleLeftIcon,
    XMarkIcon, CheckCircleIcon, PencilIcon, BookmarkIcon, DocumentTextIcon
} from '@heroicons/vue/24/outline';
import { useAutoSave } from '@/composables/useAutoSave';
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
    prepared_by: string | null;
    tax_number: string | null;
    default_tax_rate: number | null;
    default_discount_rate: number | null;
    default_notes: string | null;
    default_terms: string | null;
    // Numbering settings
    invoice_prefix: string | null;
    invoice_next_number: number | null;
    invoice_number_padding: number | null;
    quotation_prefix: string | null;
    quotation_next_number: number | null;
    quotation_number_padding: number | null;
    receipt_prefix: string | null;
    receipt_next_number: number | null;
    receipt_number_padding: number | null;
    delivery_note_prefix: string | null;
    delivery_note_next_number: number | null;
    delivery_note_number_padding: number | null;
    // Template preferences
    default_template: string | null;
    default_color: string | null;
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
const activeProfileTab = ref<'business' | 'defaults' | 'numbering' | 'library'>('business');
const shareData = ref<any>(null);
const hasSavedProfile = ref(false);
const logoPreview = ref<string | null>(null);
const signaturePreview = ref<string | null>(null);
const errors = ref<Record<string, string>>({});
const successMessage = ref('');
const showRestorePrompt = ref(false);
const lastSaveTime = ref<Date | null>(null);

const selectedTemplate = ref(props.initialTemplate || 'classic');
const primaryColor = ref('#2563eb');

// Numbering settings
const invoicePrefix = ref('INV');
const invoiceNextNumber = ref(1);
const invoiceNumberPadding = ref(4);
const quotationPrefix = ref('QUO');
const quotationNextNumber = ref(1);
const quotationNumberPadding = ref(4);
const receiptPrefix = ref('REC');
const receiptNextNumber = ref(1);
const receiptNumberPadding = ref(4);
const deliveryNotePrefix = ref('DN');
const deliveryNoteNextNumber = ref(1);
const deliveryNoteNumberPadding = ref(4);

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

// Attachments
const attachments = ref<File[]>([]);
const attachmentInput = ref<HTMLInputElement | null>(null);
const libraryUploadInput = ref<HTMLInputElement | null>(null);
const attachmentLibrary = ref<any[]>([]);
const selectedLibraryAttachments = ref<Set<number>>(new Set());
const libraryAttachmentIds = ref<number[]>([]); // Track which attachments are from library
const showAttachmentLibrary = ref(false);
const isLoadingLibrary = ref(false);
const isSavingToLibrary = ref(false);
const showSaveToLibraryModal = ref(false);
const attachmentToSave = ref<{ file: File; index: number } | null>(null);
const saveToLibraryName = ref('');
const saveToLibraryDescription = ref('');

// Auto-save setup
const autoSave = useAutoSave({
    key: `invoice_${props.documentType}_${isEditing.value ? props.editDocument?.id : 'new'}`,
    data: () => ({
        selectedTemplate: selectedTemplate.value,
        primaryColor: primaryColor.value,
        businessName: businessName.value,
        businessAddress: businessAddress.value,
        businessPhone: businessPhone.value,
        businessEmail: businessEmail.value,
        businessTaxNumber: businessTaxNumber.value,
        preparedByName: preparedByName.value,
        clientName: clientName.value,
        clientAddress: clientAddress.value,
        clientPhone: clientPhone.value,
        clientEmail: clientEmail.value,
        documentNumber: documentNumber.value,
        issueDate: issueDate.value,
        dueDate: dueDate.value,
        currency: currency.value,
        taxRate: taxRate.value,
        discountRate: discountRate.value,
        notes: notes.value,
        terms: terms.value,
        items: items.value,
    }),
    onRestore: (savedData) => {
        // Only show restore prompt if not editing and not loading from saved profile
        if (!isEditing.value && !props.savedProfile) {
            showRestorePrompt.value = true;
            lastSaveTime.value = autoSave.getLastSaveTime();
        }
    },
    debounceMs: 3000, // Save every 3 seconds after changes stop
    exclude: ['businessLogo', 'signature'] // Don't auto-save uploaded files
});

const restoreSavedData = () => {
    const savedData = autoSave.restoreData();
    if (savedData) {
        selectedTemplate.value = savedData.selectedTemplate || selectedTemplate.value;
        primaryColor.value = savedData.primaryColor || primaryColor.value;
        businessName.value = savedData.businessName || '';
        businessAddress.value = savedData.businessAddress || '';
        businessPhone.value = savedData.businessPhone || '';
        businessEmail.value = savedData.businessEmail || '';
        businessTaxNumber.value = savedData.businessTaxNumber || '';
        preparedByName.value = savedData.preparedByName || '';
        clientName.value = savedData.clientName || '';
        clientAddress.value = savedData.clientAddress || '';
        clientPhone.value = savedData.clientPhone || '';
        clientEmail.value = savedData.clientEmail || '';
        documentNumber.value = savedData.documentNumber || '';
        issueDate.value = savedData.issueDate || issueDate.value;
        dueDate.value = savedData.dueDate || '';
        currency.value = savedData.currency || currency.value;
        taxRate.value = savedData.taxRate || 0;
        discountRate.value = savedData.discountRate || 0;
        notes.value = savedData.notes || '';
        terms.value = savedData.terms || '';
        if (savedData.items && savedData.items.length > 0) {
            items.value = savedData.items;
        }
        showRestorePrompt.value = false;
        successMessage.value = 'Draft restored successfully!';
        setTimeout(() => successMessage.value = '', 3000);
    }
};

const discardSavedData = () => {
    autoSave.clearSavedData();
    showRestorePrompt.value = false;
};

onMounted(() => { 
    // IMPORTANT: Set template from props FIRST, before loading profile
    // This ensures URL parameters override profile defaults
    if (props.initialTemplate) {
        selectedTemplate.value = props.initialTemplate;
        console.log('Template set from URL parameter:', props.initialTemplate);
    }
    
    if (props.editDocument) {
        loadEditDocument(props.editDocument);
    } else if (props.savedProfile) {
        loadSavedProfile(props.savedProfile);
        hasSavedProfile.value = true;
    } else if (isAuthenticated.value) {
        // Show setup wizard for logged-in users without a saved profile
        showSetupWizard.value = true;
    }
    
    // Setup auto-save watcher
    autoSave.setupAutoSave();
    
    // Load attachment library for authenticated users
    if (isAuthenticated.value) {
        loadAttachmentLibrary();
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
    if (profile.prepared_by) { preparedByName.value = profile.prepared_by; }
    // Load default settings
    if (profile.default_tax_rate) { taxRate.value = profile.default_tax_rate; }
    if (profile.default_discount_rate) { discountRate.value = profile.default_discount_rate; }
    if (profile.default_notes) { notes.value = profile.default_notes; }
    if (profile.default_terms) { terms.value = profile.default_terms; }
    // Load template and color preferences ONLY if not already set from URL/props
    // selectedTemplate is set in onMounted BEFORE this function is called
    if (profile.default_template && selectedTemplate.value === 'classic') { 
        // Only apply if still at default value
        selectedTemplate.value = profile.default_template; 
        console.log('Template set from profile:', profile.default_template);
    }
    if (profile.default_color && selectedTemplate.value === profile.default_template) { 
        // Only apply color if using profile's template
        primaryColor.value = profile.default_color; 
    }
    // Load numbering settings
    if (profile.invoice_prefix) { invoicePrefix.value = profile.invoice_prefix; }
    if (profile.invoice_next_number) { invoiceNextNumber.value = profile.invoice_next_number; }
    if (profile.invoice_number_padding) { invoiceNumberPadding.value = profile.invoice_number_padding; }
    if (profile.quotation_prefix) { quotationPrefix.value = profile.quotation_prefix; }
    if (profile.quotation_next_number) { quotationNextNumber.value = profile.quotation_next_number; }
    if (profile.quotation_number_padding) { quotationNumberPadding.value = profile.quotation_number_padding; }
    if (profile.receipt_prefix) { receiptPrefix.value = profile.receipt_prefix; }
    if (profile.receipt_next_number) { receiptNextNumber.value = profile.receipt_next_number; }
    if (profile.receipt_number_padding) { receiptNumberPadding.value = profile.receipt_number_padding; }
    if (profile.delivery_note_prefix) { deliveryNotePrefix.value = profile.delivery_note_prefix; }
    if (profile.delivery_note_next_number) { deliveryNoteNextNumber.value = profile.delivery_note_next_number; }
    if (profile.delivery_note_number_padding) { deliveryNoteNumberPadding.value = profile.delivery_note_number_padding; }
};

const openEditProfile = () => {
    showEditProfile.value = true;
    activeProfileTab.value = 'business'; // Reset to business tab
    // Load library when opening profile modal
    if (isAuthenticated.value) {
        loadAttachmentLibrary();
    }
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

const handleAttachmentUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files?.length) return;
    
    const files = Array.from(target.files);
    
    // Validate files
    const validFiles = files.filter(file => {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            errors.value.attachments = `${file.name} is too large (max 5MB)`;
            return false;
        }
        
        // Check file type
        const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            errors.value.attachments = `${file.name} is not a valid file type`;
            return false;
        }
        
        return true;
    });
    
    // Check total count (max 5 files)
    if (attachments.value.length + validFiles.length > 5) {
        errors.value.attachments = 'Maximum 5 attachments allowed';
        return;
    }
    
    attachments.value.push(...validFiles);
    errors.value.attachments = '';
    
    // Clear input
    if (target) target.value = '';
};

const removeAttachment = (index: number) => {
    attachments.value.splice(index, 1);
};

// Attachment Library Functions
const loadAttachmentLibrary = async () => {
    if (!isAuthenticated.value) return;
    
    isLoadingLibrary.value = true;
    try {
        const response = await axios.get(route('quick-invoice.attachment-library'));
        if (response.data.success) {
            attachmentLibrary.value = response.data.attachments;
        }
    } catch (error: any) {
        console.error('Failed to load attachment library:', error);
    } finally {
        isLoadingLibrary.value = false;
    }
};

const toggleLibraryAttachment = (attachmentId: number) => {
    if (selectedLibraryAttachments.value.has(attachmentId)) {
        selectedLibraryAttachments.value.delete(attachmentId);
    } else {
        selectedLibraryAttachments.value.add(attachmentId);
    }
};

const addSelectedLibraryAttachments = async () => {
    if (selectedLibraryAttachments.value.size === 0) return;
    
    // Check if adding these would exceed the limit
    if (attachments.value.length + selectedLibraryAttachments.value.size > 5) {
        errors.value.attachments = 'Maximum 5 attachments allowed';
        return;
    }
    
    // Get selected attachments from library
    const selected = attachmentLibrary.value.filter(att => 
        selectedLibraryAttachments.value.has(att.id)
    );
    
    // Add library attachment IDs to track them
    selected.forEach(att => {
        libraryAttachmentIds.value.push(att.id);
        
        // Create a placeholder File object for display purposes only
        // This won't be uploaded - we'll send the library ID instead
        const blob = new Blob([], { type: att.type });
        const file = new File([blob], att.original_filename, { type: att.type });
        
        // Add a custom property to mark it as from library
        (file as any).isLibraryAttachment = true;
        (file as any).libraryId = att.id;
        (file as any).size = att.size; // Use actual size from library
        
        attachments.value.push(file);
    });
    
    // Clear selection and close library
    selectedLibraryAttachments.value.clear();
    showAttachmentLibrary.value = false;
    errors.value.attachments = '';
};

const openSaveToLibraryModal = (file: File, index: number) => {
    attachmentToSave.value = { file, index };
    saveToLibraryName.value = file.name.replace(/\.[^/.]+$/, ''); // Remove extension
    saveToLibraryDescription.value = '';
    showSaveToLibraryModal.value = true;
};

const saveAttachmentToLibrary = async () => {
    if (!attachmentToSave.value || !isAuthenticated.value) return;
    
    isSavingToLibrary.value = true;
    errors.value.library = '';
    
    try {
        const formData = new FormData();
        formData.append('file', attachmentToSave.value.file);
        formData.append('name', saveToLibraryName.value);
        formData.append('description', saveToLibraryDescription.value);
        
        const response = await axios.post(
            route('quick-invoice.attachment-library.save'),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        
        if (response.data.success) {
            // Add to library list
            attachmentLibrary.value.push(response.data.attachment);
            
            // Close modal
            showSaveToLibraryModal.value = false;
            attachmentToSave.value = null;
            
            successMessage.value = 'Attachment saved to library!';
            setTimeout(() => successMessage.value = '', 3000);
        }
    } catch (error: any) {
        errors.value.library = error.response?.data?.message || 'Failed to save to library';
    } finally {
        isSavingToLibrary.value = false;
    }
};

const deleteFromLibrary = async (attachmentId: number) => {
    if (!confirm('Remove this attachment from your library?')) return;
    
    try {
        const response = await axios.delete(
            route('quick-invoice.attachment-library.delete', attachmentId)
        );
        
        if (response.data.success) {
            attachmentLibrary.value = attachmentLibrary.value.filter(att => att.id !== attachmentId);
            selectedLibraryAttachments.value.delete(attachmentId);
            
            successMessage.value = 'Attachment removed from library';
            setTimeout(() => successMessage.value = '', 3000);
        }
    } catch (error: any) {
        errors.value.library = error.response?.data?.message || 'Failed to delete attachment';
    }
};

const handleLibraryUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files?.length) return;
    
    const file = target.files[0];
    
    // Validate file
    if (file.size > 5 * 1024 * 1024) {
        errors.value.library = `${file.name} is too large (max 5MB)`;
        return;
    }
    
    const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!validTypes.includes(file.type)) {
        errors.value.library = `${file.name} is not a valid file type`;
        return;
    }
    
    // Open save modal with the file
    saveToLibraryName.value = file.name.replace(/\.[^/.]+$/, ''); // Remove extension
    saveToLibraryDescription.value = '';
    
    // Save directly to library
    isSavingToLibrary.value = true;
    errors.value.library = '';
    
    try {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('name', saveToLibraryName.value);
        formData.append('description', saveToLibraryDescription.value);
        
        const response = await axios.post(
            route('quick-invoice.attachment-library.save'),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        
        if (response.data.success) {
            // Add to library list
            attachmentLibrary.value.push(response.data.attachment);
            
            successMessage.value = 'File uploaded to library!';
            setTimeout(() => successMessage.value = '', 3000);
        }
    } catch (error: any) {
        errors.value.library = error.response?.data?.message || 'Failed to upload to library';
    } finally {
        isSavingToLibrary.value = false;
        // Clear input
        if (target) target.value = '';
    }
};

const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

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
        
        // Separate uploaded files from library attachments
        const uploadedFiles = attachments.value.filter(file => !(file as any).isLibraryAttachment);
        const libraryIds = attachments.value
            .filter(file => (file as any).isLibraryAttachment)
            .map(file => (file as any).libraryId);
        
        // If there are attachments, use FormData
        let requestData: any;
        let headers: Record<string, string> = {};
        
        if (uploadedFiles.length > 0 || libraryIds.length > 0) {
            const formData = new FormData();
            formData.append('data', JSON.stringify(payload));
            
            // Add only uploaded files (not library references)
            uploadedFiles.forEach((file, index) => {
                formData.append(`attachments[${index}]`, file);
            });
            
            // Add library attachment IDs
            if (libraryIds.length > 0) {
                formData.append('library_attachments', JSON.stringify(libraryIds));
            }
            
            requestData = formData;
            headers['Content-Type'] = 'multipart/form-data';
        } else {
            requestData = payload;
        }
        
        const response = await axios.post(route('quick-invoice.generate'), requestData, { headers });
        shareData.value = response.data.share; showShareModal.value = true; successMessage.value = isEditing.value ? 'Document updated!' : 'Document generated!';
        
        // Clear auto-saved data and attachments on successful generation
        autoSave.clearSavedData();
        attachments.value = [];
        libraryAttachmentIds.value = [];
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
        // Add timestamp to force fresh download (prevent caching)
        const url = new URL(shareData.value.pdf_url, window.location.origin);
        url.searchParams.set('t', Date.now().toString());
        // Open in new window - this works better for server-generated PDFs
        window.open(url.toString(), '_blank');
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
            email: businessEmail.value, logo: businessLogo.value, signature: signature.value, 
            prepared_by: preparedByName.value, tax_number: businessTaxNumber.value,
            default_tax_rate: taxRate.value, default_discount_rate: discountRate.value,
            default_notes: notes.value, default_terms: terms.value,
            // Numbering settings
            invoice_prefix: invoicePrefix.value,
            invoice_next_number: invoiceNextNumber.value,
            invoice_number_padding: invoiceNumberPadding.value,
            quotation_prefix: quotationPrefix.value,
            quotation_next_number: quotationNextNumber.value,
            quotation_number_padding: quotationNumberPadding.value,
            receipt_prefix: receiptPrefix.value,
            receipt_next_number: receiptNextNumber.value,
            receipt_number_padding: receiptNumberPadding.value,
            delivery_note_prefix: deliveryNotePrefix.value,
            delivery_note_next_number: deliveryNoteNextNumber.value,
            delivery_note_number_padding: deliveryNoteNumberPadding.value,
            // Template preferences
            default_template: selectedTemplate.value,
            default_color: primaryColor.value,
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
    <QuickInvoiceLayout>
        <Head :title="`${isEditing ? 'Edit' : 'Create'} ${documentTypeLabels[documentType]}`" />
        <div class="min-h-screen bg-gray-50">
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Page Title with Auto-save indicator -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit' : 'Create' }} {{ documentTypeLabels[documentType] }}</h1>
                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    Auto-saving
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ isEditing ? 'Update the details below' : 'Fill in the details below' }}</p>
                        </div>
                        <button @click="generateDocument" :disabled="isLoading" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 transition">
                            {{ isLoading ? 'Generating...' : (isEditing ? `Update ${documentTypeLabels[documentType]}` : `Generate ${documentTypeLabels[documentType]}`) }}
                        </button>
                    </div>
                </div>

            <!-- Restore Draft Prompt -->
            <div v-if="showRestorePrompt" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900">Draft Found</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            We found an unsaved draft from {{ lastSaveTime ? new Date(lastSaveTime).toLocaleString() : 'earlier' }}. 
                            Would you like to restore it?
                        </p>
                        <div class="flex gap-3 mt-3">
                            <button @click="restoreSavedData" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                Restore Draft
                            </button>
                            <button @click="discardSavedData" class="px-4 py-2 bg-white text-blue-600 text-sm font-medium rounded-lg border border-blue-300 hover:bg-blue-50">
                                Start Fresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="successMessage" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-700 flex items-center gap-2"><CheckCircleIcon class="w-5 h-5" aria-hidden="true" />{{ successMessage }}</p>
            </div>
            <div v-if="errors.general" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-600">{{ errors.general }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Business Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Your Business</h2>
                            <div class="flex items-center gap-2">
                                <button v-if="isAuthenticated && hasSavedProfile" @click="openEditProfile" class="text-sm text-gray-600 hover:text-gray-700 flex items-center gap-1">
                                    <PencilIcon class="w-4 h-4" aria-hidden="true" />Edit Profile
                                </button>
                                <button v-if="isAuthenticated && !hasSavedProfile" @click="saveBusinessProfile" :disabled="isSavingProfile" class="text-sm text-blue-600 hover:text-blue-700 disabled:opacity-50 flex items-center gap-1">
                                    <BookmarkIcon class="w-4 h-4" aria-hidden="true" />{{ isSavingProfile ? 'Saving...' : 'Save Profile' }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Saved Profile Summary (for authenticated users with profile) -->
                        <div v-if="isAuthenticated && hasSavedProfile" class="space-y-4">
                            <!-- Logo Preview -->
                            <div v-if="logoPreview" class="pb-4 border-b border-gray-200">
                                <img :src="logoPreview" alt="Business Logo" class="h-16 w-auto object-contain" />
                            </div>
                            
                            <!-- Business Summary Card -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">{{ businessName }}</h3>
                                        <p v-if="businessAddress" class="text-sm text-gray-600 mt-1">{{ businessAddress }}</p>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-gray-600">
                                            <span v-if="businessPhone">{{ businessPhone }}</span>
                                            <span v-if="businessEmail">{{ businessEmail }}</span>
                                            <span v-if="businessTaxNumber">TPIN: {{ businessTaxNumber }}</span>
                                        </div>
                                    </div>
                                    <button @click="openEditProfile" class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center gap-1 flex-shrink-0 ml-4">
                                        <PencilIcon class="w-4 h-4" aria-hidden="true" />
                                        Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Full Form (for guests or users without profile) -->
                        <div v-else>
                            <!-- Setup prompt for authenticated users without profile -->
                            <div v-if="isAuthenticated && !hasSavedProfile" class="mb-6 pb-6 border-b border-gray-200">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm text-blue-800">
                                            Fill in your business details below and click "Save Profile" to reuse them for future documents.
                                        </p>
                                    </div>
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

                    <!-- Attachments -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <DocumentTextIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
                            </div>
                            <span class="text-xs text-gray-500">Optional</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Attach supporting documents (specs, photos, etc.) to be included in the final PDF.</p>

                        <!-- Upload Area -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors mb-4">
                            <input
                                type="file"
                                ref="attachmentInput"
                                @change="handleAttachmentUpload"
                                accept=".pdf,.jpg,.jpeg,.png"
                                multiple
                                class="hidden"
                            />
                            <div class="flex items-center justify-center gap-3">
                                <button
                                    type="button"
                                    @click="$refs.attachmentInput?.click()"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"
                                >
                                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                                    Upload New
                                </button>
                                <button
                                    v-if="isAuthenticated && attachmentLibrary.length > 0"
                                    type="button"
                                    @click="showAttachmentLibrary = true"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors"
                                >
                                    <BookmarkIcon class="h-5 w-5" aria-hidden="true" />
                                    From Library ({{ attachmentLibrary.length }})
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                PDF, JPG, PNG (max 5MB each, up to 5 files)
                            </p>
                        </div>

                        <!-- Error Message -->
                        <p v-if="errors.attachments" class="mb-3 text-sm text-red-600">{{ errors.attachments }}</p>

                        <!-- Attachment List -->
                        <div v-if="attachments.length > 0" class="space-y-2">
                            <div
                                v-for="(attachment, index) in attachments"
                                :key="index"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        <DocumentTextIcon v-if="attachment.type === 'application/pdf'" class="h-5 w-5 text-red-500" aria-hidden="true" />
                                        <PhotoIcon v-else class="h-5 w-5 text-blue-500" aria-hidden="true" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.name }}</p>
                                        <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        v-if="isAuthenticated"
                                        type="button"
                                        @click="openSaveToLibraryModal(attachment, index)"
                                        class="flex-shrink-0 p-1.5 text-purple-600 hover:bg-purple-50 rounded transition-colors"
                                        title="Save to library"
                                    >
                                        <BookmarkIcon class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="removeAttachment(index)"
                                        class="flex-shrink-0 p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors"
                                        aria-label="Remove attachment"
                                    >
                                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
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

                    <!-- Tabs -->
                    <div class="flex border-b mb-6">
                        <button
                            @click="activeProfileTab = 'business'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors"
                            :class="activeProfileTab === 'business' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            Business Info
                        </button>
                        <button
                            @click="activeProfileTab = 'defaults'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors"
                            :class="activeProfileTab === 'defaults' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            Default Settings
                        </button>
                        <button
                            @click="activeProfileTab = 'numbering'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors"
                            :class="activeProfileTab === 'numbering' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            Numbering
                        </button>
                        <button
                            @click="activeProfileTab = 'library'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors"
                            :class="activeProfileTab === 'library' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            Library
                        </button>
                    </div>

                    <!-- Business Info Tab -->
                    <div v-show="activeProfileTab === 'business'" class="space-y-4">
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prepared By</label>
                            <input v-model="preparedByName" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Your name" />
                            <p class="mt-1 text-xs text-gray-400">This name will appear on all your documents</p>
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

                    <!-- Default Settings Tab -->
                    <div v-show="activeProfileTab === 'defaults'" class="space-y-4">
                        <div class="text-sm text-gray-600 mb-4">
                            Set default values that will be pre-filled when creating new documents.
                        </div>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Notes</label>
                            <textarea v-model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Thank you for your business!"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Terms & Conditions</label>
                            <textarea v-model="terms" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Payment due within 30 days"></textarea>
                        </div>
                    </div>

                    <!-- Numbering Settings Tab -->
                    <div v-show="activeProfileTab === 'numbering'" class="space-y-4">
                        <div class="text-sm text-gray-600 mb-4">
                            Configure automatic document numbering for each document type. The system will auto-generate sequential numbers.
                        </div>

                        <!-- Invoice Numbering -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Invoice Numbering</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Prefix</label>
                                    <input v-model="invoicePrefix" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="INV" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Next Number</label>
                                    <input v-model.number="invoiceNextNumber" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Padding</label>
                                    <input v-model.number="invoiceNumberPadding" type="number" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Preview: {{ invoicePrefix }}-{{ String(invoiceNextNumber).padStart(invoiceNumberPadding, '0') }}</p>
                        </div>

                        <!-- Quotation Numbering -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Quotation Numbering</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Prefix</label>
                                    <input v-model="quotationPrefix" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="QUO" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Next Number</label>
                                    <input v-model.number="quotationNextNumber" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Padding</label>
                                    <input v-model.number="quotationNumberPadding" type="number" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Preview: {{ quotationPrefix }}-{{ String(quotationNextNumber).padStart(quotationNumberPadding, '0') }}</p>
                        </div>

                        <!-- Receipt Numbering -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Receipt Numbering</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Prefix</label>
                                    <input v-model="receiptPrefix" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="REC" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Next Number</label>
                                    <input v-model.number="receiptNextNumber" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Padding</label>
                                    <input v-model.number="receiptNumberPadding" type="number" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Preview: {{ receiptPrefix }}-{{ String(receiptNextNumber).padStart(receiptNumberPadding, '0') }}</p>
                        </div>

                        <!-- Delivery Note Numbering -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Delivery Note Numbering</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Prefix</label>
                                    <input v-model="deliveryNotePrefix" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="DN" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Next Number</label>
                                    <input v-model.number="deliveryNoteNextNumber" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Padding</label>
                                    <input v-model.number="deliveryNoteNumberPadding" type="number" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Preview: {{ deliveryNotePrefix }}-{{ String(deliveryNoteNextNumber).padStart(deliveryNoteNumberPadding, '0') }}</p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-800">
                                <strong>Note:</strong> The system will automatically increment the "Next Number" each time you generate a document. You can manually adjust it if needed.
                            </p>
                        </div>
                    </div>

                    <!-- Attachment Library Tab -->
                    <div v-show="activeProfileTab === 'library'" class="space-y-4">
                        <div class="text-sm text-gray-600 mb-4">
                            Manage your reusable attachments. Save frequently-used files here to quickly add them to any document.
                        </div>

                        <!-- Upload to Library -->
                        <div class="border-2 border-dashed border-purple-300 rounded-lg p-4 bg-purple-50">
                            <input
                                type="file"
                                ref="libraryUploadInput"
                                @change="handleLibraryUpload"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="hidden"
                            />
                            <div class="text-center">
                                <button
                                    type="button"
                                    @click="$refs.libraryUploadInput?.click()"
                                    :disabled="isSavingToLibrary"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50"
                                >
                                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                                    {{ isSavingToLibrary ? 'Uploading...' : 'Upload to Library' }}
                                </button>
                                <p class="text-xs text-purple-700 mt-2">
                                    PDF, JPG, PNG • Max 5MB per file
                                </p>
                            </div>
                        </div>

                        <p v-if="errors.library" class="text-sm text-red-600">{{ errors.library }}</p>

                        <!-- Library Files List -->
                        <div v-if="isLoadingLibrary" class="text-center py-8 text-gray-500">
                            Loading your library...
                        </div>
                        <div v-else-if="attachmentLibrary.length === 0" class="text-center py-8 text-gray-500">
                            <DocumentTextIcon class="w-12 h-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
                            <p>No saved attachments yet</p>
                            <p class="text-xs mt-1">Upload files to reuse them across multiple documents</p>
                        </div>
                        <div v-else class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="attachment in attachmentLibrary"
                                :key="attachment.id"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                <!-- File Icon -->
                                <div class="flex-shrink-0">
                                    <div v-if="attachment.is_pdf" class="w-10 h-10 bg-red-100 rounded flex items-center justify-center">
                                        <DocumentTextIcon class="w-6 h-6 text-red-600" aria-hidden="true" />
                                    </div>
                                    <div v-else class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center">
                                        <PhotoIcon class="w-6 h-6 text-blue-600" aria-hidden="true" />
                                    </div>
                                </div>

                                <!-- File Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ attachment.formatted_size }}
                                        <span v-if="attachment.description" class="ml-2">• {{ attachment.description }}</span>
                                    </p>
                                </div>

                                <!-- Actions -->
                                <button
                                    @click="deleteFromLibrary(attachment.id)"
                                    class="flex-shrink-0 p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    aria-label="Delete attachment"
                                >
                                    <TrashIcon class="w-5 h-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button @click="showEditProfile = false" :disabled="isSavingProfile" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 disabled:opacity-50">
                            {{ activeProfileTab === 'library' ? 'Close' : 'Cancel' }}
                        </button>
                        <button
                            v-if="activeProfileTab !== 'library'"
                            @click="saveBusinessProfile"
                            :disabled="!businessName.trim() || isSavingProfile"
                            class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <BookmarkIcon class="w-5 h-5" aria-hidden="true" />
                            {{ isSavingProfile ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attachment Library Modal -->
        <div v-if="showAttachmentLibrary" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Attachment Library</h3>
                    <button @click="showAttachmentLibrary = false" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close">
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Upload to Library Section -->
                    <div class="mb-6 border-2 border-dashed border-purple-300 rounded-lg p-4 bg-purple-50">
                        <input
                            type="file"
                            ref="libraryUploadInput"
                            @change="handleLibraryUpload"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="hidden"
                        />
                        <div class="text-center">
                            <button
                                type="button"
                                @click="$refs.libraryUploadInput?.click()"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                            >
                                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                                Upload to Library
                            </button>
                            <p class="text-xs text-purple-700 mt-2">
                                Upload files directly to your library for future use
                            </p>
                        </div>
                    </div>

                    <p v-if="isLoadingLibrary" class="text-center text-gray-500 py-8">Loading...</p>
                    <p v-else-if="attachmentLibrary.length === 0" class="text-center text-gray-500 py-8">
                        No saved attachments yet. Upload files to your library for reuse across multiple documents.
                    </p>
                    <div v-else class="space-y-2">
                        <div
                            v-for="attachment in attachmentLibrary"
                            :key="attachment.id"
                            class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                            :class="selectedLibraryAttachments.has(attachment.id) ? 'border-purple-500 bg-purple-50' : 'border-gray-200'"
                        >
                            <input
                                type="checkbox"
                                :checked="selectedLibraryAttachments.has(attachment.id)"
                                @change="toggleLibraryAttachment(attachment.id)"
                                class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"
                            />
                            <div class="flex-shrink-0">
                                <DocumentTextIcon v-if="attachment.is_pdf" class="h-5 w-5 text-red-500" aria-hidden="true" />
                                <PhotoIcon v-else class="h-5 w-5 text-blue-500" aria-hidden="true" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ attachment.name }}</p>
                                <p class="text-xs text-gray-500">{{ attachment.formatted_size }} • {{ attachment.original_filename }}</p>
                                <p v-if="attachment.description" class="text-xs text-gray-600 mt-1">{{ attachment.description }}</p>
                            </div>
                            <button
                                type="button"
                                @click="deleteFromLibrary(attachment.id)"
                                class="flex-shrink-0 p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors"
                                title="Delete from library"
                            >
                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between gap-3 p-6 border-t bg-gray-50">
                    <p class="text-sm text-gray-600">
                        {{ selectedLibraryAttachments.size }} selected
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="showAttachmentLibrary = false"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="addSelectedLibraryAttachments"
                            :disabled="selectedLibraryAttachments.size === 0"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors"
                        >
                            Add Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save to Library Modal -->
        <div v-if="showSaveToLibraryModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Save to Library</h3>
                    <button @click="showSaveToLibraryModal = false" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close">
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input
                            v-model="saveToLibraryName"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g., Company Certificate"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea
                            v-model="saveToLibraryDescription"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="Add a description to help identify this attachment later"
                        ></textarea>
                    </div>
                    <p v-if="errors.library" class="text-sm text-red-600">{{ errors.library }}</p>
                </div>
                
                <div class="flex items-center justify-end gap-3 p-6 border-t bg-gray-50">
                    <button
                        @click="showSaveToLibraryModal = false"
                        class="px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveAttachmentToLibrary"
                        :disabled="!saveToLibraryName.trim() || isSavingToLibrary"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors"
                    >
                        {{ isSavingToLibrary ? 'Saving...' : 'Save to Library' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    </QuickInvoiceLayout>
</template>
