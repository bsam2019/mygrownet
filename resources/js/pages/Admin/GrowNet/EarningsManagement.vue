<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

interface UserRow {
    id: number;
    name: string;
    email: string;
    phone: string;
    status: string;
    account_type: string;
    has_starter_kit: boolean;
    total_earnings: number;
    pending_earnings: number;
    commissions: number;
    profit_shares: number;
    bonus_points: number;
    loyalty_points: number;
    bonus_balance: number;
    created_at: string;
}

interface PaginatedUsers {
    data: UserRow[];
    meta: any;
}

const props = defineProps<{
    users: PaginatedUsers;
    filters: { search: string | null; status: string | null };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const searchUsers = () => {
    router.get(route('admin.grownet.earnings'), { search: search.value, status: statusFilter.value || undefined }, { preserveState: true });
};

const formatCurrency = (value: number) => `K${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
</script>

<template>
    <Head title="Earnings Management" />
    <AdminLayout>
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Earnings Management</h1>
                    <p class="text-sm text-gray-600 mt-1">View and manage member earnings across all streams</p>
                </div>
                <Link :href="route('admin.grownet.dashboard')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    &larr; Back to Dashboard
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                        <input v-model="search" type="text" placeholder="Name, email, or phone..." @keyup.enter="searchUsers"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="statusFilter" @change="searchUsers"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <button @click="searchUsers" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Search</button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Commissions</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pending</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit Shares</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Earnings</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                <p class="text-xs text-gray-500">{{ user.email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', user.status === 'active' ? 'bg-green-100 text-green-800' : user.status === 'inactive' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800']">
                                    {{ user.status }}
                                </span>
                                <span v-if="user.has_starter_kit" class="ml-1 px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Kit</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatCurrency(user.commissions) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-amber-600">{{ formatCurrency(user.pending_earnings) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatCurrency(user.profit_shares) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-green-700">{{ formatCurrency(user.total_earnings) }}</td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('admin.grownet.earnings.show', user.id)" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View Details &rarr;
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 text-sm">No users found</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="users.meta?.last_page > 1" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-sm text-gray-600">Page {{ users.meta.current_page }} of {{ users.meta.last_page }}</p>
                    <div class="flex gap-2">
                        <Link v-if="users.meta.prev_page_url" :href="users.meta.prev_page_url" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">&larr; Previous</Link>
                        <Link v-if="users.meta.next_page_url" :href="users.meta.next_page_url" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Next &rarr;</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
