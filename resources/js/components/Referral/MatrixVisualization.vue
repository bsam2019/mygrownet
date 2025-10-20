<template>
    <div class="matrix-visualization">
        <!-- Matrix Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">3x3 Matrix Structure</h2>
                    <p class="text-gray-600">Your referral matrix with spillover visualization</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Matrix Level</div>
                        <div class="text-lg font-bold text-blue-600">{{ currentLevel }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Total Positions</div>
                        <div class="text-lg font-bold text-emerald-600">{{ filledPositions }}/{{ totalPositions }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Matrix Legend -->
            <div class="flex items-center space-x-6 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                    <span class="text-gray-600">You</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-emerald-500 rounded-full"></div>
                    <span class="text-gray-600">Direct Referral</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-amber-500 rounded-full"></div>
                    <span class="text-gray-600">Spillover</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-gray-300 rounded-full border-2 border-dashed border-gray-400"></div>
                    <span class="text-gray-600">Empty Position</span>
                </div>
            </div>
        </div>

        <!-- Matrix Tree Structure -->
        <div class="bg-white rounded-lg shadow-sm p-8">
            <div class="matrix-tree">
                <!-- Level 0 - Root (You) -->
                <div class="level level-0 flex justify-center mb-12">
                    <MatrixNode 
                        :node="rootNode"
                        :is-root="true"
                        @node-click="handleNodeClick"
                    />
                </div>

                <!-- Level 1 - Direct Referrals -->
                <div class="level level-1 flex justify-center space-x-16 mb-12">
                    <div v-for="(node, index) in level1Nodes" :key="`level1-${index}`" class="relative">
                        <!-- Connection Line to Parent -->
                        <div class="connection-line absolute -top-12 left-1/2 transform -translate-x-1/2 w-px h-12 bg-gray-300"></div>
                        <MatrixNode 
                            :node="node"
                            :position="index + 1"
                            :level="1"
                            @node-click="handleNodeClick"
                        />
                    </div>
                </div>

                <!-- Level 2 - Second Level Referrals -->
                <div class="level level-2 flex justify-center space-x-8 mb-12">
                    <div v-for="(group, groupIndex) in level2Groups" :key="`group-${groupIndex}`" class="flex space-x-4">
                        <div v-for="(node, nodeIndex) in group" :key="`level2-${groupIndex}-${nodeIndex}`" class="relative">
                            <!-- Connection Line to Parent -->
                            <div class="connection-line absolute -top-12 left-1/2 transform -translate-x-1/2 w-px h-12 bg-gray-300"></div>
                            <MatrixNode 
                                :node="node"
                                :position="(groupIndex * 3) + nodeIndex + 1"
                                :level="2"
                                @node-click="handleNodeClick"
                            />
                        </div>
                    </div>
                </div>

                <!-- Level 3 - Third Level Referrals -->
                <div class="level level-3 flex justify-center space-x-2">
                    <div v-for="(superGroup, superIndex) in level3SuperGroups" :key="`super-${superIndex}`" class="flex space-x-1">
                        <div v-for="(group, groupIndex) in superGroup" :key="`group-${superIndex}-${groupIndex}`" class="flex space-x-1">
                            <div v-for="(node, nodeIndex) in group" :key="`level3-${superIndex}-${groupIndex}-${nodeIndex}`" class="relative">
                                <!-- Connection Line to Parent -->
                                <div class="connection-line absolute -top-12 left-1/2 transform -translate-x-1/2 w-px h-12 bg-gray-300"></div>
                                <MatrixNode 
                                    :node="node"
                                    :position="(superIndex * 9) + (groupIndex * 3) + nodeIndex + 1"
                                    :level="3"
                                    :is-small="true"
                                    @node-click="handleNodeClick"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spillover Information -->
        <div v-if="spilloverInfo.has_opportunities" class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-6 mt-6 border border-amber-200">
            <div class="flex items-start space-x-4">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <TrendingUpIcon class="h-6 w-6 text-amber-600" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-amber-900 mb-2">Spillover Opportunity Available!</h3>
                    <p class="text-amber-800 mb-3">
                        Your next referral will be placed in {{ spilloverInfo.placement_type }} position 
                        with {{ spilloverInfo.available_slots }} available slots.
                    </p>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm">
                            <span class="font-medium text-amber-900">Next Position:</span>
                            <span class="text-amber-700">Level {{ spilloverInfo.next_position?.level }}, Position {{ spilloverInfo.next_position?.position }}</span>
                        </div>
                        <button 
                            @click="showSpilloverDetails = !showSpilloverDetails"
                            class="text-amber-600 hover:text-amber-800 text-sm font-medium"
                        >
                            {{ showSpilloverDetails ? 'Hide' : 'Show' }} Details
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Spillover Details -->
            <div v-if="showSpilloverDetails" class="mt-4 pt-4 border-t border-amber-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg p-4">
                        <div class="text-sm font-medium text-gray-600">Placement Strategy</div>
                        <div class="text-lg font-bold text-gray-900">{{ spilloverInfo.placement_type }}</div>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <div class="text-sm font-medium text-gray-600">Available Slots</div>
                        <div class="text-lg font-bold text-emerald-600">{{ spilloverInfo.available_slots }}</div>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <div class="text-sm font-medium text-gray-600">Potential Commission</div>
                        <div class="text-lg font-bold text-blue-600">{{ formatCurrency(estimatedCommission) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <UsersIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Level 1 Referrals</p>
                        <p class="text-2xl font-bold text-gray-900">{{ matrixStats?.level_1_count || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <UsersIcon class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Level 2 Referrals</p>
                        <p class="text-2xl font-bold text-gray-900">{{ matrixStats?.level_2_count || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <UsersIcon class="h-6 w-6 text-amber-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Level 3 Referrals</p>
                        <p class="text-2xl font-bold text-gray-900">{{ matrixStats?.level_3_count || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <TrendingUpIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Matrix Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(matrixStats?.total_earnings || 0) }}</p>
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
    // Ensure we always have 3 positions
    while (nodes.length < 3) {
        nodes.push({ is_empty: true, position: nodes.length + 1, level: 1 });
    }
    return nodes.slice(0, 3);
});

const level2Groups = computed(() => {
    const nodes = props.matrixData?.levels?.level_2 || [];
    // Ensure we have 9 positions (3 groups of 3)
    while (nodes.length < 9) {
        nodes.push({ is_empty: true, position: nodes.length + 1, level: 2 });
    }
    
    // Group into 3 groups of 3
    const groups = [];
    for (let i = 0; i < 3; i++) {
        groups.push(nodes.slice(i * 3, (i + 1) * 3));
    }
    return groups;
});

const level3SuperGroups = computed(() => {
    const nodes = props.matrixData?.levels?.level_3 || [];
    // Ensure we have 27 positions (3 super groups of 3 groups of 3)
    while (nodes.length < 27) {
        nodes.push({ is_empty: true, position: nodes.length + 1, level: 3 });
    }
    
    // Group into 3 super groups, each containing 3 groups of 3
    const superGroups = [];
    for (let i = 0; i < 3; i++) {
        const superGroup = [];
        for (let j = 0; j < 3; j++) {
            const startIndex = (i * 9) + (j * 3);
            superGroup.push(nodes.slice(startIndex, startIndex + 3));
        }
        superGroups.push(superGroup);
    }
    return superGroups;
});

const currentLevel = computed(() => {
    const filledCount = props.matrixStats?.filled_positions || 0;
    if (filledCount <= 3) return 1;
    if (filledCount <= 12) return 2;
    return 3;
});

const filledPositions = computed(() => props.matrixStats?.filled_positions || 0);
const totalPositions = computed(() => 3279); // 3×3 Matrix, 7 Levels: 3+9+27+81+243+729+2187 = 3,279

const estimatedCommission = computed(() => {
    // Estimate commission based on current tier and average investment
    const averageInvestment = 2500; // Default average
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

// Event handlers
const handleNodeClick = (node: MatrixNodeData) => {
    if (node.is_empty) {
        // Show referral sharing modal or information
        return;
    }
    
    // Show node details
    console.log('Node clicked:', node);
    // You could emit an event or show a modal with node details
};


</script>

<style scoped>
.matrix-tree {
    position: relative;
    min-height: 600px;
}

.level {
    position: relative;
}

.connection-line {
    z-index: 1;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .level-1 {
        flex-direction: column;
        align-items: center;
        space-y: 4rem;
    }
    
    .level-1 > div {
        margin-bottom: 2rem;
    }
    
    .level-2, .level-3 {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .matrix-tree {
        min-height: 800px;
    }
}

@media (max-width: 640px) {
    .level-2 .flex {
        flex-direction: column;
        align-items: center;
    }
    
    .level-3 .flex {
        flex-direction: column;
        align-items: center;
    }
}
</style>