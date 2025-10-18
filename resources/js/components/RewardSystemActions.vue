<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
    UserPlusIcon,
    ChartBarIcon,
    GiftIcon,
    ArrowUpIcon,
    ClipboardDocumentIcon,
    ShareIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';

interface Props {
    referralCode?: string;
    canUpgrade?: boolean;
    nextTier?: string;
    pendingCommissions?: number;
}

const props = withDefaults(defineProps<Props>(), {
    referralCode: '',
    canUpgrade: false,
    nextTier: '',
    pendingCommissions: 0,
});

const quickActions = [
    {
        title: 'Invite Friends',
        description: 'Share your referral code',
        href: 'referrals.index',
        icon: UserPlusIcon,
        color: 'bg-blue-500 hover:bg-blue-600',
        action: 'invite'
    },
    {
        title: 'View Matrix',
        description: 'Check your matrix position',
        href: 'matrix.index',
        icon: ChartBarIcon,
        color: 'bg-green-500 hover:bg-green-600',
        action: 'matrix'
    },
    {
        title: 'Track Commissions',
        description: 'Monitor your earnings',
        href: 'referrals.commissions',
        icon: GiftIcon,
        color: 'bg-purple-500 hover:bg-purple-600',
        action: 'commissions'
    },
    {
        title: 'Upgrade Tier',
        description: props.canUpgrade ? `Upgrade to ${props.nextTier}` : 'View tier benefits',
        href: props.canUpgrade ? 'investments.tier-upgrade' : 'tiers.compare',
        icon: ArrowUpIcon,
        color: props.canUpgrade ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-gray-500 hover:bg-gray-600',
        action: 'upgrade'
    }
];

const copyReferralCode = () => {
    if (props.referralCode) {
        navigator.clipboard.writeText(props.referralCode);
        // You could add a toast notification here
    }
};

const shareReferralLink = () => {
    const referralLink = `${window.location.origin}/register?ref=${props.referralCode}`;
    if (navigator.share) {
        navigator.share({
            title: 'Join VBIF Investment Platform',
            text: 'Start your investment journey with VBIF',
            url: referralLink
        });
    } else {
        navigator.clipboard.writeText(referralLink);
        // You could add a toast notification here
    }
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            
            <!-- Referral Code Section -->
            <div v-if="referralCode" class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900">Your Referral Code</h4>
                    <div class="flex space-x-2">
                        <button
                            @click="copyReferralCode"
                            class="p-1 text-gray-500 hover:text-blue-600 transition-colors"
                            title="Copy Code"
                        >
                            <ClipboardDocumentIcon class="h-4 w-4" />
                        </button>
                        <button
                            @click="shareReferralLink"
                            class="p-1 text-gray-500 hover:text-blue-600 transition-colors"
                            title="Share Link"
                        >
                            <ShareIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <div class="font-mono text-lg font-bold text-blue-600 bg-white px-3 py-2 rounded border">
                    {{ referralCode }}
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    Share this code to earn referral commissions
                </p>
            </div>

            <!-- Pending Commissions Alert -->
            <div v-if="pendingCommissions > 0" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <GiftIcon class="h-5 w-5 text-yellow-600 mr-2" />
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Pending Commissions</h4>
                        <p class="text-sm text-yellow-700">
                            You have {{ formatCurrency(pendingCommissions) }} in pending commissions
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <Link
                    v-for="action in quickActions"
                    :key="action.action"
                    :href="route(action.href)"
                    :class="action.color"
                    class="flex flex-col items-center justify-center p-4 rounded-lg text-white transition-colors duration-200 hover:shadow-md"
                >
                    <component :is="action.icon" class="h-6 w-6 mb-2" />
                    <span class="text-sm font-medium text-center">{{ action.title }}</span>
                    <span class="text-xs opacity-90 text-center mt-1">{{ action.description }}</span>
                </Link>
            </div>

            <!-- Additional Links -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 gap-2">
                    <Link
                        :href="route('referrals.performance-report')"
                        class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                        <span>Performance Report</span>
                        <ChartBarIcon class="h-4 w-4 text-gray-400" />
                    </Link>
                    <Link
                        :href="route('referrals.matrix-genealogy')"
                        class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                        <span>Matrix Genealogy</span>
                        <ChartBarIcon class="h-4 w-4 text-gray-400" />
                    </Link>
                    <Link
                        :href="route('referrals.by-level')"
                        class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                        <span>Referrals by Level</span>
                        <UserPlusIcon class="h-4 w-4 text-gray-400" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>