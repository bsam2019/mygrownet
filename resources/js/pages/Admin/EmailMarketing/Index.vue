<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';

interface Campaign {
    id: number;
    name: string;
    type: string;
    status: string;
    trigger_type: string;
    subscribers_count: number;
    sequences_count: number;
    created_at: string;
}

interface Props {
    campaigns: {
        data: Campaign[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

const statusColors = {
    draft: 'bg-gray-100 text-gray-800',
    active: 'bg-green-100 text-green-800',
    paused: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-blue-100 text-blue-800',
};

const typeLabels = {
    onboarding: 'Onboarding',
    engagement: 'Engagement',
    reactivation: 'Re-activation',
    upgrade: 'Upgrade',
    custom: 'Custom',
};

const activateCampaign = async (id: number) => {
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
        router.post(route('admin.email-campaigns.activate', id));
    }
};

const pauseCampaign = async (id: number) => {
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
        router.post(route('admin.email-campaigns.pause', id));
    }
};

const deleteCampaign = async (id: number) => {
    const result = await Swal.fire({
        title: 'Delete Campaign?',
        text: 'This action cannot be undone!',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        router.delete(route('admin.email-campaigns.destroy', id));
    }
};
</script>

<template>
    <Head title="Email Campaigns" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Email Campaigns</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage automated email campaigns and sequences
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('admin.email-campaigns.templates')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Templates
                        </Link>
                        <Link
                            :href="route('admin.email-campaigns.analytics')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Analytics
                        </Link>
                        <Link
                            :href="route('admin.email-campaigns.create')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                        >
                            Create Campaign
                        </Link>
                    </div>
                </div>

                <!-- Campaigns List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Campaign
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subscribers
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Emails
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Link
                                        :href="route('admin.email-campaigns.show', campaign.id)"
                                        class="text-sm font-medium text-blue-600 hover:text-blue-800"
                                    >
                                        {{ campaign.name }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">
                                        {{ typeLabels[campaign.type] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="statusColors[campaign.status]"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ campaign.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ campaign.subscribers_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ campaign.sequences_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(campaign.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            v-if="campaign.status === 'draft'"
                                            :href="route('admin.email-campaigns.edit', campaign.id)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            v-if="campaign.status === 'draft'"
                                            @click="activateCampaign(campaign.id)"
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Activate
                                        </button>
                                        <button
                                            v-if="campaign.status === 'active'"
                                            @click="pauseCampaign(campaign.id)"
                                            class="text-yellow-600 hover:text-yellow-900"
                                        >
                                            Pause
                                        </button>
                                        <button
                                            v-if="campaign.status === 'draft'"
                                            @click="deleteCampaign(campaign.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="campaigns.data.length === 0" class="text-center py-12">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new email campaign.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('admin.email-campaigns.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            >
                                Create Campaign
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="campaigns.last_page > 1" class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <Link
                            v-for="page in campaigns.last_page"
                            :key="page"
                            :href="route('admin.email-campaigns.index', { page })"
                            :class="[
                                page === campaigns.current_page
                                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </nav>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
