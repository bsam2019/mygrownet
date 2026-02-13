<template>
    <UbumiLayout title="Ubumi - Family Tree">
        <div class="py-6 md:py-12 px-4 md:px-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                        Welcome to Ubumi
                    </h1>
                    <p class="text-base md:text-lg text-gray-600">
                        Your family lineage and wellness platform
                    </p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-4 md:p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">{{ stats.families }}</div>
                        <div class="text-sm opacity-90">{{ stats.families === 1 ? 'Family' : 'Families' }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-4 md:p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">{{ stats.persons }}</div>
                        <div class="text-sm opacity-90">{{ stats.persons === 1 ? 'Person' : 'People' }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl p-4 md:p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">{{ stats.relationships }}</div>
                        <div class="text-sm opacity-90">Connections</div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-4 md:p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">Active</div>
                        <div class="text-sm opacity-90">Status</div>
                    </div>
                </div>

                <!-- Family Wellness Check-Ins -->
                <div v-if="recentFamilies.length > 0" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900">Family Wellness</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Link
                            v-for="family in recentFamilies"
                            :key="family.id"
                            :href="route('ubumi.families.check-ins.dashboard', family.slug)"
                            class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl shadow-lg hover:shadow-xl transition-all p-6 group"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-green-600 transition-colors">
                                            {{ family.name }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ family.member_count }} {{ family.member_count === 1 ? 'member' : 'members' }}
                                        </p>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-green-700 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                View Wellness Dashboard
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Recent Families -->
                <div v-if="recentFamilies.length > 0" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900">Recent Families</h2>
                        <Link
                            :href="route('ubumi.families.index')"
                            class="text-purple-600 hover:text-purple-700 font-semibold text-sm"
                        >
                            View All →
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Link
                            v-for="family in recentFamilies"
                            :key="family.id"
                            :href="route('ubumi.families.show', family.slug)"
                            class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all p-6 group"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors">
                                    {{ family.name }}
                                </h3>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ family.member_count }} {{ family.member_count === 1 ? 'member' : 'members' }}
                            </p>
                        </Link>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <Link
                        :href="route('ubumi.families.create')"
                        class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all group"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-1">Create New Family</h3>
                                <p class="text-sm opacity-90">Start a new family tree</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        :href="route('ubumi.persons.index')"
                        class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all group"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-1">View All People</h3>
                                <p class="text-sm opacity-90">Browse family members</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        v-if="recentFamilies.length > 0"
                        :href="route('ubumi.families.check-ins.dashboard', recentFamilies[0].slug)"
                        class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all group"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-1">Health Check-Ins</h3>
                                <p class="text-sm opacity-90">Monitor family wellness</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-if="recentFamilies.length === 0" class="space-y-6">
                    <!-- Main Empty State -->
                    <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <div class="w-20 h-20 mx-auto bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Start Your Family Tree</h3>
                        <p class="text-gray-600 mb-6">
                            Create your first family to begin documenting your lineage and staying connected with loved ones.
                        </p>
                        <Link
                            :href="route('ubumi.families.create')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Your First Family
                        </Link>
                    </div>

                    <!-- Health Check-In Feature Info -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl shadow-lg p-8">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Health Check-In Feature</h3>
                                <p class="text-gray-700 mb-4">
                                    Once you create a family and add members, you'll be able to:
                                </p>
                                <ul class="space-y-2 text-gray-700 mb-4">
                                    <li class="flex items-start gap-2">
                                        <span class="text-2xl">😊</span>
                                        <span><strong>Check in on family members</strong> - Simple wellness updates with 3 status options</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-2xl">🔔</span>
                                        <span><strong>Receive alerts</strong> - Get notified when someone needs help or isn't feeling well</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-2xl">📊</span>
                                        <span><strong>Monitor wellness</strong> - View family-wide wellness dashboard with check-in history</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-2xl">💚</span>
                                        <span><strong>Stay connected</strong> - Keep track of your loved ones' wellbeing across distances</span>
                                    </li>
                                </ul>
                                <div class="bg-white/50 rounded-xl p-4 border border-green-200">
                                    <p class="text-sm text-gray-600 italic">
                                        <strong>Note:</strong> Ubumi is a family communication tool, not a medical system. 
                                        It helps families stay informed about each other's wellbeing.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UbumiLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UbumiLayout from '@/layouts/UbumiLayout.vue';

interface Family {
    id: string;
    slug: string;
    name: string;
    member_count: number;
}

interface Stats {
    families: number;
    persons: number;
    relationships: number;
}

defineProps<{
    stats: Stats;
    recentFamilies: Family[];
}>();
</script>
