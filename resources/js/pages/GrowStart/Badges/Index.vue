<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    TrophyIcon,
    LockClosedIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';
import type { Badge, Journey } from '@/types/growstart';

interface Props {
    badges: Badge[];
    earnedBadges: Badge[];
    journey: Journey | null;
    totalPoints: number;
}

const props = defineProps<Props>();

const earnedBadgeIds = computed(() => 
    props.earnedBadges.map(b => b.id)
);

const getBadgeIcon = (slug: string) => {
    const icons: Record<string, string> = {
        first_step: '👣',
        idea_master: '💡',
        validator: '✅',
        planner: '📋',
        registered: '📝',
        launched: '🚀',
        accountant: '💰',
        marketer: '📢',
        growth_hacker: '📈',
        streak_7: '🔥',
        streak_30: '⚡',
        completionist: '🏆',
        early_bird: '🌅',
        night_owl: '🦉'
    };
    return icons[slug] || '🏅';
};

const getCriteriaLabel = (type: string, value: string | null) => {
    switch (type) {
        case 'stage_complete': return `Complete ${value} stage`;
        case 'tasks_complete': return `Complete ${value} tasks`;
        case 'streak_days': return `${value} day streak`;
        case 'journey_complete': return 'Complete your journey';
        default: return 'Special achievement';
    }
};
</script>

<template>
    <Head title="GrowStart - Badges & Achievements" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Badges & Achievements</h1>
                    <p class="text-gray-600">Track your progress and earn rewards</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 text-center">
                        <TrophyIcon class="mx-auto h-8 w-8 text-amber-500" aria-hidden="true" />
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ earnedBadges.length }}</p>
                        <p class="text-sm text-gray-500">Badges Earned</p>
                    </div>
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 text-center">
                        <span class="text-3xl">⭐</span>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ totalPoints }}</p>
                        <p class="text-sm text-gray-500">Total Points</p>
                    </div>
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 text-center">
                        <span class="text-3xl">🎯</span>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ badges.length - earnedBadges.length }}</p>
                        <p class="text-sm text-gray-500">To Unlock</p>
                    </div>
                </div>

                <!-- Earned Badges -->
                <div v-if="earnedBadges.length > 0" class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Earned Badges</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="badge in earnedBadges"
                            :key="badge.id"
                            class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200 text-center"
                        >
                            <div class="w-16 h-16 mx-auto rounded-full bg-white shadow-sm flex items-center justify-center text-3xl">
                                {{ getBadgeIcon(badge.slug) }}
                            </div>
                            <h3 class="mt-3 font-medium text-gray-900">{{ badge.name }}</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ badge.description }}</p>
                            <div class="mt-2 flex items-center justify-center gap-1 text-amber-600">
                                <span class="text-sm font-medium">+{{ badge.points }}</span>
                                <span class="text-xs">pts</span>
                            </div>
                            <p v-if="badge.earned_at" class="mt-1 text-xs text-gray-400">
                                Earned {{ new Date(badge.earned_at).toLocaleDateString() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- All Badges -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">All Badges</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="badge in badges"
                            :key="badge.id"
                            :class="[
                                'rounded-xl p-4 border text-center transition',
                                earnedBadgeIds.includes(badge.id)
                                    ? 'bg-white border-emerald-200'
                                    : 'bg-gray-50 border-gray-200 opacity-60'
                            ]"
                        >
                            <div 
                                :class="[
                                    'w-16 h-16 mx-auto rounded-full flex items-center justify-center text-3xl',
                                    earnedBadgeIds.includes(badge.id) ? 'bg-emerald-50' : 'bg-gray-100'
                                ]"
                            >
                                <template v-if="earnedBadgeIds.includes(badge.id)">
                                    {{ getBadgeIcon(badge.slug) }}
                                </template>
                                <LockClosedIcon v-else class="h-8 w-8 text-gray-400" aria-hidden="true" />
                            </div>
                            <h3 class="mt-3 font-medium text-gray-900">{{ badge.name }}</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ badge.description }}</p>
                            <div 
                                :class="[
                                    'mt-2 flex items-center justify-center gap-1',
                                    earnedBadgeIds.includes(badge.id) ? 'text-emerald-600' : 'text-gray-400'
                                ]"
                            >
                                <CheckCircleIcon 
                                    v-if="earnedBadgeIds.includes(badge.id)"
                                    class="h-4 w-4" 
                                    aria-hidden="true" 
                                />
                                <span class="text-sm font-medium">{{ badge.points }} pts</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ getCriteriaLabel(badge.criteria_type, badge.criteria_value) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- No Journey State -->
                <div v-if="!journey" class="mt-8 text-center py-8 bg-blue-50 rounded-xl">
                    <TrophyIcon class="mx-auto h-12 w-12 text-blue-400" aria-hidden="true" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Start earning badges!</h3>
                    <p class="mt-2 text-gray-600">Begin your journey to unlock achievements</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
