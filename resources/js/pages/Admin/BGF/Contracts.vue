<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CustomAppSidebarLayout from '@/layouts/admin/CustomAppSidebarLayout.vue';
import { FileText, CheckCircle, Clock, XCircle, Download } from 'lucide-vue-next';

interface Contract {
    id: number;
    contract_number: string;
    funding_amount: number;
    member_contribution: number;
    member_profit_percentage: number;
    mygrownet_profit_percentage: number;
    start_date: string;
    end_date: string;
    status: string;
    member_signed_at: string | null;
    mygrownet_signed_at: string | null;
    contract_pdf_url: string | null;
    created_at: string;
    project: {
        project_number: string;
        user: {
            name: string;
        };
        application: {
            business_name: string;
        };
    };
}

defineProps<{
    contracts: {
        data: Contract[];
    };
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        pending_member_signature: 'bg-yellow-100 text-yellow-800',
        pending_mygrownet_signature: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        completed: 'bg-emerald-100 text-emerald-800',
        terminated: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'active':
        case 'completed':
            return CheckCircle;
        case 'pending_member_signature':
        case 'pending_mygrownet_signature':
            return Clock;
        case 'terminated':
            return XCircle;
        default:
            return FileText;
    }
};

const isFullySigned = (contract: Contract) => {
    return contract.member_signed_at && contract.mygrownet_signed_at;
};
</script>

<template>
    <Head title="BGF Contracts" />

    <CustomAppSidebarLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Contracts</h1>
                <p class="mt-2 text-gray-600">Manage project funding contracts</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <FileText class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">Total Contracts</p>
                            <p class="text-2xl font-bold text-gray-900">{{ contracts.data.length }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <CheckCircle class="h-8 w-8 text-green-600" />
                        <div>
                            <p class="text-sm text-gray-600">Active</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ contracts.data.filter(c => c.status === 'active').length }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <Clock class="h-8 w-8 text-yellow-600" />
                        <div>
                            <p class="text-sm text-gray-600">Pending Signature</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ contracts.data.filter(c => c.status.includes('pending')).length }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <FileText class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">Total Value</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ formatCurrency(contracts.data.reduce((sum, c) => sum + c.funding_amount, 0)) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contract #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funding</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit Split</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Signatures</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="contracts.data.length === 0">
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                No contracts found
                            </td>
                        </tr>
                        <tr
                            v-for="contract in contracts.data"
                            :key="contract.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ contract.contract_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ contract.project.project_number }}</div>
                                <div class="text-sm text-gray-500">{{ contract.project.application.business_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ contract.project.user.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(contract.funding_amount) }}</div>
                                <div class="text-xs text-gray-500">+ {{ formatCurrency(contract.member_contribution) }} member</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="font-semibold text-blue-600">{{ contract.member_profit_percentage }}%</span> Member
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ contract.mygrownet_profit_percentage }}% MyGrowNet
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ new Date(contract.start_date).toLocaleDateString() }}</div>
                                <div class="text-xs text-gray-500">to {{ new Date(contract.end_date).toLocaleDateString() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <CheckCircle 
                                        :class="contract.member_signed_at ? 'text-green-600' : 'text-gray-300'" 
                                        class="h-4 w-4" 
                                        :title="contract.member_signed_at ? 'Member signed' : 'Pending member signature'"
                                    />
                                    <CheckCircle 
                                        :class="contract.mygrownet_signed_at ? 'text-green-600' : 'text-gray-300'" 
                                        class="h-4 w-4"
                                        :title="contract.mygrownet_signed_at ? 'MyGrowNet signed' : 'Pending MyGrowNet signature'"
                                    />
                                </div>
                                <div v-if="isFullySigned(contract)" class="text-xs text-green-600 mt-1">
                                    Fully signed
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full', getStatusColor(contract.status)]">
                                    <component :is="getStatusIcon(contract.status)" class="h-3 w-3" />
                                    {{ contract.status.replace(/_/g, ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a
                                    v-if="contract.contract_pdf_url"
                                    :href="contract.contract_pdf_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800"
                                >
                                    <Download class="h-4 w-4" />
                                    PDF
                                </a>
                                <span v-else class="text-gray-400">No PDF</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CustomAppSidebarLayout>
</template>
