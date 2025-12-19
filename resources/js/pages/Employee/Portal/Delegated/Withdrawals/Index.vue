<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { MagnifyingGlassIcon, BanknotesIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Withdrawal {
    id: number;
    user: { id: number; name: string; email: string } | null;
    amount: string;
    net_amount: string;
    status: string;
    payment_method: string;
    created_at: string;
    requested_at: string;
}

interface Props {
    employee: any;
    withdrawals: { data: Withdrawal[]; links: any; meta: any };
    stats: { pending: number; approved_today: number; total_pending_amount: number };
    filters: { search?: string; status?: string };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('employee.portal.delegated.withdrawals.index'), { search: search.value, status: status.value }, { preserveState: true });
};

const getStatusClass = (s: string) => ({
    'pending': 'bg-amber-100 text-amber-700',
    'approved': 'bg-blue-100 text-blue-700',
    'processed': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
}[s] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head title="Delegated - Withdrawals" />
    <EmployeePortalLayout>
        <template #header>Withdrawals</template>
        
        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg"><ClockIcon class="h-5 w-5 text-amber-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.pending }}</p>
                            <p class="text-sm text-gray-500">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg"><CheckCircleIcon class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.approved_today }}</p>
                            <p class="text-sm text-gray-500">Approved Today</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg"><BanknotesIcon class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">K{{ Number(stats.total_pending_amount).toLocaleString() }}</p>
                            <p class="text-sm text-gray-500">Pending Amount</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                            <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                    </div>
                    <select v-model="status" @change="applyFilters" class="border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="processed">Processed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <!-- Withdrawals Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="withdrawal in withdrawals.data" :key="withdrawal.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ withdrawal.user?.name || 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ withdrawal.user?.email }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">K{{ Number(withdrawal.amount).toLocaleString() }}</td>
                            <td class="px-6 py-4 text-sm text-green-600 font-medium">K{{ Number(withdrawal.net_amount).toLocaleString() }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ withdrawal.payment_method?.replace('_', ' ') || 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full capitalize', getStatusClass(withdrawal.status)]">{{ withdrawal.status }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(withdrawal.requested_at || withdrawal.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('employee.portal.delegated.withdrawals.show', withdrawal.id)" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View</Link>
                            </td>
                        </tr>
                        <tr v-if="withdrawals.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No withdrawals found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
