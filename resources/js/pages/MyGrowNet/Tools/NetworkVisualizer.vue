<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { 
    NetworkIcon, 
    UsersIcon, 
    TrendingUpIcon,
    UserCheckIcon,
    UserXIcon
} from 'lucide-vue-next';

interface NetworkMember {
    id: number;
    name: string;
    tier: string | null;
    has_starter_kit: boolean;
    joined_at: string;
    children: NetworkMember[];
}

interface NetworkStats {
    total_members: number;
    active_members: number;
    total_volume: number;
    this_month_volume: number;
}

interface Props {
    networkTree: NetworkMember[];
    networkStats: NetworkStats;
    userTier: 'basic' | 'premium' | null;
}

const props = defineProps<Props>();

const selectedLevel = ref(1);
const expandedNodes = ref<Set<number>>(new Set());

const levelCounts = computed(() => {
    const counts: Record<number, number> = {};
    
    const countLevel = (nodes: NetworkMember[], level: number) => {
        if (!counts[level]) counts[level] = 0;
        counts[level] += nodes.length;
        
        nodes.forEach(node => {
            if (node.children && node.children.length > 0) {
                countLevel(node.children, level + 1);
            }
        });
    };
    
    countLevel(props.networkTree, 1);
    return counts;
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
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
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
</script>

<template>
    <Head title="Network Visualizer" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <NetworkIcon class="h-8 w-8 text-blue-600" />
                        Network Visualizer
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Visualize and analyze your 7-level network structure
                    </p>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                        <UsersIcon class="h-8 w-8 mb-2 opacity-80" />
                        <p class="text-sm opacity-90">Total Members</p>
                        <p class="text-3xl font-bold mt-1">{{ networkStats.total_members }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                        <UserCheckIcon class="h-8 w-8 mb-2 opacity-80" />
                        <p class="text-sm opacity-90">Active Members</p>
                        <p class="text-3xl font-bold mt-1">{{ networkStats.active_members }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
                        <TrendingUpIcon class="h-8 w-8 mb-2 opacity-80" />
                        <p class="text-sm opacity-90">Total Volume</p>
                        <p class="text-3xl font-bold mt-1">{{ formatCurrency(networkStats.total_volume) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow p-6 text-white">
                        <TrendingUpIcon class="h-8 w-8 mb-2 opacity-80" />
                        <p class="text-sm opacity-90">This Month</p>
                        <p class="text-3xl font-bold mt-1">{{ formatCurrency(networkStats.this_month_volume) }}</p>
                    </div>
                </div>

                <!-- Level Breakdown -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Network by Level</h3>
                    <div class="grid grid-cols-7 gap-2">
                        <div
                            v-for="level in 7"
                            :key="level"
                            @click="selectedLevel = level"
                            :class="selectedLevel === level ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="text-center p-4 rounded-lg cursor-pointer hover:shadow-md transition-all"
                        >
                            <p class="text-xs font-medium mb-1">Level {{ level }}</p>
                            <p class="text-2xl font-bold">{{ levelCounts[level] || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Network Tree Visualization -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Network Tree</h3>
                    
                    <div v-if="networkTree.length === 0" class="text-center py-12">
                        <UserXIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Network Yet</h3>
                        <p class="text-gray-600">Start building your network by referring new members!</p>
                    </div>

                    <div v-else class="space-y-2">
                        <!-- Recursive Network Tree Component -->
                        <div v-for="member in networkTree" :key="member.id" class="border-l-2 border-blue-300 pl-4">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                                    :class="member.has_starter_kit ? 'bg-green-500' : 'bg-gray-400'"
                                >
                                    {{ member.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ member.name }}</h4>
                                    <p class="text-xs text-gray-600">
                                        {{ member.has_starter_kit ? '✅ Active' : '⏳ Pending' }} • 
                                        Joined {{ formatDate(member.joined_at) }}
                                        <span v-if="member.tier" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                            {{ member.tier }}
                                        </span>
                                    </p>
                                </div>
                                <button
                                    v-if="member.children && member.children.length > 0"
                                    @click="toggleNode(member.id)"
                                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition-colors"
                                >
                                    {{ isExpanded(member.id) ? '−' : '+' }} {{ member.children.length }}
                                </button>
                            </div>

                            <!-- Children (Recursive) -->
                            <div v-if="isExpanded(member.id) && member.children && member.children.length > 0" class="ml-6 mt-2 space-y-2">
                                <div v-for="child in member.children" :key="child.id" class="border-l-2 border-green-300 pl-4">
                                    <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                        <div 
                                            class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                            :class="child.has_starter_kit ? 'bg-green-500' : 'bg-gray-400'"
                                        >
                                            {{ child.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 text-sm">{{ child.name }}</h4>
                                            <p class="text-xs text-gray-600">
                                                {{ child.has_starter_kit ? '✅ Active' : '⏳ Pending' }}
                                            </p>
                                        </div>
                                        <button
                                            v-if="child.children && child.children.length > 0"
                                            @click="toggleNode(child.id)"
                                            class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold hover:bg-green-200 transition-colors"
                                        >
                                            {{ isExpanded(child.id) ? '−' : '+' }} {{ child.children.length }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Legend</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <span class="text-gray-700">Active Member</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-gray-400 rounded-full"></div>
                            <span class="text-gray-700">Pending Member</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-blue-300"></div>
                            <span class="text-gray-700">Level 1 (Direct)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-green-300"></div>
                            <span class="text-gray-700">Level 2+</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
