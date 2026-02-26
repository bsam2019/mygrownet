<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { UsersIcon, TrendingUpIcon, ActivityIcon, AwardIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface LevelBreakdown {
    level: number;
    count: number;
    active: number;
}

interface GrowthData {
    month: string;
    members: number;
}

interface TopPerformer {
    name: string;
    referrals: number;
    level: string;
}

const props = defineProps<{
    directReferrals: number;
    totalNetwork: number;
    activeMembers: number;
    levelBreakdown: LevelBreakdown[];
    growthData: GrowthData[];
    teamPerformance: {
        topPerformers: TopPerformer[];
        averageTeamSize: number;
    };
}>();

const maxGrowth = computed(() => 
    Math.max(...props.growthData.map(g => g.members), 1)
);

const activityRate = computed(() => 
    props.totalNetwork > 0 ? ((props.activeMembers / props.totalNetwork) * 100).toFixed(1) : 0
);
</script>

<template>
    <MemberLayout>
        <Head title="Network Analytics" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Network Analytics</h1>
                <p class="mt-2 text-sm text-gray-600">Visualize your team growth and performance</p>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Direct Referrals</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ directReferrals }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <UsersIcon class="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Network</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ totalNetwork }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <TrendingUpIcon class="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Active Members</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ activeMembers }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <ActivityIcon class="h-6 w-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Activity Rate</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ activityRate }}%</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <AwardIcon class="h-6 w-6 text-amber-600" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Level Breakdown -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Network by Level</h2>
                    <div class="space-y-3">
                        <div
                            v-for="level in levelBreakdown"
                            :key="level.level"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 text-blue-600 font-bold rounded-full h-8 w-8 flex items-center justify-center text-sm">
                                    L{{ level.level }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Level {{ level.level }}</p>
                                    <p class="text-xs text-gray-500">{{ level.active }} active</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">{{ level.count }}</p>
                                <p class="text-xs text-gray-500">members</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Performers</h2>
                    <div class="space-y-3">
                        <div
                            v-for="(performer, index) in teamPerformance.topPerformers"
                            :key="index"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="font-bold rounded-full h-8 w-8 flex items-center justify-center text-sm"
                                    :class="{
                                        'bg-yellow-100 text-yellow-600': index === 0,
                                        'bg-gray-200 text-gray-600': index === 1,
                                        'bg-orange-100 text-orange-600': index === 2,
                                        'bg-blue-100 text-blue-600': index > 2,
                                    }"
                                >
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ performer.name }}</p>
                                    <p class="text-xs text-gray-500">{{ performer.level }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">{{ performer.referrals }}</p>
                                <p class="text-xs text-gray-500">referrals</p>
                            </div>
                        </div>
                        
                        <div v-if="teamPerformance.topPerformers.length === 0" class="text-center py-8 text-gray-500">
                            No team members yet
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Network Growth (Last 6 Months)</h2>
                <div class="space-y-3">
                    <div
                        v-for="data in growthData"
                        :key="data.month"
                        class="flex items-center gap-3"
                    >
                        <div class="w-12 text-sm font-medium text-gray-600">{{ data.month }}</div>
                        <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-green-500 to-green-600 h-full rounded-full transition-all duration-500 flex items-center justify-end pr-3"
                                :style="{ width: `${(data.members / maxGrowth) * 100}%` }"
                            >
                                <span v-if="data.members > 0" class="text-xs font-medium text-white">
                                    {{ data.members }} members
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <TrendingUpIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-green-900">Growing Your Network</h3>
                        <p class="text-sm text-green-800 mt-1">
                            Focus on helping your direct referrals succeed. Active team members contribute 
                            to your BP earnings and help build a sustainable network. The 3×7 matrix structure 
                            allows for spillover, so supporting your team benefits everyone.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </MemberLayout>
</template>
