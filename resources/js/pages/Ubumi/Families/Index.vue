<template>
    <UbumiLayout title="My Families - Ubumi">
        <div class="py-6 md:py-12 px-4 md:px-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My Families</h1>
                        <p class="mt-2 text-sm md:text-base text-gray-600">Manage your family trees and connections</p>
                    </div>
                    <Link
                        :href="route('ubumi.families.create')"
                        class="hidden md:inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 border border-transparent rounded-xl font-semibold text-white hover:shadow-lg transition-all"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Family
                    </Link>
                </div>

                <!-- Families Grid -->
                <div v-if="families.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    <div
                        v-for="family in families"
                        :key="family.id"
                        class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200"
                    >
                        <div class="p-4 md:p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg md:text-xl font-bold text-gray-900 mb-2">{{ family.name }}</h3>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                        Admin
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Created {{ formatDate(family.created_at) }}
                            </div>

                            <Link
                                :href="route('ubumi.families.show', family.slug)"
                                class="inline-flex items-center justify-center w-full px-4 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all active:scale-95"
                            >
                                View Family Tree
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-lg p-8 md:p-12 text-center">
                    <div class="w-20 h-20 mx-auto bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900">No families yet</h3>
                    <p class="mt-2 text-sm md:text-base text-gray-600">Get started by creating your first family tree.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('ubumi.families.create')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 border border-transparent rounded-xl font-semibold text-white hover:shadow-lg transition-all active:scale-95"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Your First Family
                        </Link>
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
    admin_user_id: number;
    created_at: string;
}

defineProps<{
    families: Family[];
}>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>
