<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { EnvelopeIcon, UserGroupIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

interface Investor {
    id: number;
    name: string;
    email: string;
    investment_amount: string;
    status: string;
}

interface Ticket {
    id: number;
    investor_account_id: number;
    investorAccount: { id: number; name: string; email: string } | null;
    subject: string;
    status: string;
    priority: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    employee: any;
    investors: Investor[];
    tickets: Ticket[];
}

defineProps<Props>();

const getStatusClass = (s: string) => ({
    'open': 'bg-amber-100 text-amber-700',
    'in_progress': 'bg-blue-100 text-blue-700',
    'resolved': 'bg-green-100 text-green-700',
    'closed': 'bg-gray-100 text-gray-700',
}[s] || 'bg-gray-100 text-gray-700');

const getPriorityClass = (p: string) => ({
    'low': 'text-gray-500',
    'medium': 'text-amber-600',
    'high': 'text-red-600',
}[p] || 'text-gray-500');
</script>

<template>
    <Head title="Delegated - Investor Messages" />
    <EmployeePortalLayout>
        <template #header>Investor Messages</template>
        
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
                        <div class="p-2 bg-amber-100 rounded-lg"><EnvelopeIcon class="h-5 w-5 text-amber-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ tickets.filter(t => t.status === 'open').length }}</p>
                            <p class="text-sm text-gray-500">Open Tickets</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg"><ChatBubbleLeftRightIcon class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ tickets.length }}</p>
                            <p class="text-sm text-gray-500">Total Messages</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Tickets List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="p-4 border-b border-gray-100">
                            <h2 class="font-semibold text-gray-900">Investor Support Tickets</h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="ticket in tickets" :key="ticket.id" class="p-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <Link :href="route('employee.portal.delegated.support.show', ['investor', ticket.id])" class="font-medium text-gray-900 hover:text-blue-600">
                                                {{ ticket.subject }}
                                            </Link>
                                            <span :class="['px-2 py-0.5 text-xs font-medium rounded-full capitalize', getStatusClass(ticket.status)]">
                                                {{ ticket.status.replace('_', ' ') }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">
                                            From: {{ ticket.investorAccount?.name || 'Unknown' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span :class="['text-xs font-medium capitalize', getPriorityClass(ticket.priority)]">{{ ticket.priority }}</span>
                                        <p class="text-xs text-gray-400 mt-1">{{ new Date(ticket.updated_at).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="tickets.length === 0" class="p-8 text-center text-gray-500">
                                No investor tickets found
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investors List -->
                <div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="p-4 border-b border-gray-100">
                            <h2 class="font-semibold text-gray-900">Active Investors</h2>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                            <div v-for="investor in investors" :key="investor.id" class="p-3">
                                <p class="font-medium text-gray-900 text-sm">{{ investor.name }}</p>
                                <p class="text-xs text-gray-500">{{ investor.email }}</p>
                                <p class="text-xs text-blue-600 mt-1">K{{ Number(investor.investment_amount).toLocaleString() }}</p>
                            </div>
                            <div v-if="investors.length === 0" class="p-4 text-center text-gray-500 text-sm">
                                No active investors
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
