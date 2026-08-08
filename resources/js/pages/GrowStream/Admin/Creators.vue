<template>
    <AdminLayout title="Creator Management - GrowStream Admin">
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[var(--gs-text)]">Creator Management</h1>
                <p class="mt-2 text-[var(--gs-muted)]">Manage content creators and their permissions</p>
            </div>

            <!-- Stats Cards -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Total Creators</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">{{ stats.total }}</p>
                        </div>
                        <div class="rounded-full bg-[var(--gs-primary-soft)] p-3">
                            <svg class="h-6 w-6 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Verified</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--gs-primary)]">{{ stats.verified }}</p>
                        </div>
                        <div class="rounded-full bg-[var(--gs-primary-soft)] p-3">
                            <svg class="h-6 w-6 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Active</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--gs-primary)]">{{ stats.active }}</p>
                        </div>
                        <div class="rounded-full bg-[var(--gs-primary-soft)] p-3">
                            <svg class="h-6 w-6 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Suspended</p>
                            <p class="mt-2 text-3xl font-bold text-red-400">{{ stats.suspended }}</p>
                        </div>
                        <div class="rounded-full bg-red-500/15 p-3">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    class="gs-input flex-1 min-w-[300px]"
                    @input="debouncedSearch"
                />
                <select
                    v-model="filters.status"
                    class="gs-input w-auto"
                    @change="applyFilters"
                >
                    <option value="">All Status</option>
                    <option value="pending">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
                <select
                    v-model="filters.verified"
                    class="gs-input w-auto"
                    @change="applyFilters"
                >
                    <option value="">All Creators</option>
                    <option value="true">Verified</option>
                    <option value="false">Unverified</option>
                </select>
            </div>

            <!-- Creators Table -->
            <div class="gs-surface overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--gs-border)]">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">
                                Creator
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">
                                Videos
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">
                                Total Views
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">
                                Revenue Share
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--gs-border)]">
                        <tr v-for="creator in creators.data" :key="creator.id" class="hover:bg-[var(--gs-card-hover)]">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="creator.user.profile_photo_url || '/default-avatar.jpg'"
                                        :alt="creator.display_name"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-[var(--gs-text)]">{{ creator.display_name }}</span>
                                            <svg
                                                v-if="creator.is_verified"
                                                class="h-5 w-5 text-[var(--gs-primary)]"
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
                                        <div class="text-sm text-[var(--gs-muted)]">{{ creator.user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="statusChipClass(creator.status)">
                                    {{ creator.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--gs-text)]">
                                {{ creator.total_videos }}
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--gs-text)]">
                                {{ creator.total_views.toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--gs-text)]">
                                {{ creator.revenue_share_percentage }}%
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <!-- Pending application: approve / reject -->
                                    <template v-if="creator.status === 'pending' || creator.status === 'rejected'">
                                        <button
                                            @click="approveCreator(creator.id)"
                                            class="text-[var(--gs-primary)] hover:text-[var(--gs-primary-hover)]"
                                            aria-label="Approve" title="Approve application"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="rejectCreator(creator)"
                                            class="text-red-400 hover:text-red-300"
                                            aria-label="Reject" title="Reject application"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </template>
                                    <button
                                        v-if="!creator.is_verified"
                                        @click="verifyCreator(creator.id)"
                                        class="text-[var(--gs-primary)] hover:text-[var(--gs-primary-hover)]"
                                        aria-label="Verify" title="Verify"
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
                                        class="text-red-400 hover:text-red-300"
                                        aria-label="Suspend" title="Suspend"
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
                                        class="text-[var(--gs-primary)] hover:text-[var(--gs-primary-hover)]"
                                        aria-label="Unsuspend" title="Unsuspend"
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
                                        class="text-[var(--gs-accent)] hover:opacity-85"
                                        aria-label="Edit Limits" title="Edit Limits"
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
                <div class="flex items-center justify-between border-t border-[var(--gs-border)] px-6 py-4">
                    <div class="text-sm text-[var(--gs-muted)]">
                        Showing {{ (creators.meta.current_page - 1) * creators.meta.per_page + 1 }} to
                        {{ Math.min(creators.meta.current_page * creators.meta.per_page, creators.meta.total) }} of
                        {{ creators.meta.total }} results
                    </div>
                    <div class="flex gap-2">
                        <button
                            :disabled="creators.meta.current_page === 1"
                            @click="changePage(creators.meta.current_page - 1)"
                            class="gs-btn gs-btn-outline px-4 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            :disabled="creators.meta.current_page === creators.meta.last_page"
                            @click="changePage(creators.meta.current_page + 1)"
                            class="gs-btn gs-btn-outline px-4 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50"
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
                <div class="fixed inset-0 bg-black/70" @click="showLimitsModal = false"></div>
                <div class="gs-card relative w-full max-w-md p-6">
                    <h3 class="mb-4 text-lg font-semibold text-[var(--gs-text)]">Edit Creator Limits</h3>
                    <form @submit.prevent="saveLimits">
                        <div class="mb-4">
                            <label class="gs-label">Max Upload Size (MB)</label>
                            <input
                                v-model.number="limitsForm.max_upload_size_mb"
                                type="number"
                                class="gs-input"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="gs-label">Max Videos Per Month</label>
                            <input
                                v-model.number="limitsForm.max_videos_per_month"
                                type="number"
                                class="gs-input"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="gs-label">Revenue Share (%)</label>
                            <input
                                v-model.number="limitsForm.revenue_share_percentage"
                                type="number"
                                min="0"
                                max="100"
                                class="gs-input"
                            />
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showLimitsModal = false"
                                class="gs-btn gs-btn-outline"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="gs-btn gs-btn-primary"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4">
                <div class="fixed inset-0 bg-black/70" @click="showRejectModal = false"></div>
                <div class="gs-card relative w-full max-w-md p-6">
                    <h3 class="mb-4 text-lg font-semibold text-[var(--gs-text)]">Reject Creator Application</h3>
                    <form @submit.prevent="confirmReject">
                        <div class="mb-4">
                            <label class="gs-label">Reason (shown to the applicant)</label>
                            <textarea v-model="rejectReason" rows="4" required class="gs-input" placeholder="Tell the creator why their application was not approved..."></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showRejectModal = false" class="gs-btn gs-btn-outline">Cancel</button>
                            <button type="submit" class="gs-btn bg-red-500 text-white">Reject Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
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

// Approve a pending creator application (web-session route, Inertia redirect).
const approveCreator = (creatorId: number) => {
    router.post(route('growstream.admin.creators.approve', { id: creatorId }), {}, { preserveScroll: true });
};

const showRejectModal = ref(false);
const rejectReason = ref('');
const rejectTarget = ref<number | null>(null);

const rejectCreator = (creator: CreatorProfile) => {
    rejectTarget.value = creator.id;
    rejectReason.value = '';
    showRejectModal.value = true;
};

const confirmReject = () => {
    if (!rejectTarget.value || !rejectReason.value.trim()) return;
    router.post(
        route('growstream.admin.creators.reject', { id: rejectTarget.value }),
        { reason: rejectReason.value.trim() },
        { preserveScroll: true }
    );
    showRejectModal.value = false;
};

const statusChipClass = (status: string): string => {
    switch (status) {
        case 'approved':
        case 'active':
            return 'gs-chip gs-chip-primary';
        case 'pending':
            return 'gs-chip bg-yellow-500/15 text-yellow-400';
        case 'rejected':
            return 'gs-chip bg-red-500/15 text-red-400';
        case 'suspended':
            return 'gs-chip bg-orange-500/15 text-orange-400';
        default:
            return 'gs-chip bg-gray-500/15 text-gray-400';
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

