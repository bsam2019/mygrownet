<template>
    <div class="spillover-visualization space-y-6">
        <!-- Spillover Overview -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Spillover System</h3>
                    <p class="text-gray-600">Track spillover placements and opportunities</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Available Positions</div>
                        <div class="text-lg font-bold text-emerald-600">{{ availablePositions }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Spillover Received</div>
                        <div class="text-lg font-bold text-amber-600">{{ spilloverReceived }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Spillover Status -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-200">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-emerald-100 rounded-lg">
                            <TrendingUpIcon class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-emerald-900">Next Spillover</div>
                            <div class="text-xs text-emerald-700">
                                {{ nextSpillover ? `Level ${nextSpillover.level}, Position ${nextSpillover.position}` : 'No spillover expected' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <UsersIcon class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-blue-900">Spillover Given</div>
                            <div class="text-xs text-blue-700">{{ spilloverGiven }} referrals placed</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <DollarSignIcon class="h-5 w-5 text-purple-600" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-purple-900">Growth Earnings</div>
                            <div class="text-xs text-purple-700">{{ formatCurrency(spilloverEarnings || 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spillover Flow Diagram -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h4 class="text-md font-semibold text-gray-900 mb-4">Spillover Flow</h4>
            <div class="spillover-flow">
                <!-- Your Position -->
                <div class="flow-level flex justify-center mb-8">
                    <div class="flow-node you">
                        <div class="node-avatar">
                            <UserIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="node-label">You</div>
                        <div class="node-stats">{{ directReferrals }}/3 Direct</div>
                    </div>
                </div>

                <!-- Direct Referrals Level -->
                <div class="flow-level flex justify-center space-x-12 mb-8">
                    <div v-for="(referral, index) in level1Referrals" :key="`level1-${index}`" class="flow-node">
                        <!-- Connection Line -->
                        <div class="connection-line"></div>
                        
                        <div :class="referralNodeClasses(referral)">
                            <div class="node-avatar">
                                <UserIcon v-if="!referral.is_empty" class="h-5 w-5" />
                                <PlusIcon v-else class="h-5 w-5 text-gray-400" />
                            </div>
                            <div class="node-label">
                                {{ referral.is_empty ? 'Available' : referral.name }}
                            </div>
                            <div class="node-stats">
                                {{ referral.is_empty ? 'Position' : `${referral.downline_count}/3` }}
                            </div>
                        </div>

                        <!-- Spillover Indicators -->
                        <div v-if="referral.has_spillover" class="spillover-indicator">
                            <div class="spillover-arrow">
                                <ArrowDownIcon class="h-4 w-4 text-amber-500" />
                            </div>
                            <div class="spillover-count">{{ referral.spillover_count }}</div>
                        </div>
                    </div>
                </div>

                <!-- Spillover Level -->
                <div class="flow-level flex justify-center space-x-6">
                    <div v-for="(spillover, index) in spilloverPlacements" :key="`spillover-${index}`" class="flow-node spillover">
                        <!-- Connection Line -->
                        <div class="connection-line spillover-line"></div>
                        
                        <div class="node spillover-node">
                            <div class="node-avatar">
                                <UserIcon v-if="!spillover.is_empty" class="h-4 w-4" />
                                <PlusIcon v-else class="h-4 w-4 text-amber-400" />
                            </div>
                            <div class="node-label">
                                {{ spillover.is_empty ? 'Next' : spillover.name }}
                            </div>
                            <div class="node-stats">
                                {{ spillover.is_empty ? 'Spillover' : 'Placed' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spillover History -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-gray-900">Recent Spillover Activity</h4>
                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    View All
                </button>
            </div>
            
            <div class="space-y-4">
                <div v-for="activity in spilloverHistory" :key="activity.id" class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                    <div :class="spilloverActivityIconClasses(activity.type)">
                        <component :is="getSpilloverIcon(activity.type)" class="h-5 w-5" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
                                <p class="text-xs text-gray-500">{{ formatRelativeTime(activity.created_at) }}</p>
                            </div>
                            <div class="text-right">
                                <div v-if="activity.commission_earned" class="text-sm font-medium text-emerald-600">
                                    +{{ formatCurrency(activity.commission_earned) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Level {{ activity.level }} • Position {{ activity.position }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="spilloverHistory.length === 0" class="text-center py-8">
                <TrendingUpIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500">No spillover activity yet</p>
                <p class="text-sm text-gray-400 mt-1">Spillover placements will appear here</p>
            </div>
        </div>

        <!-- Spillover Opportunities -->
        <div v-if="spilloverOpportunities.length > 0" class="bg-gradient-to-r from-emerald-50 to-blue-50 rounded-lg p-6 border border-emerald-200">
            <div class="flex items-start space-x-4">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <TrendingUpIcon class="h-6 w-6 text-emerald-600" />
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-semibold text-emerald-900 mb-2">Spillover Opportunities Available!</h4>
                    <p class="text-emerald-800 mb-4">
                        You have {{ spilloverOpportunities.length }} potential spillover placement{{ spilloverOpportunities.length > 1 ? 's' : '' }} available.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="opportunity in spilloverOpportunities" :key="opportunity.id" class="bg-white rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ opportunity.sponsor_name }}'s Network
                                </div>
                                <div class="text-xs text-emerald-600 font-medium">
                                    Level {{ opportunity.level }}
                                </div>
                            </div>
                            <div class="text-xs text-gray-600 mb-3">
                                Position {{ opportunity.position }} • {{ opportunity.available_slots }} slots available
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    Potential Commission: {{ formatCurrency(opportunity.potential_commission) }}
                                </div>
                                <button class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded font-medium hover:bg-emerald-200 transition-colors">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spillover Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <ArrowDownIcon class="h-6 w-6 text-amber-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Spillovers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ spilloverStats.total_spillovers }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <DollarSignIcon class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Growth Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(spilloverStats?.total_earnings || 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <TrendingUpIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">This Month</p>
                        <p class="text-2xl font-bold text-gray-900">{{ spilloverStats.monthly_spillovers }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <UsersIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Spillovers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ spilloverStats.active_spillovers }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { 
    TrendingUpIcon, 
    UsersIcon, 
    DollarSignIcon, 
    UserIcon, 
    PlusIcon, 
    ArrowDownIcon,
    ArrowUpIcon,
    GiftIcon
} from 'lucide-vue-next';
import { formatCurrency } from '@/utils/formatting';

interface SpilloverData {
    available_positions: number;
    spillover_received: number;
    spillover_given: number;
    spillover_earnings: number;
    direct_referrals: number;
    next_spillover?: {
        level: number;
        position: number;
    };
}

interface ReferralNode {
    id?: number;
    name?: string;
    is_empty?: boolean;
    downline_count?: number;
    has_spillover?: boolean;
    spillover_count?: number;
}

interface SpilloverPlacement {
    id?: number;
    name?: string;
    is_empty?: boolean;
    level: number;
    position: number;
}

interface SpilloverActivity {
    id: number;
    type: 'received' | 'given' | 'commission';
    description: string;
    level: number;
    position: number;
    commission_earned?: number;
    created_at: string;
}

interface SpilloverOpportunity {
    id: number;
    sponsor_name: string;
    level: number;
    position: number;
    available_slots: number;
    potential_commission: number;
}

interface SpilloverStats {
    total_spillovers: number;
    total_earnings: number;
    monthly_spillovers: number;
    active_spillovers: number;
}

interface Props {
    spilloverData: SpilloverData;
    level1Referrals: ReferralNode[];
    spilloverPlacements: SpilloverPlacement[];
    spilloverHistory: SpilloverActivity[];
    spilloverOpportunities: SpilloverOpportunity[];
    spilloverStats: SpilloverStats;
}

const props = defineProps<Props>();

// Computed properties
const availablePositions = computed(() => props.spilloverData.available_positions);
const spilloverReceived = computed(() => props.spilloverData.spillover_received);
const spilloverGiven = computed(() => props.spilloverData.spillover_given);
const spilloverEarnings = computed(() => props.spilloverData.spillover_earnings);
const directReferrals = computed(() => props.spilloverData.direct_referrals);
const nextSpillover = computed(() => props.spilloverData.next_spillover);

// Methods
const referralNodeClasses = (referral: ReferralNode) => [
    'node',
    {
        'filled-node': !referral.is_empty,
        'empty-node': referral.is_empty,
        'has-spillover': referral.has_spillover
    }
];

const spilloverActivityIconClasses = (type: string) => [
    'w-10 h-10 rounded-full flex items-center justify-center',
    {
        'bg-emerald-100 text-emerald-600': type === 'received',
        'bg-blue-100 text-blue-600': type === 'given',
        'bg-purple-100 text-purple-600': type === 'commission'
    }
];

const getSpilloverIcon = (type: string) => {
    const icons = {
        'received': ArrowDownIcon,
        'given': ArrowUpIcon,
        'commission': GiftIcon
    };
    return icons[type as keyof typeof icons] || ArrowDownIcon;
};



const formatRelativeTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
};
</script>

<style scoped>
.spillover-flow {
    position: relative;
    min-height: 400px;
}

.flow-level {
    position: relative;
}

.flow-node {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.node {
    @apply bg-white border-2 rounded-lg p-3 text-center min-w-20;
    transition: all 0.2s ease-in-out;
}

.node.filled-node {
    @apply border-blue-200 bg-blue-50;
}

.node.empty-node {
    @apply border-gray-200 bg-gray-50 border-dashed;
}

.node.spillover-node {
    @apply border-amber-200 bg-amber-50;
}

.node.has-spillover {
    @apply border-emerald-200 bg-emerald-50;
}

.node.you {
    @apply border-blue-500 bg-blue-100;
}

.node-avatar {
    @apply w-8 h-8 rounded-full bg-white border-2 border-current flex items-center justify-center mb-2;
}

.node-label {
    @apply text-xs font-medium text-gray-900 truncate max-w-16;
}

.node-stats {
    @apply text-xs text-gray-600 mt-1;
}

.connection-line {
    @apply absolute -top-8 left-1/2 transform -translate-x-1/2 w-px h-8 bg-gray-300;
}

.connection-line.spillover-line {
    @apply bg-amber-400;
}

.spillover-indicator {
    @apply absolute -bottom-6 left-1/2 transform -translate-x-1/2 flex flex-col items-center;
}

.spillover-arrow {
    @apply w-6 h-6 bg-amber-100 rounded-full flex items-center justify-center;
}

.spillover-count {
    @apply text-xs font-bold text-amber-600 mt-1;
}

/* Hover effects */
.node:hover {
    @apply transform scale-105 shadow-md;
}

/* Animation for spillover indicators */
.spillover-arrow {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-4px);
    }
    60% {
        transform: translateY(-2px);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .flow-level {
        flex-direction: column;
        align-items: center;
        gap: 2rem;
    }
    
    .spillover-flow {
        min-height: 600px;
    }
}
</style>