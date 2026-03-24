<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    DocumentTextIcon,
    UserGroupIcon,
    ChartBarIcon,
    PlusIcon,
    ReceiptPercentIcon,
    ClipboardDocumentListIcon,
    TruckIcon,
    ArrowRightIcon,
    BuildingOfficeIcon,
    PencilIcon,
    CheckCircleIcon,
    Cog6ToothIcon,
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

interface Props {
    businessProfile?: any;
}

const props = defineProps<Props>();
const page = usePage();
const successMessage = computed(() => page.props.flash?.success);

const stats = [
    {
        name: 'Total Documents',
        value: '0',
        sub: 'All time',
        icon: DocumentTextIcon,
        iconBg: 'bg-slate-100',
        iconColor: 'text-slate-600',
    },
    {
        name: 'Customers',
        value: '0',
        sub: 'In database',
        icon: UserGroupIcon,
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
    },
    {
        name: 'This Month',
        value: 'K 0.00',
        sub: 'Revenue billed',
        icon: ChartBarIcon,
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
    },
];

const quickActions = [
    {
        name: 'New Invoice',
        description: 'Bill a customer',
        href: '/bizdocs/documents/create?type=invoice',
        icon: DocumentTextIcon,
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
        border: 'hover:border-blue-300',
    },
    {
        name: 'New Receipt',
        description: 'Record payment',
        href: '/bizdocs/documents/create?type=receipt',
        icon: ReceiptPercentIcon,
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
        border: 'hover:border-emerald-300',
    },
    {
        name: 'New Quotation',
        description: 'Send a quote',
        href: '/bizdocs/documents/create?type=quotation',
        icon: ClipboardDocumentListIcon,
        iconBg: 'bg-violet-50',
        iconColor: 'text-violet-600',
        border: 'hover:border-violet-300',
    },
    {
        name: 'Proforma Invoice',
        description: 'Preliminary bill',
        href: '/bizdocs/documents/create?type=proforma_invoice',
        icon: DocumentTextIcon,
        iconBg: 'bg-indigo-50',
        iconColor: 'text-indigo-600',
        border: 'hover:border-indigo-300',
    },
    {
        name: 'Add Customer',
        description: 'Manage contacts',
        href: '/bizdocs/customers',
        icon: UserGroupIcon,
        iconBg: 'bg-amber-50',
        iconColor: 'text-amber-600',
        border: 'hover:border-amber-300',
    },
    {
        name: 'Settings',
        description: 'Document numbering',
        href: '/bizdocs/settings/numbering',
        icon: Cog6ToothIcon,
        iconBg: 'bg-slate-50',
        iconColor: 'text-slate-600',
        border: 'hover:border-slate-300',
    },
];
</script>

<template>
    <Head title="BizDocs Dashboard" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-7xl mx-auto">

                <!-- Page header -->
                <div class="flex items-start justify-between mb-7">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                                <span class="w-5 h-px bg-blue-500 inline-block"></span>
                                BizDocs
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 leading-none">Dashboard</h1>
                        <p class="text-sm text-slate-400 mt-1">Your document activity at a glance</p>
                    </div>

                    <Link
                        href="/bizdocs/documents/create?type=invoice"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        <PlusIcon class="w-4 h-4" />
                        New Document
                    </Link>
                </div>

                <!-- Success Message -->
                <div v-if="successMessage" class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-emerald-900">{{ successMessage }}</p>
                    </div>
                </div>

                <!-- Stats strip -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                    <div
                        v-for="stat in stats"
                        :key="stat.name"
                        class="bg-white border border-slate-200 rounded-xl px-5 py-4 flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                            :class="stat.iconBg">
                            <component :is="stat.icon" class="w-5 h-5" :class="stat.iconColor" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900 leading-none">{{ stat.value }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ stat.name }}</p>
                            <p class="text-xs text-slate-300 mt-0.5">{{ stat.sub }}</p>
                        </div>
                    </div>
                </div>

                <!-- Business Profile Card (if exists) -->
                <div v-if="businessProfile" class="bg-gradient-to-br from-blue-50 to-slate-50 border border-blue-100 rounded-xl p-5 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                <BuildingOfficeIcon class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 mb-1">{{ businessProfile.businessName }}</h3>
                                <p class="text-sm text-slate-600 mb-2">{{ businessProfile.address }}</p>
                                <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                                    <span v-if="businessProfile.phone">📞 {{ businessProfile.phone }}</span>
                                    <span v-if="businessProfile.email">✉️ {{ businessProfile.email }}</span>
                                    <span v-if="businessProfile.tpin">🏛️ TPIN: {{ businessProfile.tpin }}</span>
                                </div>
                            </div>
                        </div>
                        <Link
                            href="/bizdocs/profile/edit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 hover:border-blue-300 transition-all">
                            <PencilIcon class="w-3.5 h-3.5" />
                            Edit Profile
                        </Link>
                    </div>
                </div>

                <!-- Setup prompt (if no profile) -->
                <div v-else class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center flex-shrink-0">
                            <BuildingOfficeIcon class="w-5 h-5 text-white" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-amber-900 mb-1">Complete Your Business Profile</h3>
                            <p class="text-xs text-amber-700 mb-3">
                                Set up your business details to start creating professional documents.
                            </p>
                            <Link
                                href="/bizdocs/setup"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-semibold rounded-lg hover:bg-amber-700 transition-all">
                                <PlusIcon class="w-3.5 h-3.5" />
                                Setup Now
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Two-column body -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <!-- Quick Actions (1/3) -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">Quick Actions</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                        </div>

                        <div class="p-3 space-y-2">
                            <Link
                                v-for="action in quickActions"
                                :key="action.name"
                                :href="action.href"
                                class="group flex items-center gap-3 p-3 rounded-lg border border-transparent hover:border-slate-200 hover:bg-slate-50 transition-all duration-150">
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                                    :class="action.iconBg">
                                    <component :is="action.icon" class="w-4 h-4" :class="action.iconColor" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-800 leading-none">{{ action.name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ action.description }}</p>
                                </div>
                                <ArrowRightIcon class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-500 group-hover:translate-x-0.5 transition-all flex-shrink-0" />
                            </Link>
                        </div>

                        <!-- View All link -->
                        <div class="px-5 py-3 border-t border-slate-100">
                            <Link
                                href="/bizdocs/documents"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                                View all documents →
                            </Link>
                        </div>
                    </div>

                    <!-- Recent Documents (2/3) -->
                    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">Recent Documents</span>
                                <span class="flex-1 h-px bg-slate-100 w-8"></span>
                            </div>
                            <Link
                                href="/bizdocs/documents"
                                class="text-xs font-semibold text-slate-400 hover:text-blue-600 transition-colors">
                                View all →
                            </Link>
                        </div>

                        <!-- Empty state -->
                        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                            <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mb-4">
                                <DocumentTextIcon class="w-6 h-6 text-slate-400" />
                            </div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-1">No documents yet</h3>
                            <p class="text-xs text-slate-400 mb-5 max-w-xs">
                                Create your first invoice, receipt, or quotation to see it here.
                            </p>
                            <Link
                                href="/bizdocs/documents/create?type=invoice"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-blue-600 transition-all duration-200">
                                <PlusIcon class="w-3.5 h-3.5" />
                                Create First Document
                            </Link>
                        </div>

                        <!--
                            When documents exist, replace the empty state above with a table:

                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Document #</th>
                                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Customer</th>
                                            <th class="px-5 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Amount</th>
                                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        ... rows ...
                                    </tbody>
                                </table>
                            </div>
                        -->
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>