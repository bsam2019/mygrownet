<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    DocumentTextIcon,
    ArrowDownTrayIcon,
    PaperAirplaneIcon,
    CheckCircleIcon,
    ArrowLeftIcon,
    BuildingOfficeIcon,
    UserIcon,
    BanknotesIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

interface DocumentItem {
    description: string;
    quantity: number;
    unitPrice: number;
    taxRate: number;
    discountAmount: number;
    lineTotal: number;
}

interface Payment {
    id: number;
    paymentDate: string;
    amount: number;
    paymentMethod: string;
    paymentMethodLabel: string;
    referenceNumber?: string;
    notes?: string;
    receiptId?: number;
}

interface Document {
    id: number;
    documentNumber: string;
    documentType: string;
    issueDate: string;
    dueDate?: string;
    validityDate?: string;
    subtotal: number;
    taxTotal: number;
    discountTotal: number;
    grandTotal: number;
    currency: string;
    status: string;
    notes?: string;
    terms?: string;
    paymentInstructions?: string;
    pdfPath?: string;
    discountType?: string;
    discountValue?: number;
    collectTax?: boolean;
    items: DocumentItem[];
    payments: Payment[];
    totalPaid: number;
    remainingBalance: number;
}

interface Customer {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
    tpin?: string;
}

interface BusinessProfile {
    id: number;
    businessName: string;
    address: string;
    phone: string;
    email?: string;
    tpin?: string;
    website?: string;
    logo?: string;
}

interface Props {
    document: Document;
    businessProfile: BusinessProfile;
    customer: Customer;
}

const props = defineProps<Props>();

const isGeneratingPdf = ref(false);
const whatsappLink = ref<string | null>(null);
const showPaymentModal = ref(false);
const isRecordingPayment = ref(false);

const paymentForm = ref({
    payment_date: new Date().toISOString().split('T')[0],
    amount: 0,
    payment_method: 'cash',
    reference_number: '',
    notes: '',
    generate_receipt: true,
});

const statusConfig: Record<string, { dot: string; bg: string; text: string }> = {
    draft:         { dot: 'bg-slate-400',   bg: 'bg-slate-100',   text: 'text-slate-600'   },
    sent:          { dot: 'bg-blue-500',    bg: 'bg-blue-50',     text: 'text-blue-700'    },
    paid:          { dot: 'bg-emerald-500', bg: 'bg-emerald-50',  text: 'text-emerald-700' },
    overdue:       { dot: 'bg-red-500',     bg: 'bg-red-50',      text: 'text-red-700'     },
    cancelled:     { dot: 'bg-slate-400',   bg: 'bg-slate-100',   text: 'text-slate-500'   },
    issued:        { dot: 'bg-emerald-500', bg: 'bg-emerald-50',  text: 'text-emerald-700' },
    voided:        { dot: 'bg-red-500',     bg: 'bg-red-50',      text: 'text-red-700'     },
    accepted:      { dot: 'bg-emerald-500', bg: 'bg-emerald-50',  text: 'text-emerald-700' },
    rejected:      { dot: 'bg-red-500',     bg: 'bg-red-50',      text: 'text-red-700'     },
    expired:       { dot: 'bg-amber-500',   bg: 'bg-amber-50',    text: 'text-amber-700'   },
    delivered:     { dot: 'bg-blue-500',    bg: 'bg-blue-50',     text: 'text-blue-700'    },
    acknowledged:  { dot: 'bg-emerald-500', bg: 'bg-emerald-50',  text: 'text-emerald-700' },
    partially_paid:{ dot: 'bg-amber-500',   bg: 'bg-amber-50',    text: 'text-amber-700'   },
};

const documentTypeLabel = computed(() => ({
    invoice:          'Invoice',
    receipt:          'Receipt',
    quotation:        'Quotation',
    delivery_note:    'Delivery Note',
    proforma_invoice: 'Proforma Invoice',
    credit_note:      'Credit Note',
    debit_note:       'Debit Note',
    purchase_order:   'Purchase Order',
}[props.document.documentType] ?? 'Document'));

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

const fmt = (amount: number) =>
    `${props.document.currency} ${amount.toFixed(2)}`;

const statusFor = (s: string | undefined) => {
    if (!s) return { dot: 'bg-slate-400', bg: 'bg-slate-100', text: 'text-slate-600' };
    return statusConfig[s.toLowerCase()] ?? { dot: 'bg-slate-400', bg: 'bg-slate-100', text: 'text-slate-600' };
};

const canFinalize = computed(() => props.document.status === 'draft');

const canEdit = computed(() => props.document.status === 'draft');

const canRecordPayment = computed(() => 
    props.document.documentType === 'invoice' && 
    props.document.status !== 'cancelled' &&
    props.document.status !== 'paid' &&
    props.document.remainingBalance > 0
);

const canCancel = computed(() => 
    props.document.documentType === 'invoice' &&
    ['draft', 'sent', 'overdue', 'partially_paid'].includes(props.document.status)
);

const canVoid = computed(() => 
    props.document.documentType === 'receipt' &&
    props.document.status === 'issued'
);

const canConvert = computed(() => 
    props.document.documentType === 'quotation' &&
    ['sent', 'accepted'].includes(props.document.status)
);

const showCancelModal = ref(false);
const showVoidModal = ref(false);
const showConvertModal = ref(false);
const cancellationReason = ref('');
const isProcessing = ref(false);

const openPaymentModal = () => {
    paymentForm.value.amount = props.document.remainingBalance;
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
    paymentForm.value = {
        payment_date: new Date().toISOString().split('T')[0],
        amount: 0,
        payment_method: 'cash',
        reference_number: '',
        notes: '',
        generate_receipt: true,
    };
};

const openCancelModal = () => {
    cancellationReason.value = '';
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    showCancelModal.value = false;
    cancellationReason.value = '';
};

const openVoidModal = () => {
    cancellationReason.value = '';
    showVoidModal.value = true;
};

const closeVoidModal = () => {
    showVoidModal.value = false;
    cancellationReason.value = '';
};

const convertForm = ref({
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    notes: '',
    payment_instructions: '',
});

const openConvertModal = () => {
    // Pre-fill with quotation data
    convertForm.value = {
        issue_date: new Date().toISOString().split('T')[0],
        due_date: '',
        notes: props.document.notes || '',
        payment_instructions: props.document.paymentInstructions || '',
    };
    showConvertModal.value = true;
};

const closeConvertModal = () => {
    showConvertModal.value = false;
};

const convertToInvoice = async () => {
    const result = await Swal.fire({
        title: 'Convert to Invoice?',
        text: 'This will create a new invoice from this quotation.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, convert it',
        cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) return;

    isProcessing.value = true;

    try {
        await axios.post(`/bizdocs/documents/${props.document.id}/convert-to-invoice`, convertForm.value);

        await Swal.fire({
            icon: 'success',
            title: 'Converted Successfully',
            text: 'Quotation has been converted to invoice',
        });

        // Will redirect to the new invoice via controller
    } catch (error: any) {
        console.error('Conversion error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Conversion Failed',
            text: error.response?.data?.error || 'Failed to convert quotation',
        });
        isProcessing.value = false;
    }
};

const cancelDocument = async () => {
    if (!cancellationReason.value.trim()) {
        await Swal.fire({
            icon: 'error',
            title: 'Reason Required',
            text: 'Please provide a reason for cancellation',
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Cancel Document?',
        text: 'This action cannot be undone. The document will be marked as cancelled.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'No, keep it',
    });

    if (!result.isConfirmed) return;

    isProcessing.value = true;

    try {
        await axios.post(`/bizdocs/documents/${props.document.id}/cancel`, {
            reason: cancellationReason.value,
        });

        await Swal.fire({
            icon: 'success',
            title: 'Document Cancelled',
            text: 'The document has been cancelled successfully',
        });

        router.reload();
    } catch (error: any) {
        console.error('Cancellation error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Cancellation Failed',
            text: error.response?.data?.error || 'Failed to cancel document',
        });
    } finally {
        isProcessing.value = false;
        closeCancelModal();
    }
};

const voidDocument = async () => {
    if (!cancellationReason.value.trim()) {
        await Swal.fire({
            icon: 'error',
            title: 'Reason Required',
            text: 'Please provide a reason for voiding this receipt',
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Void Receipt?',
        text: 'This action cannot be undone. The receipt will be marked as voided.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, void it',
        cancelButtonText: 'No, keep it',
    });

    if (!result.isConfirmed) return;

    isProcessing.value = true;

    try {
        await axios.post(`/bizdocs/documents/${props.document.id}/void`, {
            reason: cancellationReason.value,
        });

        await Swal.fire({
            icon: 'success',
            title: 'Receipt Voided',
            text: 'The receipt has been voided successfully',
        });

        router.reload();
    } catch (error: any) {
        console.error('Void error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Void Failed',
            text: error.response?.data?.error || 'Failed to void receipt',
        });
    } finally {
        isProcessing.value = false;
        closeVoidModal();
    }
};

const recordPayment = async () => {
    if (isRecordingPayment.value) return;
    
    isRecordingPayment.value = true;
    
    try {
        const response = await axios.post(
            `/bizdocs/documents/${props.document.id}/record-payment`,
            paymentForm.value
        );
        
        console.log('Payment response:', response.data);
        
        if (response.data.success) {
            closePaymentModal();
            await Swal.fire({
                icon: 'success',
                title: 'Payment Recorded',
                text: 'Payment has been recorded successfully',
                timer: 2000,
                showConfirmButton: false,
            });
            router.reload({ only: ['document'] });
        } else {
            throw new Error(response.data.error || 'Payment failed');
        }
    } catch (error: any) {
        console.error('Payment error:', error);
        console.error('Error response:', error.response);
        
        await Swal.fire({
            icon: 'error',
            title: 'Payment Failed',
            text: error.response?.data?.error || error.message || 'Failed to record payment',
        });
    } finally {
        isRecordingPayment.value = false;
    }
};

const finalize = async () => {
    const result = await Swal.fire({
        title: 'Finalise Document?',
        text: `Finalise this ${documentTypeLabel.value.toLowerCase()}? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, finalise it',
        cancelButtonText: 'Cancel',
    });

    if (result.isConfirmed) {
        router.post(`/bizdocs/documents/${props.document.id}/finalize`);
    }
};

const downloadPdf = () => {
    window.open(`/bizdocs/documents/${props.document.id}/download-pdf`, '_blank');
};

const previewPdf = () => {
    window.open(`/bizdocs/documents/${props.document.id}/preview-pdf`, '_blank');
};

const duplicateDocument = async () => {
    const result = await Swal.fire({
        title: 'Duplicate Document?',
        text: 'This will create a new draft document with the same details.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, duplicate it',
        cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) return;

    isProcessing.value = true;

    try {
        await axios.post(`/bizdocs/documents/${props.document.id}/duplicate`);

        await Swal.fire({
            icon: 'success',
            title: 'Document Duplicated',
            text: 'A new draft has been created',
        });

        // Will redirect to the new document via controller
    } catch (error: any) {
        console.error('Duplication error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Duplication Failed',
            text: error.response?.data?.error || 'Failed to duplicate document',
        });
        isProcessing.value = false;
    }
};

const shareViaWhatsApp = async () => {
    if (isGeneratingPdf.value) return;
    
    isGeneratingPdf.value = true;
    
    try {
        const response = await axios.post(`/bizdocs/documents/${props.document.id}/share-pdf`);
        
        if (response.data.success && response.data.whatsappLink) {
            whatsappLink.value = response.data.whatsappLink;
            window.open(response.data.whatsappLink, '_blank');
        }
    } catch (error: any) {
        await Swal.fire({
            icon: 'error',
            title: 'Failed to Generate Link',
            text: error.response?.data?.error || 'Failed to generate WhatsApp link',
        });
    } finally {
        isGeneratingPdf.value = false;
    }
};

const initials = (name: string | undefined) => {
    if (!name) return '??';
    return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
};
</script>

<template>
    <Head :title="`${documentTypeLabel} ${document.documentNumber}`" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-5xl mx-auto">

                <!-- Back link -->
                <Link
                    href="/bizdocs/documents"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-800 mb-5 transition-colors">
                    <ArrowLeftIcon class="w-3.5 h-3.5" />
                    Back to Documents
                </Link>

                <!-- Page header -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <!-- Document info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                                    <span class="w-5 h-px bg-blue-500 inline-block"></span>
                                    BizDocs
                                </span>
                            </div>
                            <h1 class="text-2xl font-bold text-slate-900 leading-none mb-2">
                                {{ documentTypeLabel }}
                                <span class="font-mono text-slate-500 ml-1 text-xl">{{ document.documentNumber }}</span>
                            </h1>
                            <div class="flex items-center gap-2.5">
                                <!-- Status badge -->
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium capitalize"
                                    :class="[statusFor(document.status).bg, statusFor(document.status).text]">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                        :class="statusFor(document.status).dot"></span>
                                    {{ document.status.replace('_', ' ') }}
                                </span>
                                <span class="text-xs text-slate-400">Issued {{ formatDate(document.issueDate) }}</span>
                            </div>
                        </div>

                        <!-- Action buttons - Organized by priority -->
                        <div class="flex flex-col sm:flex-row gap-2">
                            <!-- Primary actions (left group) -->
                            <div class="flex flex-wrap gap-2">
                                <!-- Finalise -->
                                <button
                                    v-if="canFinalize"
                                    @click="finalize"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition-all">
                                    <CheckCircleIcon class="w-4 h-4" aria-hidden="true" />
                                    <span>Finalise</span>
                                </button>

                                <!-- Edit Draft -->
                                <Link
                                    v-if="canEdit"
                                    :href="`/bizdocs/documents/${document.id}/edit`"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span>Edit</span>
                                </Link>

                                <!-- Record Payment -->
                                <button
                                    v-if="canRecordPayment"
                                    @click="openPaymentModal"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-all">
                                    <BanknotesIcon class="w-4 h-4" aria-hidden="true" />
                                    <span>Record Payment</span>
                                </button>

                                <!-- Convert to Invoice -->
                                <button
                                    v-if="canConvert"
                                    @click="openConvertModal"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="hidden sm:inline">Convert</span>
                                </button>

                                <!-- Cancel/Void -->
                                <button
                                    v-if="canCancel"
                                    @click="openCancelModal"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Cancel</span>
                                </button>

                                <button
                                    v-if="canVoid"
                                    @click="openVoidModal"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Void</span>
                                </button>
                            </div>

                            <!-- Secondary actions (right group) -->
                            <div class="flex flex-wrap gap-2 sm:border-l sm:border-slate-200 sm:pl-2">
                                <!-- Download PDF -->
                                <button
                                    @click="downloadPdf"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1 transition-all">
                                    <ArrowDownTrayIcon class="w-4 h-4" aria-hidden="true" />
                                    <span class="hidden sm:inline">Download</span>
                                </button>

                                <!-- Share via WhatsApp -->
                                <button
                                    @click="shareViaWhatsApp"
                                    :disabled="isGeneratingPdf"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                    <PaperAirplaneIcon class="w-4 h-4" aria-hidden="true" />
                                    <span class="hidden sm:inline">{{ isGeneratingPdf ? 'Sending...' : 'Share' }}</span>
                                </button>

                                <!-- Duplicate -->
                                <button
                                    @click="duplicateDocument"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <span class="hidden sm:inline">Duplicate</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Template Preview -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <DocumentTextIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                                <h3 class="text-base font-bold text-slate-900">Document Preview</h3>
                                <span class="text-xs text-slate-500">(As it will appear in PDF)</span>
                            </div>
                            <button
                                @click="previewPdf"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-50 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Open in New Tab
                            </button>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-4">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden" style="max-width: 210mm; margin: 0 auto;">
                            <iframe
                                :src="`/bizdocs/documents/${document.id}/preview-pdf`"
                                class="w-full border-0"
                                style="height: 297mm; min-height: 800px;"
                                title="Document Preview"
                            ></iframe>
                        </div>
                    </div>
                </div>

                <!-- Cancellation/Void Notice -->
                <div v-if="document.cancellationReason" 
                    class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-red-900 mb-1">
                                {{ document.status === 'voided' ? 'Receipt Voided' : 'Document Cancelled' }}
                            </h3>
                            <p class="text-sm text-red-700 mb-2">{{ document.cancellationReason }}</p>
                            <p v-if="document.cancelledAt" class="text-xs text-red-600">
                                {{ document.status === 'voided' ? 'Voided' : 'Cancelled' }} on {{ formatDate(document.cancelledAt.split(' ')[0]) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Bar (for invoices) -->
                <div v-if="document.documentType === 'invoice' && document.status !== 'draft'" 
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-4 mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-6 flex-wrap">
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Subtotal</p>
                                <p class="text-sm font-semibold text-slate-700">{{ fmt(document.subtotal) }}</p>
                            </div>
                            <div v-if="document.discountTotal > 0">
                                <p class="text-xs text-slate-500 mb-1">Discount</p>
                                <p class="text-sm font-semibold text-amber-600">−{{ fmt(document.discountTotal) }}</p>
                            </div>
                            <div v-if="document.collectTax !== false && document.taxTotal > 0">
                                <p class="text-xs text-slate-500 mb-1">VAT</p>
                                <p class="text-sm font-semibold text-slate-700">{{ fmt(document.taxTotal) }}</p>
                            </div>
                            <div class="border-l border-blue-200 pl-6">
                                <p class="text-xs text-slate-500 mb-1">Total Amount</p>
                                <p class="text-lg font-bold text-slate-900">{{ fmt(document.grandTotal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Total Paid</p>
                                <p class="text-lg font-bold text-emerald-600">{{ fmt(document.totalPaid) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Balance Due</p>
                                <p class="text-lg font-bold" :class="document.remainingBalance > 0 ? 'text-amber-600' : 'text-emerald-600'">
                                    {{ fmt(document.remainingBalance) }}
                                </p>
                            </div>
                        </div>
                        <button
                            v-if="canRecordPayment"
                            @click="openPaymentModal"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-all">
                            <BanknotesIcon class="w-4 h-4" aria-hidden="true" />
                            Record Payment
                        </button>
                    </div>
                </div>

                <!-- Payment History (for invoices with payments) -->
                <div v-if="document.documentType === 'invoice' && document.payments.length > 0" 
                    class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mt-6">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <ClockIcon class="w-5 h-5 text-blue-500" aria-hidden="true" />
                            <h3 class="text-base font-bold text-slate-900">Payment History</h3>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="payment in document.payments" :key="payment.id" 
                            class="px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <BanknotesIcon class="w-5 h-5 text-emerald-600" aria-hidden="true" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-900">{{ fmt(payment.amount) }}</p>
                                        <p class="text-xs text-slate-500">{{ payment.paymentMethodLabel }}</p>
                                        <p v-if="payment.referenceNumber" class="text-xs text-slate-400 mt-0.5">
                                            Ref: {{ payment.referenceNumber }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-slate-600">{{ formatDate(payment.paymentDate) }}</p>
                                        <p v-if="payment.notes" class="text-xs text-slate-400 mt-1">{{ payment.notes }}</p>
                                    </div>
                                    <Link
                                        v-if="payment.receiptId"
                                        :href="`/bizdocs/documents/${payment.receiptId}`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors">
                                        <DocumentTextIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                        View Receipt
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closePaymentModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <BanknotesIcon class="w-4 h-4 text-blue-600" aria-hidden="true" />
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Record Payment</h3>
                        </div>
                        <button
                            @click="closePaymentModal"
                            class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <form @submit.prevent="recordPayment" class="px-6 py-5 space-y-4">
                    <!-- Payment Date -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Payment Date</label>
                        <input
                            v-model="paymentForm.payment_date"
                            type="date"
                            required
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-medium">
                                {{ document.currency }}
                            </span>
                            <input
                                v-model.number="paymentForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                :max="document.remainingBalance"
                                required
                                class="w-full pl-20 pr-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Balance due: {{ fmt(document.remainingBalance) }}</p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Payment Method</label>
                        <select
                            v-model="paymentForm.payment_method"
                            required
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Reference Number (Optional)</label>
                        <input
                            v-model="paymentForm.reference_number"
                            type="text"
                            maxlength="100"
                            placeholder="Transaction ID, Cheque number, etc."
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Notes (Optional)</label>
                        <textarea
                            v-model="paymentForm.notes"
                            rows="2"
                            placeholder="Additional payment details..."
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"></textarea>
                    </div>

                    <!-- Generate Receipt -->
                    <div class="flex items-center gap-2">
                        <input
                            v-model="paymentForm.generate_receipt"
                            type="checkbox"
                            id="generate_receipt"
                            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-2 focus:ring-blue-500" />
                        <label for="generate_receipt" class="text-sm text-slate-700">
                            Auto-generate receipt
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="closePaymentModal"
                            class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isRecordingPayment"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            {{ isRecordingPayment ? 'Recording...' : 'Record Payment' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closeCancelModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Cancel Document</h3>
                        </div>
                        <button
                            @click="closeCancelModal"
                            class="text-slate-400 hover:text-slate-600 transition-colors"
                            aria-label="Close cancel modal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600 mb-4">
                        Please provide a reason for cancelling this document. This action cannot be undone.
                    </p>
                    <textarea
                        v-model="cancellationReason"
                        rows="4"
                        placeholder="Enter cancellation reason..."
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none"
                        required></textarea>
                </div>

                <div class="px-6 py-4 bg-slate-50 rounded-b-xl flex gap-3">
                    <button
                        @click="closeCancelModal"
                        type="button"
                        class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition-all">
                        Keep Document
                    </button>
                    <button
                        @click="cancelDocument"
                        :disabled="isProcessing"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        {{ isProcessing ? 'Cancelling...' : 'Cancel Document' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Void Modal -->
        <div v-if="showVoidModal" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closeVoidModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Void Receipt</h3>
                        </div>
                        <button
                            @click="closeVoidModal"
                            class="text-slate-400 hover:text-slate-600 transition-colors"
                            aria-label="Close void modal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600 mb-4">
                        Please provide a reason for voiding this receipt. This action cannot be undone.
                    </p>
                    <textarea
                        v-model="cancellationReason"
                        rows="4"
                        placeholder="Enter void reason..."
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none"
                        required></textarea>
                </div>

                <div class="px-6 py-4 bg-slate-50 rounded-b-xl flex gap-3">
                    <button
                        @click="closeVoidModal"
                        type="button"
                        class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition-all">
                        Keep Receipt
                    </button>
                    <button
                        @click="voidDocument"
                        :disabled="isProcessing"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        {{ isProcessing ? 'Voiding...' : 'Void Receipt' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Convert to Invoice Modal -->
        <div v-if="showConvertModal" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closeConvertModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
                <div class="px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Convert to Invoice</h3>
                                <p class="text-xs text-slate-500">Create an invoice from this quotation</p>
                            </div>
                        </div>
                        <button
                            @click="closeConvertModal"
                            class="text-slate-400 hover:text-slate-600 transition-colors"
                            aria-label="Close convert modal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="convert_issue_date" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Issue Date <span class="text-red-400">*</span>
                            </label>
                            <input
                                id="convert_issue_date"
                                v-model="convertForm.issue_date"
                                type="date"
                                required
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" />
                        </div>

                        <div>
                            <label for="convert_due_date" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Due Date
                            </label>
                            <input
                                id="convert_due_date"
                                v-model="convertForm.due_date"
                                type="date"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" />
                        </div>
                    </div>

                    <div>
                        <label for="convert_notes" class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Notes
                        </label>
                        <textarea
                            id="convert_notes"
                            v-model="convertForm.notes"
                            rows="2"
                            placeholder="Any additional notes for the invoice..."
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"></textarea>
                    </div>

                    <div>
                        <label for="convert_payment_instructions" class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Payment Instructions
                        </label>
                        <textarea
                            id="convert_payment_instructions"
                            v-model="convertForm.payment_instructions"
                            rows="2"
                            placeholder="How should the customer pay?"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"></textarea>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <p class="text-xs text-indigo-700">
                            <strong>Note:</strong> A new invoice will be created with the same items and customer details from this quotation.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 rounded-b-xl flex gap-3">
                    <button
                        @click="closeConvertModal"
                        type="button"
                        class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button
                        @click="convertToInvoice"
                        :disabled="isProcessing"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        {{ isProcessing ? 'Converting...' : 'Convert to Invoice' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>