<template>
    <MemberLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <Link 
                            :href="route('my-team.index')"
                            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-2"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back to My Team
                        </Link>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900">Team by Level</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1">View your team members organized by network level</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Total Team Size</div>
                        <div class="text-2xl font-bold text-blue-600">{{ totalTeamSize }}</div>
                    </div>
                </div>
            </div>

            <!-- Level Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div 
                    v-for="level in levelSummary" 
                    :key="level.level"
                    @click="selectLevel(level.level)"
                    :class="[
                        'bg-white rounded-lg shadow-sm p-6 cursor-pointer transition-all duration-200',
                        selectedLevel === level.level 
                            ? 'ring-2 ring-blue-500 shadow-md' 
                            : 'hover:shadow-md hover:ring-1 hover:ring-gray-300'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Level {{ level.level }}</div>
                            <div class="text-3xl font-bold text-gray-900 mt-1">{{ level.count }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ level.count === 1 ? 'member' : 'members' }}
                            </div>
                        </div>
                        <div :class="getLevelIconColor(level.level)" class="text-4xl">
                            {{ getLevelIcon(level.level) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Level Members -->
            <div v-if="selectedLevelData" class="bg-white rounded-lg shadow-sm p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        Level {{ selectedLevel }} Members
                    </h2>
                    <p class="text-gray-600 mt-2">
                        {{ selectedLevelData.count }} {{ selectedLevelData.count === 1 ? 'member' : 'members' }} at this level
                    </p>
                </div>

                <div v-if="selectedLevelData.members.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referred By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr 
                                v-for="(member, index) in selectedLevelData.members" 
                                :key="member.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ member.name }}</div>
                                    <div class="text-sm text-gray-500">{{ member.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ member.phone }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ member.referrer_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="getTierBadgeColor(member.tier)"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ member.tier }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ member.joined_date }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-12">
                    <div class="text-gray-400 text-5xl mb-4">👥</div>
                    <p class="text-gray-500">No members at this level yet</p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { Link } from '@inertiajs/vue3';

interface Member {
    id: number;
    name: string;
    email: string;
    phone: string;
    referrer_id: number;
    referrer_name: string;
    joined_date: string;
    tier: string;
    level: number;
}

interface LevelSummary {
    level: number;
    count: number;
    members: Member[];
}

interface Props {
    levelSummary: LevelSummary[];
    selectedLevel?: number;
    totalTeamSize: number;
}

const props = withDefaults(defineProps<Props>(), {
    selectedLevel: 1
});

const selectedLevel = ref(props.selectedLevel);

const selectedLevelData = computed(() => {
    return props.levelSummary.find(level => level.level === selectedLevel.value);
});

const selectLevel = (level: number) => {
    selectedLevel.value = level;
};

const getLevelIcon = (level: number): string => {
    const icons = ['🌱', '🌿', '🌳', '🏆', '💎', '👑', '⭐'];
    return icons[level - 1] || '👤';
};

const getLevelIconColor = (level: number): string => {
    const colors = [
        'text-green-500',
        'text-green-600',
        'text-blue-500',
        'text-blue-600',
        'text-indigo-500',
        'text-purple-500',
        'text-yellow-500'
    ];
    return colors[level - 1] || 'text-gray-500';
};

const getTierBadgeColor = (tier: string): string => {
    const tierColors: Record<string, string> = {
        'None': 'bg-gray-100 text-gray-800',
        'Basic': 'bg-gray-100 text-gray-800',
        'Starter': 'bg-blue-100 text-blue-800',
        'Builder': 'bg-blue-100 text-blue-800',
        'Leader': 'bg-indigo-100 text-indigo-800',
        'Elite': 'bg-purple-100 text-purple-800'
    };
    return tierColors[tier] || 'bg-gray-100 text-gray-800';
};
</script>
