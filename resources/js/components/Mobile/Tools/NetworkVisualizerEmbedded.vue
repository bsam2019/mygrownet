<script setup lang="ts">
import { ref, computed } from 'vue';
import { 
    NetworkIcon, 
    UsersIcon, 
    TrendingUpIcon,
    UserCheckIcon,
    ChevronDownIcon,
    ChevronRightIcon
} from 'lucide-vue-next';

interface Props {
    networkTree: any[];
    networkStats: any;
    userTier: 'basic' | 'premium' | null;
    allLevels?: any[];
    levelCounts?: any;
}

const props = defineProps<Props>();

const selectedView = ref<'tree' | 'stats'>('tree');
const expandedNodes = ref<Set<number>>(new Set());

// Use level counts from parent (real data)
const realLevelCounts = computed(() => props.levelCounts || {});

// Calculate growth rate
const growthRate = computed(() => {
    const level1 = realLevelCounts.value[1] || 0;
    const level2 = realLevelCounts.value[2] || 0;
    if (level1 === 0) return 0;
    return ((level2 / level1) * 100).toFixed(0);
});

// Calculate team depth (how many levels have members)
const teamDepth = computed(() => {
    let depth = 0;
    for (let i = 1; i <= 7; i++) {
        if (realLevelCounts.value[i] > 0) {
            depth = i;
        }
    }
    return depth;
});

const toggleNode = (nodeId: number) => {
    if (expandedNodes.value.has(nodeId)) {
        expandedNodes.value.delete(nodeId);
    } else {
        expandedNodes.value.add(nodeId);
    }
};

const isExpanded = (nodeId: number) => {
    return expandedNodes.value.has(nodeId);
};

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

// Get level 1 members for tree view
const level1Members = computed(() => {
    if (!props.allLevels || !Array.isArray(props.allLevels)) return [];
    return props.allLevels[0]?.members || [];
});
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header with View Toggle -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <NetworkIcon class="h-6 w-6 text-orange-600" />
                    Network Visualizer
                </h2>
                <p class="text-xs text-gray-600 mt-1">Visual network tree</p>
            </div>
            <div class="flex gap-2">
                <button
                    @click="selectedView = 'tree'"
                    :class="selectedView === 'tree' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                >
                    🌳 Tree
                </button>
                <button
                    @click="selectedView = 'stats'"
                    :class="selectedView === 'stats' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                >
                    📊 Stats
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-4 gap-2">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-3 text-white text-center">
                <p class="text-xs opacity-90">Total</p>
                <p class="text-xl font-bold mt-1">{{ networkStats.total_members }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-3 text-white text-center">
                <p class="text-xs opacity-90">Active</p>
                <p class="text-xl font-bold mt-1">{{ networkStats.active_members }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-3 text-white text-center">
                <p class="text-xs opacity-90">Depth</p>
                <p class="text-xl font-bold mt-1">L{{ teamDepth }}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-3 text-white text-center">
                <p class="text-xs opacity-90">Growth</p>
                <p class="text-xl font-bold mt-1">{{ growthRate }}%</p>
            </div>
        </div>

        <!-- TREE VIEW -->
        <div v-if="selectedView === 'tree'" class="space-y-4">
            <!-- Visual Tree Diagram -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Network Tree Structure</h3>
                
                <!-- You (Root) -->
                <div class="flex flex-col items-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                        YOU
                    </div>
                    <p class="text-xs text-gray-600 mt-1 font-semibold">{{ userTier || 'Member' }}</p>
                </div>

                <!-- Connection Line -->
                <div class="flex justify-center mb-2">
                    <div class="w-0.5 h-6 bg-gray-300"></div>
                </div>

                <!-- Level 1 Members (Direct Referrals) -->
                <div v-if="level1Members.length > 0" class="space-y-3">
                    <div class="text-center mb-2">
                        <span class="text-xs font-semibold text-gray-600 bg-blue-100 px-3 py-1 rounded-full">
                            Level 1 - Direct Referrals ({{ level1Members.length }})
                        </span>
                    </div>

                    <!-- Members Grid -->
                    <div class="grid grid-cols-3 gap-3">
                        <div
                            v-for="member in level1Members.slice(0, 9)"
                            :key="member.id"
                            class="relative"
                        >
                            <!-- Connection Line to Parent -->
                            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 w-0.5 h-3 bg-gray-300"></div>
                            
                            <!-- Member Card -->
                            <div
                                @click="toggleNode(member.id)"
                                class="bg-white border-2 rounded-lg p-2 text-center cursor-pointer hover:shadow-md transition-all"
                                :class="member.has_starter_kit ? 'border-green-400' : 'border-gray-300'"
                            >
                                <div 
                                    class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-white font-bold text-xs"
                                    :class="member.has_starter_kit ? 'bg-green-500' : 'bg-gray-400'"
                                >
                                    {{ member.name.charAt(0).toUpperCase() }}
                                </div>
                                <p class="text-xs font-semibold text-gray-900 mt-1 truncate">{{ member.name.split(' ')[0] }}</p>
                                <p class="text-xs text-gray-500">{{ member.has_starter_kit ? '✅' : '⏳' }}</p>
                                
                                <!-- Expand indicator if has children -->
                                <div v-if="realLevelCounts[2] > 0" class="mt-1">
                                    <ChevronDownIcon 
                                        class="h-3 w-3 mx-auto text-gray-400"
                                        :class="{ 'rotate-180': isExpanded(member.id) }"
                                    />
                                </div>
                            </div>

                            <!-- Expanded Details -->
                            <div
                                v-if="isExpanded(member.id)"
                                class="mt-2 bg-gray-50 rounded-lg p-2 text-xs"
                            >
                                <p class="font-semibold text-gray-900 truncate">{{ member.name }}</p>
                                <p class="text-gray-600 truncate">{{ member.email }}</p>
                                <p class="text-gray-500 mt-1">Joined: {{ formatDate(member.joined_date) }}</p>
                                <div class="mt-1 flex gap-1 flex-wrap">
                                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">
                                        {{ member.tier || 'Associate' }}
                                    </span>
                                    <span 
                                        v-if="member.starter_kit_tier"
                                        class="px-1.5 py-0.5 bg-purple-100 text-purple-800 rounded text-xs"
                                    >
                                        {{ member.starter_kit_tier }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Show more indicator -->
                    <div v-if="level1Members.length > 9" class="text-center mt-3">
                        <p class="text-xs text-gray-600">
                            + {{ level1Members.length - 9 }} more members
                        </p>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-8">
                    <UsersIcon class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                    <p class="text-sm text-gray-600">No direct referrals yet</p>
                    <p class="text-xs text-gray-500 mt-1">Start building your network!</p>
                </div>

                <!-- Level Indicators -->
                <div v-if="teamDepth > 1" class="mt-6 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-600 text-center mb-3">Your network extends to:</p>
                    <div class="flex justify-center gap-2">
                        <div
                            v-for="level in teamDepth"
                            :key="level"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                            :class="realLevelCounts[level] > 0 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'"
                        >
                            L{{ level }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Level Breakdown -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Level Distribution</h3>
                <div class="space-y-2">
                    <div v-for="level in 7" :key="level" class="flex items-center gap-3">
                        <div class="w-12 text-xs font-semibold text-gray-700">Level {{ level }}</div>
                        <div class="flex-1 bg-gray-200 rounded-full h-6 overflow-hidden">
                            <div 
                                class="h-6 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-end pr-2 transition-all duration-500"
                                :style="{ width: `${networkStats.total_members > 0 ? (realLevelCounts[level] / networkStats.total_members * 100) : 0}%` }"
                            >
                                <span v-if="realLevelCounts[level] > 0" class="text-xs font-bold text-white">
                                    {{ realLevelCounts[level] }}
                                </span>
                            </div>
                        </div>
                        <div class="w-12 text-xs text-gray-600 text-right">
                            {{ realLevelCounts[level] || 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATS VIEW -->
        <div v-if="selectedView === 'stats'" class="space-y-4">
            <!-- Network Health -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Network Health</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">Activation Rate</span>
                            <span class="font-semibold text-gray-900">
                                {{ networkStats.total_members > 0 ? ((networkStats.active_members / networkStats.total_members) * 100).toFixed(0) : 0 }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="bg-green-500 h-2 rounded-full transition-all"
                                :style="{ width: `${networkStats.total_members > 0 ? (networkStats.active_members / networkStats.total_members * 100) : 0}%` }"
                            ></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">Network Depth</span>
                            <span class="font-semibold text-gray-900">{{ teamDepth }} / 7 Levels</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="bg-purple-500 h-2 rounded-full transition-all"
                                :style="{ width: `${(teamDepth / 7) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Volume Stats -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <TrendingUpIcon class="h-5 w-5 text-purple-600 mb-2" />
                    <p class="text-xs text-purple-700">Total Volume</p>
                    <p class="text-lg font-bold text-purple-900 mt-1">{{ formatCurrency(networkStats.total_volume) }}</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                    <TrendingUpIcon class="h-5 w-5 text-orange-600 mb-2" />
                    <p class="text-xs text-orange-700">This Month</p>
                    <p class="text-lg font-bold text-orange-900 mt-1">{{ formatCurrency(networkStats.this_month_volume) }}</p>
                </div>
            </div>

            <!-- Level Performance -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Level Performance</h3>
                <div class="space-y-2">
                    <div v-for="level in 7" :key="level" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <span class="text-xs font-medium text-gray-700">Level {{ level }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-600">{{ realLevelCounts[level] || 0 }} members</span>
                            <span 
                                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="realLevelCounts[level] > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            >
                                {{ realLevelCounts[level] > 0 ? 'Active' : 'Empty' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
            <h4 class="text-xs font-semibold text-gray-900 mb-2">Legend</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-gray-700">Active (Has Kit)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                    <span class="text-gray-700">Pending (No Kit)</span>
                </div>
            </div>
        </div>
    </div>
</template>
