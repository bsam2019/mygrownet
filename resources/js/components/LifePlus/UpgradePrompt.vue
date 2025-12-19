<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useLifePlusAccess } from '@/composables/useLifePlusAccess';
import {
    SparklesIcon,
    LockClosedIcon,
    CheckIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    feature: string;
    title?: string;
    message?: string;
    showBenefits?: boolean;
    compact?: boolean;
}>();

const emit = defineEmits(['close']);

const { tierName, canUpgrade, upgradeBenefits, isFreeTier } = useLifePlusAccess();

const defaultMessages: Record<string, { title: string; message: string }> = {
    chilimba: {
        title: 'Unlock Chilimba Tracker',
        message: 'Track your village banking groups, contributions, and payouts with our Chilimba tracker.',
    },
    community_post: {
        title: 'Post in Community',
        message: 'Share notices, events, and connect with your community.',
    },
    gigs_post: {
        title: 'Post Gig Listings',
        message: 'Create gig listings and find local work opportunities.',
    },
    budget_planning: {
        title: 'Budget Planning',
        message: 'Plan your monthly budget and track your financial goals.',
    },
    analytics: {
        title: 'Analytics & Reports',
        message: 'Get insights into your spending, habits, and productivity.',
    },
    data_export: {
        title: 'Export Your Data',
        message: 'Download your data in CSV or PDF format.',
    },
    tasks_limit: {
        title: 'Task Limit Reached',
        message: 'You\'ve reached the 10 task limit on the free plan.',
    },
    habits_limit: {
        title: 'Habit Limit Reached',
        message: 'You can only track 1 habit on the free plan.',
    },
};

const displayTitle = props.title || defaultMessages[props.feature]?.title || 'Upgrade Required';
const displayMessage = props.message || defaultMessages[props.feature]?.message || 'This feature requires a premium subscription.';
</script>

<template>
    <!-- Compact inline prompt -->
    <div v-if="compact" class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
            <LockClosedIcon class="h-4 w-4 text-amber-600" aria-hidden="true" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-amber-900">{{ displayTitle }}</p>
            <p class="text-xs text-amber-700 truncate">{{ displayMessage }}</p>
        </div>
        <Link
            href="/lifeplus/upgrade"
            class="px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-semibold rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all shadow-md flex-shrink-0"
        >
            Upgrade
        </Link>
    </div>

    <!-- Full card prompt -->
    <div v-else class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-pink-500 p-6 text-white relative">
            <button
                v-if="$attrs.onClose"
                @click="emit('close')"
                class="absolute top-4 right-4 p-1 hover:bg-white/20 rounded-full transition-colors"
                aria-label="Close"
            >
                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
            
            <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4 shadow-lg">
                <LockClosedIcon class="h-7 w-7" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-bold">{{ displayTitle }}</h3>
            <p class="text-amber-100 mt-2 text-sm">{{ displayMessage }}</p>
        </div>

        <!-- Benefits -->
        <div v-if="showBenefits && upgradeBenefits.length > 0" class="p-6">
            <p class="text-sm font-semibold text-gray-900 mb-3">Upgrade to Premium and get:</p>
            <ul class="space-y-2">
                <li v-for="benefit in upgradeBenefits" :key="benefit" class="flex items-center gap-2 text-sm text-gray-700">
                    <CheckIcon class="h-4 w-4 text-emerald-500 flex-shrink-0" aria-hidden="true" />
                    {{ benefit }}
                </li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="p-6 pt-0 space-y-3">
            <Link
                href="/lifeplus/upgrade"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all shadow-lg"
            >
                <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                Upgrade to Premium - K25/month
            </Link>
            
            <p class="text-center text-xs text-gray-500">
                Or <Link href="/join" class="text-emerald-600 font-medium hover:underline">join MyGrowNet</Link> to get it FREE!
            </p>
        </div>
    </div>
</template>
