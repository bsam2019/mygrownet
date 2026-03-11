<template>
    <AppLayout title="Creator Management - GrowStream Admin">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Creator Management</h1>
                <p class="mt-2 text-gray-600">Manage content creators and their permissions</p>
            </div>

            <!-- Stats Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Creators</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Verified</p>
                            <p class="mt-2 text-3xl font-bold text-green-600">{{ stats.verified }}</p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active</p>
                            <p class="mt-2 text-3xl font-bold text-blue-600">{{ stats.active }}</p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Suspended</p>
                            <p class="mt-2 text-3xl font-bold text-red-600">{{ stats.suspended }}</p>
                        </div>
                        <div class="rounded-full bg-red-100 p-3">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-wrap gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search creators..."
                    class="flex-1 min-w-[300px] rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @input="debouncedSearch"
                />
                <select
                    v-model="filters.status"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @change="applyFilters"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
                <select
                    v-model="filters.verified"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @change="applyFilters"
                >
                    <option value="">All Creators</option>
                    <option value="true">Verified</option>
                    <option value="false">Unverified</option>
                </select>
            </div>

            <!-- Creators Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Creator
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Videos
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Total Views
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Revenue Share
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="creator in creators.data" :key="creator.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="creator.user.profile_photo_url || '/default-avatar.jpg'"
                                        :alt="creator.display_name"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">{{ creator.display_name }}</span>
                                            <svg
                                                v-if="creator.is_verified"
                                                class="h-5 w-5 text-blue-600"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                        <div class="text-sm text-gray-500">{{ creator.user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        creator.status === 'active'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800',
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    ]"
                                >
                                    {{ creator.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ creator.total_videos }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ creator.total_views.toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ creator.revenue_share_percentage }}%
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <button
                                        v-if="!creator.is_verified"
                                        @click="verifyCreator(creator.id)"
                                        class="text-green-600 hover:text-green-900"
                                        title="Verify"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-if="creator.status === 'active'"
                                        @click="suspendCreator(creator.id)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Suspend"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-else
                                        @click="unsuspendCreator(creator.id)"
                                        class="text-green-600 hover:text-green-900"
                                        title="Unsuspend"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="editLimits(creator)"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit Limits"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-4">
                    <div class="text-sm text-gray-700">
                        Showing {{ (creators.meta.current_page - 1) * creators.meta.per_page + 1 }} to
                        {{ Math.min(creators.meta.current_page * creators.meta.per_page, creators.meta.total) }} of
                        {{ creators.meta.total }} results
                    </div>
                    <div class="flex gap-2">
                        <button
                            :disabled="creators.meta.current_page === 1"
                            @click="changePage(creators.meta.current_page - 1)"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            :disabled="creators.meta.current_page === creators.meta.last_page"
                            @click="changePage(creators.meta.current_page + 1)"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Limits Modal -->
        <div v-if="showLimitsModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4">
                <div class="fixed inset-0 bg-black/50" @click="showLimitsModal = false"></div>
                <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Edit Creator Limits</h3>
                    <form @submit.prevent="saveLimits">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Max Upload Size (MB)</label>
                            <input
                                v-model.number="limitsForm.max_upload_size_mb"
                                type="number"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Max Videos Per Month</label>
                            <input
                                v-model.number="limitsForm.max_videos_per_month"
                                type="number"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Revenue Share (%)</label>
                            <input
                                v-model.number="limitsForm.revenue_share_percentage"
                                type="number"
                                min="0"
                                max="100"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showLimitsModal = false"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useGrowStreamAdmin } from '@/composables/useGrowStreamAdmin';
import type { CreatorProfile, PaginatedResponse } from '@/types/growstream';

interface Props {
    creators: PaginatedResponse<CreatorProfile>;
}

const props = defineProps<Props>();

const { verifyCreator: verifyCreatorApi, suspendCreator: suspendCreatorApi, updateCreatorLimits } =
    useGrowStreamAdmin();

const filters = reactive({
    search: '',
    status: '',
    verified: '',
    page: 1,
});

const showLimitsModal = ref(false);
const selectedCreator = ref<CreatorProfile | null>(null);
const limitsForm = reactive({
    max_upload_size_mb: 0,
    max_videos_per_month: 0,
    revenue_share_percentage: 0,
});

const stats = computed(() => ({
    total: props.creators.meta.total,
    verified: props.creators.data.filter((c) => c.is_verified).length,
    active: props.creators.data.filter((c) => c.status === 'active').length,
    suspended: props.creators.data.filter((c) => c.status === 'suspended').length,
}));

let searchTimeout: ReturnType<typeof setTimeout>;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(
        route('growstream.admin.creators'),
        { ...filters, page: 1 },
        { preserveState: true, preserveScroll: true }
    );
};

const changePage = (page: number) => {
    router.get(
        route('growstream.admin.creators'),
        { ...filters, page },
        { preserveState: true, preserveScroll: false }
    );
};

const verifyCreator = async (creatorId: number) => {
    try {
        await verifyCreatorApi(creatorId);
        router.reload({ only: ['creators'] });
    } catch (error) {
        console.error('Failed to verify creator:', error);
    }
};

const suspendCreator = async (creatorId: number) => {
    if (!confirm('Are you sure you want to suspend this creator?')) return;

    try {
        await suspendCreatorApi(creatorId);
        router.reload({ only: ['creators'] });
    } catch (error) {
        console.error('Failed to suspend creator:', error);
    }
};

const unsuspendCreator = async (creatorId: number) => {
    try {
        await suspendCreatorApi(creatorId); // Same endpoint toggles
        router.reload({ only: ['creators'] });
    } catch (error) {
        console.error('Failed to unsuspend creator:', error);
    }
};

const editLimits = (creator: CreatorProfile) => {
    selectedCreator.value = creator;
    limitsForm.max_upload_size_mb = creator.max_upload_size_mb;
    limitsForm.max_videos_per_month = creator.max_videos_per_month;
    limitsForm.revenue_share_percentage = creator.revenue_share_percentage;
    showLimitsModal.value = true;
};

const saveLimits = async () => {
    if (!selectedCreator.value) return;

    try {
        await updateCreatorLimits(selectedCreator.value.id, limitsForm);
        showLimitsModal.value = false;
        router.reload({ only: ['creators'] });
    } catch (error) {
        console.error('Failed to update limits:', error);
    }
};
</script>
