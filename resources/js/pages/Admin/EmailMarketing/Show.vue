<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';

interface Template {
    id: number;
    name: string;
    subject: string;
}

interface Sequence {
    id: number;
    sequence_order: number;
    delay_days: number;
    delay_hours: number;
    template: Template;
}

interface Subscriber {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    status: string;
    enrolled_at: string;
}

interface Campaign {
    id: number;
    name: string;
    type: string;
    status: string;
    trigger_type: string;
    trigger_config: any;
    start_date: string | null;
    end_date: string | null;
    sequences: Sequence[];
    subscribers: Subscriber[];
    subscribers_count: number;
    sequences_count: number;
    created_at: string;
}

interface Stats {
    total_sent: number;
    total_delivered: number;
    total_opened: number;
    total_clicked: number;
    open_rate: number;
    click_rate: number;
}

interface Props {
    campaign: Campaign;
    stats: Stats;
}

const props = defineProps<Props>();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    active: 'bg-green-100 text-green-800',
    paused: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-blue-100 text-blue-800',
};

const typeLabels: Record<string, string> = {
    onboarding: 'Onboarding',
    engagement: 'Engagement',
    reactivation: 'Re-activation',
    upgrade: 'Upgrade',
    custom: 'Custom',
};

const activateCampaign = async () => {
    const result = await Swal.fire({
        title: 'Activate Campaign?',
        text: 'This campaign will start sending emails to enrolled members.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Activate',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        router.post(route('admin.email-campaigns.activate', props.campaign.id));
    }
};

const pauseCampaign = async () => {
    const result = await Swal.fire({
        title: 'Pause Campaign?',
        text: 'Email sending will be temporarily stopped.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Pause',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        router.post(route('admin.email-campaigns.pause', props.campaign.id));
    }
};

const resumeCampaign = async () => {
    const result = await Swal.fire({
        title: 'Resume Campaign?',
        text: 'This campaign will start sending emails again.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Resume',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        router.post(route('admin.email-campaigns.resume', props.campaign.id));
    }
};

const deleteCampaign = async () => {
    const result = await Swal.fire({
        title: 'Delete Campaign?',
        text: 'This action cannot be undone!',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        dangerMode: true
    });

    if (result.isConfirmed) {
        router.delete(route('admin.email-campaigns.destroy', props.campaign.id));
    }
};

const formatNumber = (num: number) => new Intl.NumberFormat().format(num);
const formatPercentage = (num: number) => `${num.toFixed(1)}%`;
</script>

<template>
    <Head :title="`Campaign: ${campaign.name}`" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-2xl font-bold text-gray-900">{{ campaign.name }}</h1>
                                <span
                                    :class="statusColors[campaign.status]"
                                    class="px-3 py-1 text-sm font-semibold rounded-full"
                                >
                                    {{ campaign.status }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ typeLabels[campaign.type] }} Campaign
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a
                                :href="route('admin.email-campaigns.index')"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Back
                            </a>
                            <a
                                v-if="campaign.status === 'draft'"
                                :href="route('admin.email-campaigns.edit', campaign.id)"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Edit
                            </a>
                            <button
                                v-if="campaign.status === 'draft'"
                                @click="activateCampaign"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700"
                            >
                                Activate
                            </button>
                            <button
                                v-if="campaign.status === 'active'"
                                @click="pauseCampaign"
                                class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-medium hover:bg-yellow-700"
                            >
                                Pause
                            </button>
                            <button
                                v-if="campaign.status === 'paused'"
                                @click="resumeCampaign"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                            >
                                Resume
                            </button>
                            <button
                                v-if="campaign.status === 'draft'"
                                @click="deleteCampaign"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm font-medium text-gray-600">Subscribers</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatNumber(campaign.subscribers_count) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm font-medium text-gray-600">Emails Sent</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatNumber(stats.total_sent) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm font-medium text-gray-600">Open Rate</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatPercentage(stats.open_rate) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm font-medium text-gray-600">Click Rate</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatPercentage(stats.click_rate) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Email Sequence -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Sequence</h2>
                        <div class="space-y-4">
                            <div
                                v-for="sequence in campaign.sequences"
                                :key="sequence.id"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-600 rounded-full text-sm font-semibold">
                                            {{ sequence.sequence_order }}
                                        </span>
                                        <h3 class="font-medium text-gray-900">{{ sequence.template.name }}</h3>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        {{ sequence.delay_days }}d {{ sequence.delay_hours }}h
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 ml-8">{{ sequence.template.subject }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Subscribers -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Subscribers</h2>
                        <div class="space-y-3">
                            <div
                                v-for="subscriber in campaign.subscribers"
                                :key="subscriber.id"
                                class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
                            >
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ subscriber.user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ subscriber.user.email }}</p>
                                </div>
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800': subscriber.status === 'active',
                                        'bg-blue-100 text-blue-800': subscriber.status === 'enrolled',
                                        'bg-gray-100 text-gray-800': subscriber.status === 'completed',
                                    }"
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ subscriber.status }}
                                </span>
                            </div>
                            <div v-if="campaign.subscribers.length === 0" class="text-center py-4 text-sm text-gray-500">
                                No subscribers yet
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
