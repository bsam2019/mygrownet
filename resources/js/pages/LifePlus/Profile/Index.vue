<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PencilIcon,
    Cog6ToothIcon,
    ChevronRightIcon,
    ClipboardDocumentCheckIcon,
    BanknotesIcon,
    SparklesIcon,
    BriefcaseIcon,
    QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Profile {
    id: number;
    user_id: number;
    name: string;
    email: string;
    location: string | null;
    bio: string | null;
    skills: string[];
    avatar_url: string | null;
}

interface Stats {
    tasks_completed: number;
    tasks_pending: number;
    task_completion_rate: number;
    month_spent: number;
    month_budget: number;
    active_habits: number;
    total_streaks: number;
    gigs_posted: number;
    gigs_completed: number;
}

const props = defineProps<{
    profile: Profile;
    stats: Stats;
}>();

const formatCurrency = (amount: number) => {
    return 'K ' + amount.toLocaleString();
};

const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Profile Header -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 text-center">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mx-auto mb-4 shadow-lg">
                <span class="text-2xl font-bold text-white">{{ getInitials(profile.name) }}</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">{{ profile.name }}</h1>
            <p class="text-gray-500 text-sm">{{ profile.email }}</p>
            <p v-if="profile.location" class="text-gray-500 text-sm mt-1">📍 {{ profile.location }}</p>
            <p v-if="profile.bio" class="text-gray-600 text-sm mt-3">{{ profile.bio }}</p>
            
            <div v-if="profile.skills?.length" class="flex flex-wrap justify-center gap-2 mt-4">
                <span 
                    v-for="skill in profile.skills.slice(0, 5)" 
                    :key="skill"
                    class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium"
                >
                    {{ skill }}
                </span>
            </div>
            
            <Link 
                :href="route('lifeplus.profile.settings')"
                class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors"
            >
                <PencilIcon class="h-4 w-4" aria-hidden="true" />
                Edit Profile
            </Link>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                        <ClipboardDocumentCheckIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.tasks_completed }}</p>
                        <p class="text-xs text-gray-500">Tasks Done</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center">
                        <BanknotesIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.month_spent) }}</p>
                        <p class="text-xs text-gray-500">This Month</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
                        <SparklesIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_streaks }}</p>
                        <p class="text-xs text-gray-500">Habit Streaks</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center">
                        <BriefcaseIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.gigs_completed }}</p>
                        <p class="text-xs text-gray-500">Gigs Done</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
            <Link 
                :href="route('lifeplus.profile.skills')"
                class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center">
                        <span class="text-lg">🎯</span>
                    </div>
                    <span class="font-medium text-gray-900">My Skills</span>
                </div>
                <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </Link>
            
            <Link 
                :href="route('lifeplus.gigs.my-gigs')"
                class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center">
                        <BriefcaseIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                    </div>
                    <span class="font-medium text-gray-900">My Gigs</span>
                </div>
                <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </Link>
            
            <Link 
                :href="route('lifeplus.profile.settings')"
                class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center">
                        <Cog6ToothIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                    </div>
                    <span class="font-medium text-gray-900">Settings</span>
                </div>
                <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </Link>
            
            <Link 
                href="/apps"
                class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                        <span class="text-lg">🚀</span>
                    </div>
                    <span class="font-medium text-gray-900">MyGrowNet Hub</span>
                </div>
                <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </Link>
            
            <button 
                class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors w-full"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-amber-50 flex items-center justify-center">
                        <QuestionMarkCircleIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                    </div>
                    <span class="font-medium text-gray-900">Help & Support</span>
                </div>
                <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </button>
        </div>
    </div>
</template>
