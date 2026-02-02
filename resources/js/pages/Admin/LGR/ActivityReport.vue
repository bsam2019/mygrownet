<template>
    <AdminLayout>
        <Head title="LGR Activity Report" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">LGR Activity Performance</h1>
                    <p class="text-gray-600 mt-1">Track user activity and LGR credit earnings</p>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <UsersIcon class="h-6 w-6 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total_users }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <ActivityIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Active Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.active_users }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <CheckCircleIcon class="h-6 w-6 text-purple-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Activities</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total_activities }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <CoinsIcon class="h-6 w-6 text-yellow-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Credits Awarded</p>
                                <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(stats.total_credits) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <select
                                v-model="localFilters.date_range"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="7">Last 7 days</option>
                                <option value="30">Last 30 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="all">All time</option>
                            </select>
                        </div>

                        <!-- Activity Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
                            <select
                                v-model="localFilters.activity_type"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">All Types</option>
                                <option value="learning_module">Learning Module</option>
                                <option value="event_attendance">Event Attendance</option>
                                <option value="marketplace_purchase">Marketplace Purchase</option>
                                <option value="marketplace_sale">Marketplace Sale</option>
                                <option value="business_plan">Business Plan</option>
                                <option value="referral_registration">Referral Registration</option>
                                <option value="social_sharing">Social Sharing</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search User</label>
                            <input
                                v-model="localFilters.search"
                                @input="debouncedSearch"
                                type="text"
                                placeholder="Name, email, or phone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Activity Breakdown -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Breakdown</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="activity in activityBreakdown"
                            :key="activity.activity_type"
                            class="border border-gray-200 rounded-lg p-4"
                        >
                            <p class="text-sm text-gray-600 mb-1">{{ formatActivityType(activity.activity_type) }}</p>
                            <p class="text-xl font-bold text-gray-900">{{ activity.count }}</p>
                            <p class="text-xs text-gray-500">{{ activity.unique_users }} users</p>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">User Performance</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activities</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active Days</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credits Earned</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email || user.phone }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-gray-900">{{ user.total_activities }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-900">{{ user.active_days }} days</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-green-600">K{{ formatNumber(user.total_credits) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">{{ formatDate(user.last_activity) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            @click="viewUserDetails(user)"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                        >
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in users.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1 rounded',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                    v-html="link.label"
                                ></Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details Modal -->
        <UserDetailsModal
            v-model="showDetailsModal"
            :user="selectedUser"
            :date-range="localFilters.date_range"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import UserDetailsModal from '@/components/Admin/LGR/UserDetailsModal.vue';
import { UsersIcon, ActivityIcon, CheckCircleIcon, CoinsIcon } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    total_activities: number;
    active_days: number;
    total_credits: number;
    last_activity: string;
}

interface Props {
    users: {
        data: User[];
        from: number;
        to: number;
        total: number;
        links: any[];
    };
    activityBreakdown: any[];
    stats: {
        total_users: number;
        active_users: number;
        total_activities: number;
        total_credits: number;
    };
    dailyTrend: any[];
    filters: {
        date_range: string;
        activity_type: string | null;
        search: string | null;
    };
}

const props = defineProps<Props>();

const localFilters = ref({ ...props.filters });
const showDetailsModal = ref(false);
const selectedUser = ref<User | null>(null);

let searchTimeout: NodeJS.Timeout;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(route('admin.lgr.activity-report'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatActivityType = (type: string): string => {
    const types: Record<string, string> = {
        learning_module: 'Learning',
        event_attendance: 'Events',
        marketplace_purchase: 'Purchases',
        marketplace_sale: 'Sales',
        business_plan: 'Business Plans',
        referral_registration: 'Referrals',
        social_sharing: 'Social Shares',
    };
    return types[type] || type;
};

const formatNumber = (num: number): string => {
    return new Intl.NumberFormat().format(num);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const viewUserDetails = (user: User) => {
    selectedUser.value = user;
    showDetailsModal.value = true;
};
</script>
