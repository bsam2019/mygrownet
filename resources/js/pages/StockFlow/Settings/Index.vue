<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';
import {
    UserCircleIcon, BuildingOffice2Icon, EnvelopeIcon,
    PhoneIcon, MapPinIcon, GlobeAltIcon, CurrencyDollarIcon,
    PaintBrushIcon, CheckCircleIcon, LockClosedIcon,
    ArchiveBoxIcon, ShoppingCartIcon, CreditCardIcon,
    CurrencyDollarIcon as CashIcon, ClipboardDocumentListIcon,
    DocumentTextIcon, BuildingStorefrontIcon, BuildingOfficeIcon,
    CubeIcon, ArrowsRightLeftIcon, UsersIcon, KeyIcon,
    ServerIcon, CloudArrowDownIcon, AtSymbolIcon, ShieldCheckIcon,
    TrashIcon, PlusIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const { success, error: notifyError } = useNotifications();
const { route } = useStockflowRoute();

interface Props {
    company: Record<string, any> | null;
    profile: Record<string, any> | null;
    backup: Record<string, any> | null;
    domains: Record<string, any>[];
    emailSettings: Record<string, any> | null;
    apiKeys: Record<string, any>[];
}

const props = defineProps<Props>();

const profileForm = useForm({
    name: props.profile?.name ?? '',
    email: props.profile?.email ?? '',
    current_password: '',
    password: '',
    password_confirmation: '',
});

const defaultFeatures = {
    items: true, customers: true, sales: true, purchases: true, cash: true,
    movements: true, counts: true, audits: true, suppliers: true,
    departments: true, branches: true, bins: true, employees: true, roles: true,
    reports: true, quotations: true, invoices: true, receipts: true,
    tax: true, currency: true, warehouses: true, lots: true,
    requisitions: true, payments: true,
};

const companySettings = props.company?.settings ?? {};
const currentFeatures = companySettings.features_enabled ?? {};

const companyForm = useForm({
    name: props.company?.name ?? '',
    tagline: props.company?.tagline ?? '',
    brand_color: props.company?.brand_color ?? '#059669',
    email: props.company?.email ?? '',
    phone: props.company?.phone ?? '',
    address: props.company?.address ?? '',
    city: props.company?.city ?? '',
    country: props.company?.country ?? '',
    contact_person: props.company?.contact_person ?? '',
    currency: props.company?.currency ?? 'ZMW',
    features_enabled: { ...defaultFeatures, ...currentFeatures },
});

const isSubdomain = (page.props.routeName ?? '').startsWith('stockflow.sub.');

const updateProfile = () => {
    profileForm.put(isSubdomain ? '/settings/profile' : '/stockflow/settings/profile', {
        preserveScroll: true,
        onSuccess: () => {
            profileForm.reset('current_password', 'password', 'password_confirmation');
            success('Profile updated successfully');
        },
        onError: (errors) => {
            if (errors.profile) notifyError(errors.profile);
        },
    });
};

const updateCompany = () => {
    companyForm.put(isSubdomain ? '/settings/company' : '/stockflow/settings/company', {
        preserveScroll: true,
        onSuccess: () => success('Company settings updated'),
    });
};

// Backup
const backupForm = useForm({
    email: props.backup?.email ?? '',
    enabled: props.backup?.enabled ?? false,
    frequency: props.backup?.frequency ?? 'daily',
});

const saveBackup = () => {
    backupForm.post(isSubdomain ? '/settings/backup' : '/stockflow/settings/backup', {
        preserveScroll: true,
        onSuccess: () => success('Backup settings saved'),
        onError: (errors) => {
            if (errors.backup) notifyError(errors.backup);
        },
    });
};

// Custom Domain
const newDomain = ref('');

const addDomain = () => {
    if (!newDomain.value) return;
    router.post(isSubdomain ? '/settings/domains' : '/stockflow/settings/domains', {
        domain: newDomain.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { newDomain.value = ''; success('Domain added'); },
        onError: (errors) => { if (errors.domain) notifyError(errors.domain); },
    });
};

const deleteDomain = (id: number) => {
    router.delete(isSubdomain ? `/settings/domains/${id}` : `/stockflow/settings/domains/${id}`, {
        preserveScroll: true,
        onSuccess: () => success('Domain removed'),
    });
};

// Email Settings
const emailForm = useForm({
    provider: props.emailSettings?.provider ?? 'smtp',
    smtp_host: props.emailSettings?.smtp_host ?? '',
    smtp_port: props.emailSettings?.smtp_port ?? 587,
    smtp_username: props.emailSettings?.smtp_username ?? '',
    smtp_password: '',
    smtp_encryption: props.emailSettings?.smtp_encryption ?? 'tls',
    from_address: props.emailSettings?.from_address ?? '',
    from_name: props.emailSettings?.from_name ?? '',
});

const saveEmailSettings = () => {
    emailForm.post(isSubdomain ? '/settings/email' : '/stockflow/settings/email', {
        preserveScroll: true,
        onSuccess: () => {
            emailForm.smtp_password = '';
            success('Email settings saved');
        },
        onError: (errors) => {
            if (errors.email_settings) notifyError(errors.email_settings);
        },
    });
};

// API Keys
const newApiKeyName = ref('');

const generateApiKey = () => {
    if (!newApiKeyName.value) return;
    router.post(isSubdomain ? '/settings/api-keys' : '/stockflow/settings/api-keys', {
        name: newApiKeyName.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { newApiKeyName.value = ''; success('API key generated'); },
        onError: (errors) => { if (errors.api_key) notifyError(errors.api_key); },
    });
};

const revokeApiKey = (id: number) => {
    router.post(isSubdomain ? `/settings/api-keys/${id}/revoke` : `/stockflow/settings/api-keys/${id}/revoke`, {}, {
        preserveScroll: true,
        onSuccess: () => success('API key revoked'),
    });
};

const currentOrigin = window.location.origin;

const providerPresets: Record<string, { host: string; port: number; encryption: string }> = {
    zoho: { host: 'smtp.zoho.com', port: 587, encryption: 'tls' },
    brevo: { host: 'smtp-relay.brevo.com', port: 587, encryption: 'tls' },
    gmail: { host: 'smtp.gmail.com', port: 587, encryption: 'tls' },
    sendgrid: { host: 'smtp.sendgrid.net', port: 587, encryption: 'tls' },
    mailgun: { host: 'smtp.mailgun.org', port: 587, encryption: 'tls' },
};

const applyProviderPreset = () => {
    const preset = providerPresets[emailForm.provider];
    if (preset) {
        emailForm.smtp_host = preset.host;
        emailForm.smtp_port = preset.port;
        emailForm.smtp_encryption = preset.encryption;
    }
};
</script>

<template>
    <StockFlowLayout title="Settings">
        <Head title="Settings" />

        <div class="mx-auto max-w-3xl space-y-8 py-8 px-4 sm:px-6 lg:px-8">
            <!-- Profile Section -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <UserCircleIcon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Profile</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Your personal account information</p>
                </div>
                <form @submit.prevent="updateProfile" class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="profileForm.name" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="profileForm.errors.name" class="mt-1 text-xs text-red-600">{{ profileForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="profileForm.email" type="email" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="profileForm.errors.email" class="mt-1 text-xs text-red-600">{{ profileForm.errors.email }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Change Password</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input v-model="profileForm.current_password" type="password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <p v-if="profileForm.errors.current_password" class="mt-1 text-xs text-red-600">{{ profileForm.errors.current_password }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Password</label>
                                <input v-model="profileForm.password" type="password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <p v-if="profileForm.errors.password" class="mt-1 text-xs text-red-600">{{ profileForm.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input v-model="profileForm.password_confirmation" type="password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button type="submit" :disabled="profileForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50">
                            <CheckCircleIcon class="h-4 w-4" />
                            {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Company Section -->
            <div v-if="company" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <BuildingOffice2Icon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Company Settings</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Manage your company branding and contact information</p>
                </div>
                <form @submit.prevent="updateCompany" class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input v-model="companyForm.name" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="companyForm.errors.name" class="mt-1 text-xs text-red-600">{{ companyForm.errors.name }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Tagline</label>
                            <input v-model="companyForm.tagline" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Brand Color</label>
                            <div class="mt-1 flex items-center gap-3">
                                <input v-model="companyForm.brand_color" type="color" class="h-9 w-9 cursor-pointer rounded-lg border border-gray-300" />
                                <input v-model="companyForm.brand_color" type="text" class="block w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select v-model="companyForm.currency" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="ZMW">ZMW (Zambian Kwacha)</option>
                                <option value="MWK">MWK (Malawian Kwacha)</option>
                                <option value="USD">USD (US Dollar)</option>
                                <option value="EUR">EUR (Euro)</option>
                                <option value="GBP">GBP (British Pound)</option>
                                <option value="ZAR">ZAR (South African Rand)</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-3">
                            <GlobeAltIcon class="h-4 w-4" />
                            Contact Information
                        </h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input v-model="companyForm.email" type="email" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input v-model="companyForm.phone" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                                <input v-model="companyForm.contact_person" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">City</label>
                                <input v-model="companyForm.city" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Country</label>
                                <input v-model="companyForm.country" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea v-model="companyForm.address" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button type="submit" :disabled="companyForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50">
                            <CheckCircleIcon class="h-4 w-4" />
                            {{ companyForm.processing ? 'Saving...' : 'Save Company Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Backups Section -->
            <div v-if="company" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <CloudArrowDownIcon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Daily Backups</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Enable daily email backups of your inventory data. A CSV backup will be sent to your email every morning.</p>
                </div>
                <form @submit.prevent="saveBackup" class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Backup Email</label>
                            <input v-model="backupForm.email" type="email" placeholder="backup@example.com" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="backupForm.errors.email" class="mt-1 text-xs text-red-600">{{ backupForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Frequency</label>
                            <select v-model="backupForm.frequency" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="backupForm.enabled = !backupForm.enabled" role="switch" :aria-checked="backupForm.enabled" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" :class="backupForm.enabled ? 'bg-emerald-600' : 'bg-gray-200'">
                            <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition" :class="backupForm.enabled ? 'translate-x-5' : 'translate-x-0'">
                                <span v-if="backupForm.enabled" class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity">
                                    <CheckCircleIcon class="h-3 w-3 text-emerald-600" />
                                </span>
                            </span>
                        </button>
                        <span class="text-sm font-medium text-gray-700">{{ backupForm.enabled ? 'Backups are enabled' : 'Backups are disabled' }}</span>
                    </div>
                    <div v-if="backup?.last_backup_at" class="text-xs text-gray-500">Last backup: {{ backup.last_backup_at }}</div>
                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button type="submit" :disabled="backupForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50">
                            <CloudArrowDownIcon class="h-4 w-4" />
                            {{ backupForm.processing ? 'Saving...' : 'Save Backup Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Custom Domain Section -->
            <div v-if="company" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <ServerIcon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Custom Domain</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Add your own domain to access StockFlow from your company's web address. Point your domain's DNS to our server.</p>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="flex gap-3">
                        <input v-model="newDomain" type="text" placeholder="stockflow.yourcompany.com" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <button @click="addDomain" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                            <PlusIcon class="h-4 w-4" />
                            Add
                        </button>
                    </div>

                    <div v-if="domains.length" class="space-y-3">
                        <div v-for="domain in domains" :key="domain.id" class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ domain.domain }}</p>
                                <p class="text-xs text-gray-500">
                                    Status:
                                    <span :class="domain.status === 'verified' ? 'text-emerald-600' : 'text-amber-600'">
                                        {{ domain.status }}
                                    </span>
                                    <span v-if="domain.status === 'pending'" class="ml-2 text-gray-400">— Point an A record to your server IP or a CNAME to mygrownet.com</span>
                                </p>
                            </div>
                            <button @click="deleteDomain(domain.id)" class="rounded p-1.5 text-gray-400 hover:bg-red-100 hover:text-red-600">
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No custom domains added yet.</p>
                </div>
            </div>

            <!-- Email Settings Section -->
            <div v-if="company" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <AtSymbolIcon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Email Settings</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Configure SMTP settings to send emails from your own domain. Supports Zoho, Brevo, Gmail, SendGrid, Mailgun, or any custom SMTP.</p>
                </div>
                <form @submit.prevent="saveEmailSettings" class="px-6 py-5 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Provider</label>
                        <select v-model="emailForm.provider" @change="applyProviderPreset" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="smtp">Custom SMTP</option>
                            <option value="zoho">Zoho Mail</option>
                            <option value="brevo">Brevo (Sendinblue)</option>
                            <option value="gmail">Gmail / Google Workspace</option>
                            <option value="sendgrid">SendGrid</option>
                            <option value="mailgun">Mailgun</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                            <input v-model="emailForm.smtp_host" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SMTP Port</label>
                            <input v-model.number="emailForm.smtp_port" type="number" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SMTP Username</label>
                            <input v-model="emailForm.smtp_username" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SMTP Password</label>
                            <input v-model="emailForm.smtp_password" type="password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Encryption</label>
                            <select v-model="emailForm.smtp_encryption" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="null">None</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Address</label>
                            <input v-model="emailForm.from_address" type="email" placeholder="noreply@yourdomain.com" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Name</label>
                            <input v-model="emailForm.from_name" type="text" placeholder="Your Company" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button type="submit" :disabled="emailForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50">
                            <AtSymbolIcon class="h-4 w-4" />
                            {{ emailForm.processing ? 'Saving...' : 'Save Email Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- API Keys Section -->
            <div v-if="company" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <KeyIcon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">API Keys</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Generate API keys to connect StockFlow with external systems. Use the key in the Authorization header as a Bearer token.</p>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="flex gap-3">
                        <input v-model="newApiKeyName" type="text" placeholder="e.g. Integration with my website" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <button @click="generateApiKey" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                            <PlusIcon class="h-4 w-4" />
                            Generate
                        </button>
                    </div>

                    <div v-if="apiKeys.length" class="space-y-3">
                        <div v-for="key in apiKeys" :key="key.id" class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ key.name }}</p>
                                <p class="text-xs text-gray-500">
                                    Key: <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ key.key.substring(0, 16) }}...</code>
                                    <span v-if="key.active" class="ml-2 text-emerald-600">Active</span>
                                    <span v-else class="ml-2 text-red-600">Revoked</span>
                                    <span v-if="key.last_used_at" class="ml-2">Last used: {{ key.last_used_at }}</span>
                                </p>
                            </div>
                            <button v-if="key.active" @click="revokeApiKey(key.id)" class="rounded p-1.5 text-gray-400 hover:bg-red-100 hover:text-red-600" title="Revoke">
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No API keys generated yet.</p>

                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="text-sm font-medium text-gray-700">API Usage</h4>
                        <p class="mt-1 text-xs text-gray-500">Send requests to <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono">{{ isSubdomain ? currentOrigin : 'https://mygrownet.com' }}/api/stockflow/...</code> with header <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono">Authorization: Bearer YOUR_API_KEY</code></p>
                        <p class="mt-1 text-xs text-gray-500">Available endpoints: GET/POST /items, /sales, /invoices, /quotations, /purchases</p>
                    </div>
                </div>
            </div>

            <!-- Feature Toggles Section -->
            <div v-if="company" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <LockClosedIcon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Feature Toggles</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Enable or disable modules for your company.</p>
                </div>
                <form @submit.prevent="updateCompany" class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div v-for="(enabled, key) in companyForm.features_enabled" :key="key" class="relative flex items-center justify-between gap-3 rounded-xl border p-4 transition-colors" :class="enabled ? 'border-emerald-200 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50'">
                            <div>
                                <p class="text-sm font-medium capitalize" :class="enabled ? 'text-gray-900' : 'text-gray-500'">{{ key }}</p>
                            </div>
                            <button type="button" role="switch" :aria-checked="enabled" @click="companyForm.features_enabled[key] = !enabled" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" :class="enabled ? 'bg-emerald-600' : 'bg-gray-200'">
                                <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition" :class="enabled ? 'translate-x-5' : 'translate-x-0'">
                                    <span v-if="enabled" class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity">
                                        <CheckCircleIcon class="h-3 w-3 text-emerald-600" />
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button type="submit" :disabled="companyForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50">
                            <CheckCircleIcon class="h-4 w-4" />
                            {{ companyForm.processing ? 'Saving...' : 'Save Feature Settings' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
