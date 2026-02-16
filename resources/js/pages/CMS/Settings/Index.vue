<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import FormSection from '@/components/CMS/FormSection.vue';
import FormInput from '@/components/CMS/FormInput.vue';
import FormSelect from '@/components/CMS/FormSelect.vue';
import { 
    ClockIcon, 
    CurrencyDollarIcon, 
    CheckCircleIcon, 
    DocumentTextIcon,
    BellIcon,
    Cog6ToothIcon,
    ArrowPathIcon,
    BanknotesIcon,
    PaintBrushIcon,
    DevicePhoneMobileIcon
} from '@heroicons/vue/24/outline';

interface Props {
    company: any;
    settings: {
        business_hours: Record<string, { open: string; close: string; enabled: boolean }>;
        tax: {
            enabled: boolean;
            default_rate: number;
            tax_number: string;
            tax_label: string;
            inclusive: boolean;
        };
        approval_thresholds: {
            expense_approval_required: boolean;
            expense_auto_approve_limit: number;
            quotation_approval_required: boolean;
            quotation_auto_approve_limit: number;
            payment_approval_required: boolean;
            payment_auto_approve_limit: number;
        };
        invoice: {
            prefix: string;
            next_number: number;
            due_days: number;
            late_fee_enabled: boolean;
            late_fee_percentage: number;
            late_fee_days: number;
        };
        notifications: {
            email_invoices: boolean;
            email_payments: boolean;
            email_quotations: boolean;
            email_low_stock: boolean;
            email_expense_approval: boolean;
            email_job_updates: boolean;
        };
    };
}

const props = defineProps<Props>();

const activeTab = ref<'business-hours' | 'tax' | 'approval' | 'invoice' | 'notifications' | 'payment' | 'branding' | 'sms'>('business-hours');

// Ensure business_hours has all days with proper structure
const ensureBusinessHours = (hours: any) => {
    const defaultHours = {
        monday: { enabled: true, open: '08:00', close: '17:00' },
        tuesday: { enabled: true, open: '08:00', close: '17:00' },
        wednesday: { enabled: true, open: '08:00', close: '17:00' },
        thursday: { enabled: true, open: '08:00', close: '17:00' },
        friday: { enabled: true, open: '08:00', close: '17:00' },
        saturday: { enabled: false, open: '09:00', close: '13:00' },
        sunday: { enabled: false, open: '00:00', close: '00:00' },
    };
    
    return { ...defaultHours, ...hours };
};

// Business Hours Form
const businessHoursForm = useForm({
    business_hours: ensureBusinessHours(props.settings.business_hours)
});

const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const dayLabels: Record<string, string> = {
    monday: 'Monday',
    tuesday: 'Tuesday',
    wednesday: 'Wednesday',
    thursday: 'Thursday',
    friday: 'Friday',
    saturday: 'Saturday',
    sunday: 'Sunday'
};

const updateBusinessHours = () => {
    businessHoursForm.post(route('cms.settings.business-hours.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by flash message
        }
    });
};

// Tax Settings Form
const taxForm = useForm({
    tax: { ...props.settings.tax }
});

const updateTaxSettings = () => {
    taxForm.post(route('cms.settings.tax.update'), {
        preserveScroll: true
    });
};

// Approval Thresholds Form
const approvalForm = useForm({
    approval_thresholds: { ...props.settings.approval_thresholds }
});

const updateApprovalThresholds = () => {
    approvalForm.post(route('cms.settings.approval-thresholds.update'), {
        preserveScroll: true
    });
};

// Invoice Settings Form
const invoiceForm = useForm({
    invoice: { ...props.settings.invoice }
});

const updateInvoiceSettings = () => {
    invoiceForm.post(route('cms.settings.invoice.update'), {
        preserveScroll: true
    });
};

// Notification Settings Form
const notificationForm = useForm({
    notifications: { ...props.settings.notifications }
});

const updateNotificationSettings = () => {
    notificationForm.post(route('cms.settings.notifications.update'), {
        preserveScroll: true
    });
};

// Reset to defaults
const resetToDefaults = () => {
    if (confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
        router.post(route('cms.settings.reset-defaults'));
    }
};

// Payment Instructions Form
const paymentForm = useForm({
    payment_instructions: { ...props.settings.payment_instructions }
});

const updatePaymentInstructions = () => {
    paymentForm.post(route('cms.settings.payment-instructions.update'), {
        preserveScroll: true
    });
};

// Branding Settings Form
const brandingForm = useForm({
    branding: { ...props.settings.branding }
});

const logoFile = ref<File | null>(null);
const logoPreview = ref<string | null>(null);

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        logoFile.value = target.files[0];
        logoPreview.value = URL.createObjectURL(target.files[0]);
    }
};

const uploadLogo = () => {
    if (!logoFile.value) return;
    
    const formData = new FormData();
    formData.append('logo', logoFile.value);
    
    router.post(route('cms.settings.logo.upload'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            logoFile.value = null;
            logoPreview.value = null;
        }
    });
};

const deleteLogo = () => {
    if (confirm('Are you sure you want to delete the logo?')) {
        router.delete(route('cms.settings.logo.delete'), {
            preserveScroll: true
        });
    }
};

const updateBrandingSettings = () => {
    brandingForm.post(route('cms.settings.branding.update'), {
        preserveScroll: true
    });
};

// SMS Settings Form
const smsForm = useForm({
    sms: { ...props.settings.sms }
});

const updateSmsSettings = () => {
    smsForm.post(route('cms.settings.sms.update'), {
        preserveScroll: true
    });
};

const tabs = [
    { id: 'business-hours', name: 'Business Hours', icon: ClockIcon },
    { id: 'tax', name: 'Tax Settings', icon: CurrencyDollarIcon },
    { id: 'approval', name: 'Approval Thresholds', icon: CheckCircleIcon },
    { id: 'invoice', name: 'Invoice Settings', icon: DocumentTextIcon },
    { id: 'notifications', name: 'Notifications', icon: BellIcon },
    { id: 'payment', name: 'Payment Instructions', icon: BanknotesIcon },
    { id: 'branding', name: 'Branding', icon: PaintBrushIcon },
    { id: 'sms', name: 'SMS Gateway', icon: DevicePhoneMobileIcon },
];
</script>

<template>
    <Head title="Settings" />

    <CMSLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Configure your company settings and preferences
                        </p>
                    </div>
                    <button
                        @click="resetToDefaults"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <ArrowPathIcon class="h-5 w-5" aria-hidden="true" />
                        Reset to Defaults
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id as any"
                        :class="[
                            activeTab === tab.id
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        <component
                            :is="tab.icon"
                            :class="[
                                activeTab === tab.id ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                                '-ml-0.5 mr-2 h-5 w-5'
                            ]"
                            aria-hidden="true"
                        />
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Business Hours Tab -->
            <div v-if="activeTab === 'business-hours'" class="space-y-6">
                <FormSection
                    title="Business Hours"
                    description="Set your operating hours for each day of the week"
                >
                    <form @submit.prevent="updateBusinessHours" class="space-y-4">
                        <div v-for="day in days" :key="day" class="flex items-center gap-4">
                            <div class="w-32">
                                <label class="text-sm font-medium text-gray-700">
                                    {{ dayLabels[day] }}
                                </label>
                            </div>
                            
                            <div v-if="businessHoursForm.business_hours[day]" class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="businessHoursForm.business_hours[day].enabled"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <span class="text-sm text-gray-600">Open</span>
                            </div>

                            <div v-if="businessHoursForm.business_hours[day]?.enabled" class="flex items-center gap-2">
                                <input
                                    type="time"
                                    v-model="businessHoursForm.business_hours[day].open"
                                    class="block w-32 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                                <span class="text-sm text-gray-600">to</span>
                                <input
                                    type="time"
                                    v-model="businessHoursForm.business_hours[day].close"
                                    class="block w-32 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="businessHoursForm.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                Save Business Hours
                            </button>
                        </div>
                    </form>
                </FormSection>
            </div>

            <!-- Tax Settings Tab -->
            <div v-if="activeTab === 'tax'" class="space-y-6">
                <FormSection
                    title="Tax Settings"
                    description="Configure tax rates and tax number for invoices"
                >
                    <form @submit.prevent="updateTaxSettings" class="space-y-6">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="taxForm.tax.enabled"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label class="ml-2 block text-sm text-gray-900">
                                Enable tax on invoices
                            </label>
                        </div>

                        <FormInput
                            v-model="taxForm.tax.tax_label"
                            label="Tax Label"
                            placeholder="VAT, GST, Sales Tax, etc."
                            :error="taxForm.errors['tax.tax_label']"
                        />

                        <FormInput
                            v-model.number="taxForm.tax.default_rate"
                            type="number"
                            step="0.01"
                            label="Default Tax Rate (%)"
                            placeholder="16.00"
                            :error="taxForm.errors['tax.default_rate']"
                        />

                        <FormInput
                            v-model="taxForm.tax.tax_number"
                            label="Tax Number / TPIN"
                            placeholder="Your company tax number"
                            :error="taxForm.errors['tax.tax_number']"
                        />

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="taxForm.tax.inclusive"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label class="ml-2 block text-sm text-gray-900">
                                Tax inclusive pricing (tax included in item prices)
                            </label>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="taxForm.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                Save Tax Settings
                            </button>
                        </div>
                    </form>
                </FormSection>
            </div>

            <!-- Approval Thresholds Tab -->
            <div v-if="activeTab === 'approval'" class="space-y-6">
                <FormSection
                    title="Approval Thresholds"
                    description="Set automatic approval limits for expenses, quotations, and payments"
                >
                    <form @submit.prevent="updateApprovalThresholds" class="space-y-6">
                        <!-- Expenses -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Expenses</h3>
                            
                            <div class="flex items-center mb-4">
                                <input
                                    type="checkbox"
                                    v-model="approvalForm.approval_thresholds.expense_approval_required"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-2 block text-sm text-gray-900">
                                    Require approval for expenses
                                </label>
                            </div>

                            <FormInput
                                v-if="approvalForm.approval_thresholds.expense_approval_required"
                                v-model.number="approvalForm.approval_thresholds.expense_auto_approve_limit"
                                type="number"
                                step="0.01"
                                label="Auto-approve expenses below (K)"
                                placeholder="500"
                                help="Expenses below this amount will be automatically approved"
                                :error="approvalForm.errors['approval_thresholds.expense_auto_approve_limit']"
                            />
                        </div>

                        <!-- Quotations -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quotations</h3>
                            
                            <div class="flex items-center mb-4">
                                <input
                                    type="checkbox"
                                    v-model="approvalForm.approval_thresholds.quotation_approval_required"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-2 block text-sm text-gray-900">
                                    Require approval for quotations
                                </label>
                            </div>

                            <FormInput
                                v-if="approvalForm.approval_thresholds.quotation_approval_required"
                                v-model.number="approvalForm.approval_thresholds.quotation_auto_approve_limit"
                                type="number"
                                step="0.01"
                                label="Auto-approve quotations below (K)"
                                placeholder="5000"
                                :error="approvalForm.errors['approval_thresholds.quotation_auto_approve_limit']"
                            />
                        </div>

                        <!-- Payments -->
                        <div class="pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payments</h3>
                            
                            <div class="flex items-center mb-4">
                                <input
                                    type="checkbox"
                                    v-model="approvalForm.approval_thresholds.payment_approval_required"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-2 block text-sm text-gray-900">
                                    Require approval for payments
                                </label>
                            </div>

                            <FormInput
                                v-if="approvalForm.approval_thresholds.payment_approval_required"
                                v-model.number="approvalForm.approval_thresholds.payment_auto_approve_limit"
                                type="number"
                                step="0.01"
                                label="Auto-approve payments below (K)"
                                placeholder="10000"
                                :error="approvalForm.errors['approval_thresholds.payment_auto_approve_limit']"
                            />
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="approvalForm.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                Save Approval Thresholds
                            </button>
                        </div>
                    </form>
                </FormSection>
            </div>

            <!-- Invoice Settings Tab -->
            <div v-if="activeTab === 'invoice'" class="space-y-6">
                <FormSection
                    title="Invoice Settings"
                    description="Configure invoice numbering, due dates, and late fees"
                >
                    <form @submit.prevent="updateInvoiceSettings" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <FormInput
                                v-model="invoiceForm.invoice.prefix"
                                label="Invoice Prefix"
                                placeholder="INV"
                                :error="invoiceForm.errors['invoice.prefix']"
                            />

                            <FormInput
                                v-model.number="invoiceForm.invoice.next_number"
                                type="number"
                                label="Next Invoice Number"
                                placeholder="1"
                                :error="invoiceForm.errors['invoice.next_number']"
                            />
                        </div>

                        <FormInput
                            v-model.number="invoiceForm.invoice.due_days"
                            type="number"
                            label="Default Payment Terms (days)"
                            placeholder="30"
                            help="Number of days until invoice is due"
                            :error="invoiceForm.errors['invoice.due_days']"
                        />

                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Late Fees</h3>
                            
                            <div class="flex items-center mb-4">
                                <input
                                    type="checkbox"
                                    v-model="invoiceForm.invoice.late_fee_enabled"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-2 block text-sm text-gray-900">
                                    Enable late fees for overdue invoices
                                </label>
                            </div>

                            <div v-if="invoiceForm.invoice.late_fee_enabled" class="grid grid-cols-2 gap-6">
                                <FormInput
                                    v-model.number="invoiceForm.invoice.late_fee_percentage"
                                    type="number"
                                    step="0.01"
                                    label="Late Fee Percentage (%)"
                                    placeholder="5"
                                    :error="invoiceForm.errors['invoice.late_fee_percentage']"
                                />

                                <FormInput
                                    v-model.number="invoiceForm.invoice.late_fee_days"
                                    type="number"
                                    label="Grace Period (days)"
                                    placeholder="7"
                                    help="Days after due date before late fee applies"
                                    :error="invoiceForm.errors['invoice.late_fee_days']"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="invoiceForm.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                Save Invoice Settings
                            </button>
                        </div>
                    </form>
                </FormSection>
            </div>

            <!-- Notifications Tab -->
            <div v-if="activeTab === 'notifications'" class="space-y-6">
                <FormSection
                    title="Email Notifications"
                    description="Choose which events trigger email notifications"
                >
                    <form @submit.prevent="updateNotificationSettings" class="space-y-4">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="notificationForm.notifications.email_invoices"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-3 block text-sm text-gray-900">
                                    <span class="font-medium">Invoice Notifications</span>
                                    <p class="text-gray-500">Send email when invoices are created or sent</p>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="notificationForm.notifications.email_payments"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-3 block text-sm text-gray-900">
                                    <span class="font-medium">Payment Notifications</span>
                                    <p class="text-gray-500">Send email when payments are received</p>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="notificationForm.notifications.email_quotations"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-3 block text-sm text-gray-900">
                                    <span class="font-medium">Quotation Notifications</span>
                                    <p class="text-gray-500">Send email when quotations are sent</p>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="notificationForm.notifications.email_low_stock"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-3 block text-sm text-gray-900">
                                    <span class="font-medium">Low Stock Alerts</span>
                                    <p class="text-gray-500">Send email when inventory is low</p>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="notificationForm.notifications.email_expense_approval"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-3 block text-sm text-gray-900">
                                    <span class="font-medium">Expense Approval Notifications</span>
                                    <p class="text-gray-500">Send email when expenses need approval</p>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="notificationForm.notifications.email_job_updates"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-3 block text-sm text-gray-900">
                                    <span class="font-medium">Job Update Notifications</span>
                                    <p class="text-gray-500">Send email when job status changes</p>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="notificationForm.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                Save Notification Settings
                            </button>
                        </div>
                    </form>
                </FormSection>
            </div>

            <!-- Payment Instructions Tab -->
            <div v-if="activeTab === 'payment'" class="space-y-6">
                <FormSection
                    title="Payment Instructions"
                    description="Configure payment collection methods to display on invoices"
                >
                    <form @submit.prevent="updatePaymentInstructions" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <FormInput
                                v-model="paymentForm.payment_instructions.bank_name"
                                label="Bank Name"
                                placeholder="e.g., Zanaco Bank"
                            />
                            
                            <FormInput
                                v-model="paymentForm.payment_instructions.account_name"
                                label="Account Name"
                                placeholder="e.g., Company Name Ltd"
                            />
                            
                            <FormInput
                                v-model="paymentForm.payment_instructions.account_number"
                                label="Account Number"
                                placeholder="e.g., 1234567890"
                            />
                            
                            <FormInput
                                v-model="paymentForm.payment_instructions.branch"
                                label="Branch"
                                placeholder="e.g., Lusaka Main Branch"
                            />
                            
                            <FormInput
                                v-model="paymentForm.payment_instructions.swift_code"
                                label="SWIFT Code (Optional)"
                                placeholder="e.g., ZANAZMLX"
                            />
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-4">Mobile Money</h4>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <FormInput
                                    v-model="paymentForm.payment_instructions.mobile_money_name"
                                    label="Mobile Money Provider"
                                    placeholder="e.g., MTN Mobile Money"
                                />
                                
                                <FormInput
                                    v-model="paymentForm.payment_instructions.mobile_money_number"
                                    label="Mobile Money Number"
                                    placeholder="e.g., +260 97 1234567"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Instructions
                            </label>
                            <textarea
                                v-model="paymentForm.payment_instructions.additional_instructions"
                                rows="3"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Any additional payment instructions for customers..."
                            ></textarea>
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="paymentForm.payment_instructions.show_on_invoice"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label class="ml-3 block text-sm text-gray-900">
                                Show payment instructions on invoices
                            </label>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="paymentForm.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                Save Payment Instructions
                            </button>
                        </div>
                    </form>
                </FormSection>
            </div>

            <!-- Branding Tab -->
            <div v-if="activeTab === 'branding'" class="space-y-6">
                <FormSection
                    title="Company Branding"
                    description="Customize your company's visual identity"
                >
                    <div class="space-y-6">
                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company Logo
                            </label>
                            
                            <div v-if="company.logo_path || logoPreview" class="mb-4">
                                <img
                                    :src="logoPreview || `/storage/${company.logo_path}`"
                                    alt="Company Logo"
                                    class="h-24 w-auto object-contain border border-gray-200 rounded-lg p-2"
                                />
                                <button
                                    v-if="!logoPreview"
                                    @click="deleteLogo"
                                    type="button"
                                    class="mt-2 text-sm text-red-600 hover:text-red-700"
                                >
                                    Delete Logo
                                </button>
                            </div>

                            <div class="flex items-center gap-4">
                                <input
                                    type="file"
                                    @change="handleLogoChange"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <button
                                    v-if="logoFile"
                                    @click="uploadLogo"
                                    type="button"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700"
                                >
                                    Upload
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Recommended: PNG or SVG, max 2MB
                            </p>
                        </div>

                        <form @submit.prevent="updateBrandingSettings" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Primary Color
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="color"
                                            v-model="brandingForm.branding.primary_color"
                                            class="h-10 w-20 rounded border-gray-300"
                                        />
                                        <input
                                            type="text"
                                            v-model="brandingForm.branding.primary_color"
                                            class="block flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            placeholder="#2563eb"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Secondary Color
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="color"
                                            v-model="brandingForm.branding.secondary_color"
                                            class="h-10 w-20 rounded border-gray-300"
                                        />
                                        <input
                                            type="text"
                                            v-model="brandingForm.branding.secondary_color"
                                            class="block flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            placeholder="#64748b"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Invoice Footer Text
                                </label>
                                <textarea
                                    v-model="brandingForm.branding.invoice_footer"
                                    rows="3"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Thank you for your business! For inquiries, contact us at..."
                                ></textarea>
                                <p class="mt-2 text-sm text-gray-500">
                                    This text will appear at the bottom of invoices and receipts
                                </p>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="brandingForm.branding.show_logo_on_invoice"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <label class="ml-3 block text-sm text-gray-900">
                                        Show logo on invoices
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="brandingForm.branding.show_logo_on_receipt"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <label class="ml-3 block text-sm text-gray-900">
                                        Show logo on receipts
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button
                                    type="submit"
                                    :disabled="brandingForm.processing"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                >
                                    Save Branding Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </FormSection>
            </div>

            <!-- SMS Gateway Tab -->
            <div v-if="activeTab === 'sms'" class="space-y-6">
                <FormSection
                    title="SMS Gateway Configuration"
                    description="Configure SMS notifications (requires paid subscription)"
                >
                    <form @submit.prevent="updateSmsSettings" class="space-y-6">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-amber-800">
                                        SMS Gateway Subscription Required
                                    </h3>
                                    <div class="mt-2 text-sm text-amber-700">
                                        <p>SMS notifications require a paid subscription to Africa's Talking or Twilio. You can configure the settings now and enable them when ready.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="smsForm.sms.enabled"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label class="ml-3 block text-sm text-gray-900">
                                <span class="font-medium">Enable SMS Notifications</span>
                                <p class="text-gray-500">Turn on SMS notifications (requires valid API credentials)</p>
                            </label>
                        </div>

                        <FormSelect
                            v-model="smsForm.sms.provider"
                            label="SMS Provider"
                            :options="[
                                { value: 'africas_talking', label: 'Africa\'s Talking' },
                                { value: 'twilio', label: 'Twilio' }
                            ]"
                        />

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <FormInput
                                v-model="smsForm.sms.api_key"
                                label="API Key"
                                type="password"
                                placeholder="Enter your API key"
                            />
                            
                            <FormInput
                                v-model="smsForm.sms.api_secret"
                                label="API Secret"
                                type="password"
                                placeholder="Enter your API secret"
                            />
                        </div>

                        <FormInput
                            v-model="smsForm.sms.sender_id"
                            label="Sender ID"
                            placeholder="e.g., YourCompany"
                            help="The name that will appear as the sender"
                        />

                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-4">SMS Notification Types</h4>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="smsForm.sms.send_invoice_notifications"
                                        :disabled="!smsForm.sms.enabled"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:opacity-50"
                                    />
                                    <label class="ml-3 block text-sm text-gray-900">
                                        Send invoice notifications via SMS
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="smsForm.sms.send_payment_confirmations"
                                        :disabled="!smsForm.sms.enabled"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:opacity-50"
                                    />
                                    <label class="ml-3 block text-sm text-gray-900">
                                        Send payment confirmation via SMS
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="smsForm.sms.send_payment_reminders"
                                        :disabled="!smsForm.sms.enabled"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:opacity-50"
                                    />
                                    <label class="ml-3 block text-sm text-gray-900">
                                        Send payment reminders via SMS
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="smsForm.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                Save SMS Settings
                            </button>
                        </div>
                    </form>
                </FormSection>
            </div>
        </div>
    </CMSLayout>
</template>
