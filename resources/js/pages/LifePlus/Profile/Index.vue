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
    MapPinIcon,
    AcademicCapIcon,
    RocketLaunchIcon,
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
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-rose-50 p-4 space-y-6">
        <!-- Profile Header with Softer Gradient -->
        <div class="bg-gradient-to-br from-indigo-400 via-purple-400 to-pink-400 rounded-3xl p-6 shadow-lg text-white text-center">
            <div class="w-24 h-24 rounded-full bg-white/25 backdrop-blur-sm flex items-center justify-center mx-auto mb-4 shadow-xl border-4 border-white/40">
                <span class="text-3xl font-bold text-white drop-shadow-md">{{ getInitials(profile.name) }}</span>
            </div>
            <h1 class="text-2xl font-bold drop-shadow-md">{{ profile.name }}</h1>
            <p class="text-white/90 text-sm mt-1">{{ profile.email }}</p>
            <p v-if="profile.location" class="flex items-center justify-center gap-1 text-white/90 text-sm mt-2">
                <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                {{ profile.location }}
            </p>
            <p v-if="profile.bio" class="text-white/95 text-sm mt-3">{{ profile.bio }}</p>
            
            <div v-if="profile.skills?.length" class="flex flex-wrap justify-center gap-2 mt-4">
                <span 
                    v-for="skill in profile.skills.slice(0, 5)" 
                    :key="skill"
                    class="px-3 py-1 bg-white/25 backdrop-blur-sm text-white rounded-full text-xs font-semibold shadow-md"
                >
                    {{ skill }}
                </span>
            </div>
            
            <Link 
                :href="route('lifeplus.profile.settings')"
                class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 bg-white/25 backdrop-blur-sm text-white rounded-xl font-semibold hover:bg-white/35 transition-all shadow-md"
            >
                <PencilIcon class="h-4 w-4" aria-hidden="true" />
                Edit Profile
            </Link>
        </div>

        <!-- Stats Grid with Softer Gradients -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl p-4 shadow-lg text-white">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-white/25 backdrop-blur-sm flex items-center justify-center shadow-md">
                        <ClipboardDocumentCheckIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-3xl font-bold drop-shadow-md">{{ stats.tasks_completed }}</p>
                        <p class="text-xs text-white/90">Tasks Done</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-emerald-400 to-green-500 rounded-2xl p-4 shadow-lg text-white">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-white/25 backdrop-blur-sm flex items-center justify-center shadow-md">
                        <BanknotesIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold drop-shadow-md">{{ formatCurrency(stats.month_spent) }}</p>
                        <p class="text-xs text-white/90">This Month</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl p-4 shadow-lg text-white">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-white/25 backdrop-blur-sm flex items-center justify-center shadow-md">
                        <SparklesIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-3xl font-bold drop-shadow-md">{{ stats.total_streaks }}</p>
                        <p class="text-xs text-white/90">Habit Streaks</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl p-4 shadow-lg text-white">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-white/25 backdrop-blur-sm flex items-center justify-center shadow-md">
                        <BriefcaseIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-3xl font-bold drop-shadow-md">{{ stats.gigs_completed }}</p>
                        <p class="text-xs text-white/90">Gigs Done</p>
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
                        <AcademicCapIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
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
                :href="route('lifeplus.profile.subscription')"
                class="flex items-center justify-between p-4 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 transition-all border-l-4 border-transparent hover:border-indigo-500"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                        <SparklesIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                    </div>
                    <div>
                        <span class="font-medium text-gray-900 block">My Subscription</span>
                        <span class="text-xs text-gray-500">View plan & features</span>
                    </div>
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
                        <RocketLaunchIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
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
