<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import {
    BanknotesIcon,
    CalendarIcon,
    ClockIcon,
    CheckCircleIcon,
    ArrowRightIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface LiquidityEvent {
    id: number;
    title: string;
    description: string;
    event_type: string;
    status: string;
    announcement_date: string;
    registration_deadline: string | null;
    expected_completion: string | null;
    price_per_share: number | null;
    total_budget: number | null;
}

interface Participation {
    id: number;
    liquidity_event_id: number;
    status: string;
    shares_offered: number | null;
    shares_accepted: number | null;
    amount_to_receive: number | null;
    amount_received: number | null;
    liquidity_event: LiquidityEvent;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    events: LiquidityEvent[];
    participations: Participation[];
    currentShares: number | string;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

// Convert currentShares to number (may come as string from backend)
const currentSharesNum = computed(() => Number(props.currentShares) || 0);

const getEventTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        buyback: 'Share Buyback',
        acquisition: 'Acquisition',
        merger: 'Merger',
        special_dividend: 'Special Dividend',
        rights_issue: 'Rights Issue',
        other: 'Other',
    };
    return labels[type] || type;
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'announced': return 'text-blue-600 bg-blue-100';
        case 'open': return 'text-green-600 bg-green-100';
        case 'closed': return 'text-yellow-600 bg-yellow-100';
        case 'completed': return 'text-gray-600 bg-gray-100';
        case 'cancelled': return 'text-red-600 bg-red-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};

const getParticipationStatus = (eventId: number) => {
    return props.participations.find(p => p.liquidity_event_id === eventId);
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const registerInterest = (eventId: number) => {
    useForm({}).post(route('investor.liquidity-events.interest', eventId));
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Liquidity Events" 
        :active-page="activePage || 'liquidity-events'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Liquidity Events" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Liquidity Events</h1>
                <p class="text-gray-600 mt-1">
                    Company-initiated opportunities to sell your shares
                </p>
            </div>

            <!-- Current Holdings Card -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 text-white mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <BanknotesIcon class="h-6 w-6" aria-hidden="true" />
                    <span class="text-emerald-100">Your Eligible Holdings</span>
                </div>
                <div class="text-3xl font-bold">{{ currentSharesNum.toFixed(4) }}%</div>
                <p class="text-emerald-100 text-sm mt-2">
                    Available for participation in liquidity events
                </p>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                <div class="flex gap-3">
                    <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div>
                        <h3 class="font-medium text-blue-800">About Liquidity Events</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Liquidity events are company-initiated opportunities for shareholders to sell their shares.
                            These may include share buybacks, acquisitions, or special dividend distributions.
                            Participation is voluntary and subject to eligibility criteria.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Active Events -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Events</h2>
                
                <div v-if="events.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                    <CalendarIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No active liquidity events at this time</p>
                    <p class="text-sm text-gray-400 mt-1">Check back later for opportunities</p>
                </div>

                <div v-else class="grid gap-4">
                    <div
                        v-for="event in events"
                        :key="event.id"
                        class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span :class="[getStatusColor(event.status), 'px-3 py-1 rounded-full text-sm font-medium']">
                                        {{ event.status.charAt(0).toUpperCase() + event.status.slice(1) }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ getEventTypeLabel(event.event_type) }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ event.title }}</h3>
                                <p class="text-gray-600 mt-1">{{ event.description }}</p>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                    <div>
                                        <p class="text-xs text-gray-500">Announced</p>
                                        <p class="font-medium">{{ formatDate(event.announcement_date) }}</p>
                                    </div>
                                    <div v-if="event.registration_deadline">
                                        <p class="text-xs text-gray-500">Registration Deadline</p>
                                        <p class="font-medium">{{ formatDate(event.registration_deadline) }}</p>
                                    </div>
                                    <div v-if="event.price_per_share">
                                        <p class="text-xs text-gray-500">Price per Share</p>
                                        <p class="font-medium text-green-600">{{ formatCurrency(event.price_per_share) }}</p>
                                    </div>
                                    <div v-if="event.total_budget">
                                        <p class="text-xs text-gray-500">Total Budget</p>
                                        <p class="font-medium">{{ formatCurrency(event.total_budget) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="ml-4">
                                <template v-if="getParticipationStatus(event.id)">
                                    <div class="text-center">
                                        <CheckCircleIcon class="h-8 w-8 text-green-500 mx-auto" aria-hidden="true" />
                                        <p class="text-sm text-green-600 font-medium mt-1">
                                            {{ getParticipationStatus(event.id)?.status }}
                                        </p>
                                    </div>
                                </template>
                                <template v-else-if="event.status === 'open'">
                                    <button
                                        @click="registerInterest(event.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                    >
                                        Register Interest
                                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Participations -->
            <div v-if="participations.length > 0">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">My Participations</h2>
                
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shares Offered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="participation in participations" :key="participation.id">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ participation.liquidity_event.title }}</p>
                                    <p class="text-sm text-gray-500">{{ getEventTypeLabel(participation.liquidity_event.event_type) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[getStatusColor(participation.status), 'px-2 py-1 rounded-full text-xs font-medium']">
                                        {{ participation.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="participation.shares_offered">{{ participation.shares_offered.toFixed(4) }}%</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="participation.amount_received" class="text-green-600 font-medium">
                                        {{ formatCurrency(participation.amount_received) }}
                                    </span>
                                    <span v-else-if="participation.amount_to_receive" class="text-gray-600">
                                        {{ formatCurrency(participation.amount_to_receive) }} (est.)
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
