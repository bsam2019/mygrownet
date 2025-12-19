<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { DocumentTextIcon, UserGroupIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';

interface ShareCertificate {
    id: number;
    certificate_number: string;
    shares: number;
    issue_date: string;
}

interface Investor {
    id: number;
    name: string;
    email: string;
    investment_amount: string;
    equity_percentage: string;
    status: string;
    share_certificates: ShareCertificate[];
}

interface Props {
    employee: any;
    investors: Investor[];
}

defineProps<Props>();
</script>

<template>
    <Head title="Delegated - Investor Documents" />
    <EmployeePortalLayout>
        <template #header>Investor Documents</template>
        
        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg"><UserGroupIcon class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ investors.length }}</p>
                            <p class="text-sm text-gray-500">Active Investors</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg"><DocumentTextIcon class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ investors.reduce((sum, i) => sum + (i.share_certificates?.length || 0), 0) }}</p>
                            <p class="text-sm text-gray-500">Share Certificates</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg"><DocumentArrowDownIcon class="h-5 w-5 text-purple-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">K{{ investors.reduce((sum, i) => sum + Number(i.investment_amount || 0), 0).toLocaleString() }}</p>
                            <p class="text-sm text-gray-500">Total Investment</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investors with Documents -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Investor Documents</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="investor in investors" :key="investor.id" class="p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ investor.name }}</h3>
                                <p class="text-sm text-gray-500">{{ investor.email }}</p>
                                <div class="flex gap-4 mt-2 text-sm">
                                    <span class="text-blue-600">K{{ Number(investor.investment_amount).toLocaleString() }}</span>
                                    <span class="text-gray-500">{{ Number(investor.equity_percentage).toFixed(2) }}% equity</span>
                                </div>
                            </div>
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full capitalize', investor.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700']">
                                {{ investor.status }}
                            </span>
                        </div>
                        
                        <!-- Share Certificates -->
                        <div v-if="investor.share_certificates?.length" class="mt-4 pl-4 border-l-2 border-gray-200">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Share Certificates</p>
                            <div class="space-y-2">
                                <div v-for="cert in investor.share_certificates" :key="cert.id" class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ cert.certificate_number }}</p>
                                        <p class="text-xs text-gray-500">{{ cert.shares }} shares • Issued {{ new Date(cert.issue_date).toLocaleDateString() }}</p>
                                    </div>
                                    <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View</button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mt-3 text-sm text-gray-400 italic">No documents</div>
                    </div>
                    <div v-if="investors.length === 0" class="p-8 text-center text-gray-500">
                        No investors found
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
