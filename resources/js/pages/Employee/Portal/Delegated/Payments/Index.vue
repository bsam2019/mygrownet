<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { MagnifyingGlassIcon, CreditCardIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Payment {
    id: number;
    user: { id: number; name: string; email: string } | null;
    type: string;
    amount: string;
    status: string;
    payment_method: string;
    reference: string;
    created_at: string;
}

interface Props {
    employee: any;
    payments: { data: Payment[]; links: any; meta: any };
    stats: { pending: number; completed_today: number; total_today: number };
    filters: { search?: string; status?: string };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('employee.portal.delegated.payments.index'), { search: search.value, status: status.value }, { preserveState: true });
};

const getStatusClass = (s: string) => ({
    'pending': 'bg-amber-100 text-amber-700',
    'processing': 'bg-blue-100 text-blue-700',
    'completed': 'bg-green-100 text-green-700',
    'failed': 'bg-red-100 text-red-700',
    'cancelled': 'bg-gray-100 text-gray-700',
}[s] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head title="Delegated - Payments" />
    <EmployeePortalLayout>
        <template #header>Payments</template>
        
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
                            <p class="text-2xl font-bold text-gray-900">{{ stats.completed_today }}</p>
                            <p class="text-sm text-gray-500">Completed Today</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg"><CreditCardIcon class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">K{{ Number(stats.total_today).toLocaleString() }}</p>
                            <p class="text-sm text-gray-500">Total Today</p>
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
                            <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search by reference or user..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                    </div>
                    <select v-model="status" @change="applyFilters" class="border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ payment.reference }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ payment.user?.name || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ payment.type.replace('_', ' ') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">K{{ Number(payment.amount).toLocaleString() }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full capitalize', getStatusClass(payment.status)]">{{ payment.status }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(payment.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('employee.portal.delegated.payments.show', payment.id)" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View</Link>
                            </td>
                        </tr>
                        <tr v-if="payments.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No payments found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
