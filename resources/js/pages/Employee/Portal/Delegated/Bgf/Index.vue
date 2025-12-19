<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { MagnifyingGlassIcon, FolderIcon, ClockIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

interface Application {
    id: number;
    user: { id: number; name: string; email: string } | null;
    reference_number: string;
    business_name: string;
    amount_requested: string;
    status: string;
    score: number | null;
    created_at: string;
}

interface Props {
    employee: any;
    applications: { data: Application[]; links: any; meta: any };
    stats: { pending_review: number; under_review: number; approved_this_month: number };
    filters: { search?: string; status?: string };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('employee.portal.delegated.bgf.index'), { search: search.value, status: status.value }, { preserveState: true });
};

const getStatusClass = (s: string) => ({
    'draft': 'bg-gray-100 text-gray-700',
    'submitted': 'bg-amber-100 text-amber-700',
    'under_review': 'bg-blue-100 text-blue-700',
    'approved': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
    'funded': 'bg-purple-100 text-purple-700',
}[s] || 'bg-gray-100 text-gray-700');

const getScoreClass = (score: number | null) => {
    if (!score) return 'text-gray-400';
    if (score >= 70) return 'text-green-600';
    if (score >= 50) return 'text-amber-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Delegated - BGF Applications" />
    <EmployeePortalLayout>
        <template #header>BGF Applications</template>
        
        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg"><ClockIcon class="h-5 w-5 text-amber-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.pending_review }}</p>
                            <p class="text-sm text-gray-500">Pending Review</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg"><FolderIcon class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.under_review }}</p>
                            <p class="text-sm text-gray-500">Under Review</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg"><CheckCircleIcon class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.approved_this_month }}</p>
                            <p class="text-sm text-gray-500">Approved This Month</p>
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
                            <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search by reference, business name, or applicant..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                    </div>
                    <select v-model="status" @change="applyFilters" class="border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="funded">Funded</option>
                    </select>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="app in applications.data" :key="app.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ app.reference_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ app.business_name }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ app.user?.name || 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ app.user?.email }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">K{{ Number(app.amount_requested).toLocaleString() }}</td>
                            <td class="px-6 py-4">
                                <span :class="['text-sm font-bold', getScoreClass(app.score)]">{{ app.score ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full capitalize', getStatusClass(app.status)]">{{ app.status.replace('_', ' ') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('employee.portal.delegated.bgf.show', app.id)" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View</Link>
                            </td>
                        </tr>
                        <tr v-if="applications.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No applications found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
