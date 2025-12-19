<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { DocumentArrowDownIcon, FunnelIcon } from '@heroicons/vue/24/outline';

interface Log {
    id: number;
    employee: { id: number; first_name: string; last_name: string; full_name: string } | null;
    performer: { id: number; name: string } | null;
    permission_key: string;
    action: string;
    metadata: any;
    ip_address: string;
    created_at: string;
}

interface Props {
    logs: { data: Log[]; links: any; meta: any };
    filters: { employee_id?: string; action?: string; permission?: string };
}

const props = defineProps<Props>();
const employeeId = ref(props.filters.employee_id || '');
const action = ref(props.filters.action || '');
const permission = ref(props.filters.permission || '');

const applyFilters = () => {
    router.get(route('admin.delegations.logs'), {
        employee_id: employeeId.value || undefined,
        action: action.value || undefined,
        permission: permission.value || undefined,
    }, { preserveState: true });
};

const exportLogs = () => {
    window.location.href = route('admin.delegations.logs.export', {
        employee_id: employeeId.value || undefined,
    });
};

const getActionClass = (a: string) => ({
    'granted': 'bg-green-100 text-green-700',
    'revoked': 'bg-red-100 text-red-700',
    'used': 'bg-blue-100 text-blue-700',
    'approval_requested': 'bg-amber-100 text-amber-700',
    'approved': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
    'expired': 'bg-gray-100 text-gray-700',
}[a] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head title="Delegation Audit Logs" />
    <AdminLayout :breadcrumbs="[{ title: 'Delegations', href: route('admin.delegations.index') }, { title: 'Audit Logs' }]">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Delegation Audit Logs</h1>
                    <p class="text-gray-500">Track all delegation activities</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('admin.delegations.activity-report')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Activity Report
                    </Link>
                    <button @click="exportLogs" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <DocumentArrowDownIcon class="h-5 w-5" />Export CSV
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <FunnelIcon class="h-5 w-5 text-gray-400" />
                    <span class="font-medium text-gray-700">Filters</span>
                </div>
                <div class="flex flex-wrap gap-4">
                    <select v-model="action" @change="applyFilters" class="border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Actions</option>
                        <option value="granted">Granted</option>
                        <option value="revoked">Revoked</option>
                        <option value="used">Used</option>
                        <option value="approval_requested">Approval Requested</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input v-model="permission" @keyup.enter="applyFilters" type="text" placeholder="Permission key..." class="border border-gray-300 rounded-lg px-3 py-2" />
                    <button @click="applyFilters" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Apply</button>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permission</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performed By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(log.created_at).toLocaleString() }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ log.employee?.full_name || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono text-xs">{{ log.permission_key }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full capitalize', getActionClass(log.action)]">
                                    {{ log.action.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ log.performer?.name || 'System' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400 font-mono text-xs">{{ log.ip_address }}</td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No logs found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
