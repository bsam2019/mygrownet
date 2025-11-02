<template>
    <div class="matrix-visualization space-y-4 md:space-y-6">
        <!-- Matrix Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">3x3 Matrix Structure</h2>
                    <p class="text-sm text-gray-600">Your referral matrix with spillover visualization</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-center md:text-right">
                        <div class="text-xs text-gray-500">Matrix Level</div>
                        <div class="text-base md:text-lg font-bold text-blue-600">{{ currentLevel }}</div>
                    </div>
                    <div class="text-center md:text-right">
                        <div class="text-xs text-gray-500">Total Positions</div>
                        <div class="text-base md:text-lg font-bold text-emerald-600">{{ filledPositions }}/40</div>
                    </div>
                </div>
            </div>
            
            <!-- Matrix Legend -->
            <div class="flex flex-wrap items-center gap-3 md:gap-6 text-xs md:text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 md:w-4 md:h-4 bg-blue-500 rounded-full"></div>
                    <span class="text-gray-600">You</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 md:w-4 md:h-4 bg-emerald-500 rounded-full"></div>
                    <span class="text-gray-600">Direct</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 md:w-4 md:h-4 bg-amber-500 rounded-full"></div>
                    <span class="text-gray-600">Spillover</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 md:w-4 md:h-4 bg-gray-300 rounded-full border-2 border-dashed border-gray-400"></div>
                    <span class="text-gray-600">Empty</span>
                </div>
            </div>
        </div>

        <!-- Matrix Tree Structure -->
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
            <!-- Level Selector -->
            <div class="mb-4 md:mb-6 flex flex-wrap gap-2">
                <button
                    v-for="lvl in [1, 2, 3]"
                    :key="lvl"
                    @click="selectedLevel = lvl"
                    :class="[
                        'px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors',
                        selectedLevel === lvl
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ]"
                >
                    Level {{ lvl }} ({{ getLevelCount(lvl) }})
                </button>
                <button
                    @click="selectedLevel = 0"
                    :class="[
                        'px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors',
                        selectedLevel === 0
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ]"
                >
                    View All
                </button>
            </div>

            <div class="matrix-tree">
                <!-- Level 0 - Root (You) - Always visible -->
                <div class="flex justify-center mb-6 md:mb-8">
                    <MatrixNode 
                        :node="rootNode"
                        :is-root="true"
                        @node-click="handleNodeClick"
                    />
                </div>

                <!-- Level 1 - Direct Referrals -->
                <div v-if="selectedLevel === 0 || selectedLevel === 1" class="mb-6 md:mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 text-center">Level 1 - Direct Referrals</h3>
                    <div class="grid grid-cols-3 gap-3 md:gap-6 max-w-2xl mx-auto">
                        <div v-for="(node, index) in level1Nodes" :key="`level1-${index}`">
                            <MatrixNode 
                                :node="node"
                                :position="index + 1"
                                :level="1"
                                @node-click="handleNodeClick"
                            />
                        </div>
                    </div>
                </div>

                <!-- Level 2 - Second Level Referrals -->
                <div v-if="selectedLevel === 0 || selectedLevel === 2" class="mb-6 md:mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 text-center">Level 2 - Second Generation (9 positions)</h3>
                    <div class="grid grid-cols-3 gap-3 md:gap-4 max-w-3xl mx-auto">
                        <div v-for="(node, index) in level2Nodes" :key="`level2-${index}`">
                            <MatrixNode 
                                :node="node"
                                :position="index + 1"
                                :level="2"
                                :is-small="true"
                                @node-click="handleNodeClick"
                            />
                        </div>
                    </div>
                </div>

                <!-- Level 3 - Third Level Referrals -->
                <div v-if="selectedLevel === 0 || selectedLevel === 3">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 text-center">Level 3 - Third Generation (27 positions)</h3>
                    <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-9 gap-1 md:gap-2 max-w-6xl mx-auto">
                        <div v-for="(node, index) in level3Nodes" :key="`level3-${index}`">
                            <MatrixNode 
                                :node="node"
                                :position="index + 1"
                                :level="3"
                                :is-small="true"
                                @node-click="handleNodeClick"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spillover Information -->
        <div v-if="spilloverInfo.has_opportunities" class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-4 md:p-6 border border-amber-200">
            <div class="flex items-start gap-3 md:gap-4">
                <div class="p-2 bg-amber-100 rounded-lg flex-shrink-0">
                    <TrendingUpIcon class="h-5 w-5 md:h-6 md:w-6 text-amber-600" />
                </div>
                <div class="flex-1">
                    <h3 class="text-base md:text-lg font-semibold text-amber-900 mb-2">Spillover Opportunity Available!</h3>
                    <p class="text-sm text-amber-800 mb-3">
                        Your next referral will be placed in {{ spilloverInfo.placement_type }} position 
                        with {{ spilloverInfo.available_slots }} available slots.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="text-xs md:text-sm">
                            <span class="font-medium text-amber-900">Next Position:</span>
                            <span class="text-amber-700">Level {{ spilloverInfo.next_position?.level }}, Position {{ spilloverInfo.next_position?.position }}</span>
                        </div>
                        <button 
                            @click="showSpilloverDetails = !showSpilloverDetails"
                            class="text-amber-600 hover:text-amber-800 text-xs md:text-sm font-medium"
                        >
                            {{ showSpilloverDetails ? 'Hide' : 'Show' }} Details
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Spillover Details -->
            <div v-if="showSpilloverDetails" class="mt-4 pt-4 border-t border-amber-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    <div class="bg-white rounded-lg p-3 md:p-4">
                        <div class="text-xs font-medium text-gray-600">Placement Strategy</div>
                        <div class="text-base md:text-lg font-bold text-gray-900">{{ spilloverInfo.placement_type }}</div>
                    </div>
                    <div class="bg-white rounded-lg p-3 md:p-4">
                        <div class="text-xs font-medium text-gray-600">Available Slots</div>
                        <div class="text-base md:text-lg font-bold text-emerald-600">{{ spilloverInfo.available_slots }}</div>
                    </div>
                    <div class="bg-white rounded-lg p-3 md:p-4">
                        <div class="text-xs font-medium text-gray-600">Potential Commission</div>
                        <div class="text-base md:text-lg font-bold text-blue-600">{{ formatCurrency(estimatedCommission) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg flex-shrink-0">
                        <UsersIcon class="h-5 w-5 md:h-6 md:w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-600">Level 1</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">{{ matrixStats?.level_1_count || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg flex-shrink-0">
                        <UsersIcon class="h-5 w-5 md:h-6 md:w-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-600">Level 2</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">{{ matrixStats?.level_2_count || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-lg flex-shrink-0">
                        <UsersIcon class="h-5 w-5 md:h-6 md:w-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-600">Level 3</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">{{ matrixStats?.level_3_count || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-lg flex-shrink-0">
                        <TrendingUpIcon class="h-5 w-5 md:h-6 md:w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-600">Earnings</p>
                        <p class="text-base md:text-xl font-bold text-gray-900">{{ formatCurrency(matrixStats?.total_earnings || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { TrendingUpIcon, UsersIcon } from 'lucide-vue-next';
import MatrixNode from './MatrixNode.vue';
import { formatCurrency } from '@/utils/formatting';

interface MatrixNodeData {
    id?: number;
    name?: string;
    email?: string;
    tier?: string;
    investment_amount?: number;
    position?: number;
    level?: number;
    is_direct?: boolean;
    is_spillover?: boolean;
    is_empty?: boolean;
    avatar?: string;
    joined_at?: string;
    status?: 'active' | 'inactive';
}

interface SpilloverInfo {
    has_opportunities: boolean;
    next_position?: {
        level: number;
        position: number;
    };
    placement_type?: string;
    available_slots?: number;
}

interface MatrixStats {
    level_1_count: number;
    level_2_count: number;
    level_3_count: number;
    total_earnings: number;
    filled_positions: number;
    total_positions: number;
}

interface Props {
    matrixData: {
        root: MatrixNodeData;
        levels: {
            level_1: MatrixNodeData[];
            level_2: MatrixNodeData[];
            level_3: MatrixNodeData[];
        };
    };
    spilloverInfo: SpilloverInfo;
    matrixStats: MatrixStats;
    currentUserTier?: string;
}

const props = defineProps<Props>();

const showSpilloverDetails = ref(false);
const selectedLevel = ref(0); // 0 = all, 1-3 = specific level

// Computed properties for matrix structure
const rootNode = computed(() => props.matrixData?.root || {
    id: null,
    name: 'You',
    tier: 'Unknown',
    investment_amount: 0,
    is_empty: false,
    is_direct: false,
    is_spillover: false,
    status: 'active',
    position: 1,
    level: 0
});

const level1Nodes = computed(() => {
    const nodes = props.matrixData?.levels?.level_1 || [];
    while (nodes.length < 3) {
        nodes.push({ is_empty: true, position: nodes.length + 1, level: 1 });
    }
    return nodes.slice(0, 3);
});

const level2Nodes = computed(() => {
    const nodes = props.matrixData?.levels?.level_2 || [];
    while (nodes.length < 9) {
        nodes.push({ is_empty: true, position: nodes.length + 1, level: 2 });
    }
    return nodes.slice(0, 9);
});

const level3Nodes = computed(() => {
    const nodes = props.matrixData?.levels?.level_3 || [];
    while (nodes.length < 27) {
        nodes.push({ is_empty: true, position: nodes.length + 1, level: 3 });
    }
    return nodes.slice(0, 27);
});

const currentLevel = computed(() => {
    const filledCount = props.matrixStats?.filled_positions || 0;
    if (filledCount <= 3) return 1;
    if (filledCount <= 12) return 2;
    return 3;
});

const filledPositions = computed(() => props.matrixStats?.filled_positions || 0);

const estimatedCommission = computed(() => {
    const averageInvestment = 2500;
    const tierRates = {
        'Basic': 5,
        'Starter': 7,
        'Builder': 10,
        'Leader': 12,
        'Elite': 15
    };
    
    const rate = tierRates[props.currentUserTier as keyof typeof tierRates] || 5;
    return (averageInvestment * rate) / 100;
});

const getLevelCount = (level: number) => {
    if (level === 1) return props.matrixStats?.level_1_count || 0;
    if (level === 2) return props.matrixStats?.level_2_count || 0;
    if (level === 3) return props.matrixStats?.level_3_count || 0;
    return 0;
};

// Event handlers
const handleNodeClick = (node: MatrixNodeData) => {
    if (node.is_empty) {
        return;
    }
    console.log('Node clicked:', node);
};
</script>

<style scoped>
.matrix-tree {
    position: relative;
}
</style>
