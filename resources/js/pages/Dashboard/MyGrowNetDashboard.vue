<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { 
    TrendingUpIcon,
    UsersIcon,
    GiftIcon,
    BriefcaseIcon,
    StarIcon,
    CoinsIcon,
    AwardIcon,
    ChartBarIcon
} from 'lucide-vue-next';

interface Props {
    user: {
        name: string;
        email: string;
        phone?: string;
        referral_code: string;
        joined_at: string;
    };
    points: {
        lifetime_points: number;
        bonus_points: number;
        monthly_bp_target: number;
        bp_progress_percentage: number;
    };
    professionalLevel: {
        current: string;
        level_number: number;
        next_level: string | null;
        lp_required_for_next: number | null;
        lp_progress_percentage: number;
    };
    earnings: {
        current_month: number;
        last_month: number;
        total_lifetime: number;
        pending: number;
    };
    network: {
        direct_referrals: number;
        total_network: number;
        active_members: number;
        level_breakdown: Array<{ level: number; count: number }>;
    };
    wallet: {
        balance: number;
        total_earnings: number;
        total_withdrawals: number;
    };
    recentActivities: Array<{
        id: number;
        description: string;
        points_earned: number;
        created_at: string;
    }>;
    upcomingWorkshops?: Array<{
        id: number;
        title: string;
        slug: string;
        category: string;
        start_date: string;
        lp_reward: number;
        bp_reward: number;
        price: string;
        available_slots: number | null;
    }>;
    myWorkshops?: Array<{
        id: number;
        workshop_title: string;
        workshop_slug: string;
        start_date: string;
        status: string;
    }>;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const getLevelColor = (level: string) => {
    const colors: Record<string, string> = {
        'Associate': 'bg-gray-100 text-gray-800',
        'Professional': 'bg-blue-100 text-blue-800',
        'Senior': 'bg-indigo-100 text-indigo-800',
        'Manager': 'bg-purple-100 text-purple-800',
        'Director': 'bg-pink-100 text-pink-800',
        'Executive': 'bg-red-100 text-red-800',
        'Ambassador': 'bg-yellow-100 text-yellow-800',
    };
    return colors[level] || 'bg-gray-100 text-gray-800';
};

const quickActions = [
    { title: 'Invite Members', href: 'my-team.index', icon: UsersIcon, color: 'bg-blue-500' },
    { title: 'View Earnings', href: 'mygrownet.earnings.index', icon: GiftIcon, color: 'bg-green-500' },
    { title: 'My Wallet', href: 'mygrownet.wallet.index', icon: CoinsIcon, color: 'bg-purple-500' },
    { title: 'Network Analytics', href: 'mygrownet.network.analytics', icon: ChartBarIcon, color: 'bg-indigo-500' },
];
</script>

<template>
    <MemberLayout>
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Welcome Header -->
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Welcome back, {{ user.name }}!</h1>
                    <p class="mt-1 text-sm text-gray-600">Here's your business overview for today</p>
                </div>

                <!-- Points Overview Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Lifetime Points -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <StarIcon class="h-8 w-8 text-blue-100" />
                            <span class="text-xs font-medium bg-blue-700 px-2 py-1 rounded">LP</span>
                        </div>
                        <p class="text-3xl font-bold">{{ points.lifetime_points.toLocaleString() }}</p>
                        <p class="text-sm text-blue-100 mt-1">Lifetime Points</p>
                    </div>

                    <!-- Bonus Points -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <CoinsIcon class="h-8 w-8 text-green-100" />
                            <span class="text-xs font-medium bg-green-700 px-2 py-1 rounded">BP</span>
                        </div>
                        <p class="text-3xl font-bold">{{ points.bonus_points.toLocaleString() }}</p>
                        <p class="text-sm text-green-100 mt-1">This Month's BP</p>
                        <div class="mt-2 bg-green-700 rounded-full h-2">
                            <div 
                                class="bg-white rounded-full h-2 transition-all duration-500"
                                :style="{ width: `${points.bp_progress_percentage}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Current Month Earnings -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-2">
                            <GiftIcon class="h-8 w-8 text-purple-600" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(earnings.current_month) }}</p>
                        <p class="text-sm text-gray-600 mt-1">This Month</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Last month: {{ formatCurrency(earnings.last_month) }}
                        </p>
                    </div>

                    <!-- Network Size -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-2">
                            <UsersIcon class="h-8 w-8 text-indigo-600" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ network.total_network }}</p>
                        <p class="text-sm text-gray-600 mt-1">Total Network</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ network.active_members }} active members
                        </p>
                    </div>
                </div>

                <!-- Professional Level & Progress -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Professional Level Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Professional Level</h2>
                            <Link 
                                :href="route('mygrownet.levels.index')"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                            >
                                View Details →
                            </Link>
                        </div>
                        
                        <div class="flex items-center gap-4 mb-4">
                            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full p-4">
                                <AwardIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', getLevelColor(professionalLevel.current)]">
                                    Level {{ professionalLevel.level_number }}: {{ professionalLevel.current }}
                                </span>
                                <p class="text-sm text-gray-600 mt-1">{{ points.lifetime_points.toLocaleString() }} LP earned</p>
                            </div>
                        </div>

                        <div v-if="professionalLevel.next_level" class="mt-4">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-gray-600">Progress to {{ professionalLevel.next_level }}</span>
                                <span class="font-medium text-gray-900">{{ professionalLevel.lp_progress_percentage }}%</span>
                            </div>
                            <div class="bg-gray-200 rounded-full h-3">
                                <div 
                                    class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full h-3 transition-all duration-500"
                                    :style="{ width: `${professionalLevel.lp_progress_percentage}%` }"
                                ></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ professionalLevel.lp_required_for_next?.toLocaleString() }} LP needed
                            </p>
                        </div>
                        <div v-else class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800 font-medium">🎉 You've reached the highest level!</p>
                        </div>
                    </div>

                    <!-- Wallet Summary -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">My Wallet</h2>
                            <Link 
                                :href="route('mygrownet.wallet.index')"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                            >
                                View Wallet →
                            </Link>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Available Balance</p>
                                <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(wallet.balance) }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                <div>
                                    <p class="text-xs text-gray-600">Total Earnings</p>
                                    <p class="text-lg font-semibold text-green-600">{{ formatCurrency(wallet.total_earnings) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Withdrawals</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(wallet.total_withdrawals) }}</p>
                                </div>
                            </div>

                            <Link
                                :href="route('withdrawals.create')"
                                class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                            >
                                Request Withdrawal
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <Link
                            v-for="action in quickActions"
                            :key="action.title"
                            :href="route(action.href)"
                            class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
                        >
                            <div :class="[action.color, 'p-3 rounded-lg mb-3']">
                                <component :is="action.icon" class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-900 text-center">{{ action.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Workshops Section -->
                <div v-if="upcomingWorkshops && upcomingWorkshops.length > 0" class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Upcoming Workshops</h2>
                        <Link 
                            :href="route('mygrownet.workshops.index')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            View All →
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link
                            v-for="workshop in upcomingWorkshops"
                            :key="workshop.id"
                            :href="route('mygrownet.workshops.show', workshop.slug)"
                            class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow"
                        >
                            <h3 class="font-semibold text-gray-900 mb-2">{{ workshop.title }}</h3>
                            <p class="text-sm text-gray-600 mb-3">{{ workshop.start_date }}</p>
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="text-blue-600 font-medium">{{ workshop.lp_reward }} LP</span>
                                    <span class="text-green-600 font-medium">{{ workshop.bp_reward }} BP</span>
                                </div>
                                <span class="text-gray-900 font-semibold">K{{ workshop.price }}</span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- My Workshops -->
                <div v-if="myWorkshops && myWorkshops.length > 0" class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">My Registered Workshops</h2>
                        <Link 
                            :href="route('mygrownet.workshops.my-workshops')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            View All →
                        </Link>
                    </div>
                    <div class="bg-white rounded-lg shadow divide-y divide-gray-200">
                        <Link
                            v-for="workshop in myWorkshops"
                            :key="workshop.id"
                            :href="route('mygrownet.workshops.show', workshop.workshop_slug)"
                            class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                        >
                            <div>
                                <h3 class="font-medium text-gray-900">{{ workshop.workshop_title }}</h3>
                                <p class="text-sm text-gray-600">{{ workshop.start_date }}</p>
                            </div>
                            <span :class="[
                                'px-3 py-1 text-xs font-semibold rounded-full',
                                workshop.status === 'registered' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                            ]">
                                {{ workshop.status }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Activities</h2>
                        <Link 
                            :href="route('points.transactions')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            View All →
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="activity in recentActivities"
                            :key="activity.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ activity.created_at }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        +{{ activity.points_earned }} BP
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="recentActivities.length === 0" class="px-6 py-8 text-center text-gray-500">
                            No recent activities
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <TrendingUpIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                        <div>
                            <h3 class="font-semibold text-blue-900">Grow Your Business</h3>
                            <p class="text-sm text-blue-800 mt-1">
                                Earn more BP by inviting members, completing training, and staying active. 
                                Your monthly earnings are calculated based on your BP share of the total platform BP.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
