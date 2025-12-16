<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    MapPinIcon,
    BriefcaseIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Gig {
    id: number;
    title: string;
    description: string | null;
    category_icon: string;
    formatted_payment: string;
    location: string | null;
    status: string;
    status_color: string;
    posted_at: string;
}

interface Application {
    id: number;
    gig: Gig;
    message: string | null;
    status: string;
    applied_at: string;
}

const props = defineProps<{
    gigs: Gig[];
    applications: Application[];
}>();

const activeTab = ref<'posted' | 'applied'>('posted');

const getStatusLabel = (status: string) => {
    return {
        open: 'Open',
        assigned: 'Assigned',
        completed: 'Completed',
        cancelled: 'Cancelled',
        pending: 'Pending',
        accepted: 'Accepted',
        rejected: 'Rejected',
    }[status] || status;
};

const getStatusColor = (status: string) => {
    return {
        open: 'bg-emerald-100 text-emerald-700',
        assigned: 'bg-amber-100 text-amber-700',
        completed: 'bg-blue-100 text-blue-700',
        cancelled: 'bg-gray-100 text-gray-700',
        pending: 'bg-amber-100 text-amber-700',
        accepted: 'bg-emerald-100 text-emerald-700',
        rejected: 'bg-red-100 text-red-700',
    }[status] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.gigs.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900">My Gigs</h1>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 bg-gray-100 p-1 rounded-xl">
            <button 
                @click="activeTab = 'posted'"
                :class="[
                    'flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-colors',
                    activeTab === 'posted' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600'
                ]"
            >
                Posted ({{ gigs.length }})
            </button>
            <button 
                @click="activeTab = 'applied'"
                :class="[
                    'flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-colors',
                    activeTab === 'applied' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600'
                ]"
            >
                Applied ({{ applications.length }})
            </button>
        </div>

        <!-- Posted Gigs -->
        <div v-if="activeTab === 'posted'" class="space-y-3">
            <div v-if="gigs.length === 0" class="text-center py-12">
                <BriefcaseIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">You haven't posted any gigs</p>
                <Link 
                    :href="route('lifeplus.gigs.create')"
                    class="mt-3 inline-block text-blue-600 font-medium"
                >
                    Post your first gig
                </Link>
            </div>

            <Link 
                v-for="gig in gigs" 
                :key="gig.id"
                :href="route('lifeplus.gigs.show', gig.id)"
                class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start gap-3">
                    <span class="text-2xl">{{ gig.category_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span 
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="getStatusColor(gig.status)"
                            >
                                {{ getStatusLabel(gig.status) }}
                            </span>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ gig.title }}</h3>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-sm font-semibold text-emerald-600">
                                {{ gig.formatted_payment }}
                            </span>
                            <span v-if="gig.location" class="flex items-center gap-1 text-xs text-gray-500">
                                <MapPinIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ gig.location }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">{{ gig.posted_at }}</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Applied Gigs -->
        <div v-if="activeTab === 'applied'" class="space-y-3">
            <div v-if="applications.length === 0" class="text-center py-12">
                <BriefcaseIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">You haven't applied to any gigs</p>
                <Link 
                    :href="route('lifeplus.gigs.index')"
                    class="mt-3 inline-block text-blue-600 font-medium"
                >
                    Browse available gigs
                </Link>
            </div>

            <div 
                v-for="app in applications" 
                :key="app.id"
                class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
            >
                <div class="flex items-start gap-3">
                    <span class="text-2xl">{{ app.gig.category_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span 
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="getStatusColor(app.status)"
                            >
                                {{ getStatusLabel(app.status) }}
                            </span>
                            <span class="text-xs text-gray-400">Applied {{ app.applied_at }}</span>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ app.gig.title }}</h3>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-sm font-semibold text-emerald-600">
                                {{ app.gig.formatted_payment }}
                            </span>
                            <span v-if="app.gig.location" class="flex items-center gap-1 text-xs text-gray-500">
                                <MapPinIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ app.gig.location }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
