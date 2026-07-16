<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import {
    UserCircleIcon, BuildingOffice2Icon, EnvelopeIcon,
    PhoneIcon, MapPinIcon, GlobeAltIcon, CurrencyDollarIcon,
    PaintBrushIcon, CheckCircleIcon, LockClosedIcon,
    ArchiveBoxIcon, ShoppingCartIcon, CreditCardIcon,
    CurrencyDollarIcon as CashIcon, ClipboardDocumentListIcon,
    DocumentTextIcon, BuildingStorefrontIcon, BuildingOfficeIcon,
    CubeIcon, ArrowsRightLeftIcon, UsersIcon, KeyIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const { success, error: notifyError } = useNotifications();

const { route } = useStockflowRoute();

interface Props {
    company: Record<string, any> | null;
    profile: Record<string, any> | null;
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
    items: true, sales: true, purchases: true, cash: true,
    movements: true, counts: true, audits: true, suppliers: true,
    departments: true, bins: true, employees: true, roles: true,
    reports: true,
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
                            <input
                                v-model="profileForm.name"
                                type="text"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="profileForm.errors.name" class="mt-1 text-xs text-red-600">{{ profileForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="profileForm.errors.email" class="mt-1 text-xs text-red-600">{{ profileForm.errors.email }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Change Password</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input
                                    v-model="profileForm.current_password"
                                    type="password"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                                <p v-if="profileForm.errors.current_password" class="mt-1 text-xs text-red-600">{{ profileForm.errors.current_password }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Password</label>
                                <input
                                    v-model="profileForm.password"
                                    type="password"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                                <p v-if="profileForm.errors.password" class="mt-1 text-xs text-red-600">{{ profileForm.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input
                                    v-model="profileForm.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50"
                        >
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
                            <input
                                v-model="companyForm.name"
                                type="text"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="companyForm.errors.name" class="mt-1 text-xs text-red-600">{{ companyForm.errors.name }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Tagline</label>
                            <input
                                v-model="companyForm.tagline"
                                type="text"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Brand Color</label>
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    v-model="companyForm.brand_color"
                                    type="color"
                                    class="h-9 w-9 cursor-pointer rounded-lg border border-gray-300"
                                />
                                <input
                                    v-model="companyForm.brand_color"
                                    type="text"
                                    class="block w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select
                                v-model="companyForm.currency"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
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
                                <input
                                    v-model="companyForm.email"
                                    type="email"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input
                                    v-model="companyForm.phone"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                                <input
                                    v-model="companyForm.contact_person"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">City</label>
                                <input
                                    v-model="companyForm.city"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Country</label>
                                <input
                                    v-model="companyForm.country"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea
                                    v-model="companyForm.address"
                                    rows="2"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button
                            type="submit"
                            :disabled="companyForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50"
                        >
                            <CheckCircleIcon class="h-4 w-4" />
                            {{ companyForm.processing ? 'Saving...' : 'Save Company Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Feature Toggles Section -->
            <div v-if="company" class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <LockClosedIcon class="h-6 w-6 text-emerald-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Feature Toggles</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Enable or disable modules for your company. Disabled features will be hidden from navigation and inaccessible.</p>
                </div>
                <form @submit.prevent="updateCompany" class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div v-for="(enabled, key) in companyForm.features_enabled" :key="key" class="relative flex items-center justify-between gap-3 rounded-xl border p-4 transition-colors" :class="enabled ? 'border-emerald-200 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50'">
                            <div>
                                <p class="text-sm font-medium capitalize" :class="enabled ? 'text-gray-900' : 'text-gray-500'">{{ key }}</p>
                            </div>
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="enabled"
                                @click="companyForm.features_enabled[key] = !enabled"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                :class="enabled ? 'bg-emerald-600' : 'bg-gray-200'"
                            >
                                <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition" :class="enabled ? 'translate-x-5' : 'translate-x-0'">
                                    <span v-if="enabled" class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity">
                                        <CheckCircleIcon class="h-3 w-3 text-emerald-600" />
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5">
                        <button
                            type="submit"
                            :disabled="companyForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50"
                        >
                            <CheckCircleIcon class="h-4 w-4" />
                            {{ companyForm.processing ? 'Saving...' : 'Save Feature Settings' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
