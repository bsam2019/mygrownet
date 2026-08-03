<template>
    <CreatorStudioLayout title="Sponsorship - GrowStream Creator">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[var(--gs-text)]">Sponsorship Fund</h1>
                <p class="mt-2 text-[var(--gs-muted)]">Apply for funding to produce your next project</p>
            </div>

            <form @submit.prevent="submit" class="gs-card mb-8 space-y-5 p-6">
                <div v-if="form.errors.length > 0" class="rounded-md bg-red-500/10 p-4">
                    <ul class="list-disc space-y-1 pl-5 text-sm text-red-400">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <div>
                    <label for="title" class="gs-label">Project Title *</label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="gs-input"
                        required
                    />
                </div>

                <div>
                    <label for="description" class="gs-label">Description *</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="gs-input"
                        placeholder="Describe the project, target audience, and expected outcomes"
                        required
                    ></textarea>
                </div>

                <div>
                    <label for="amount" class="gs-label">Requested Amount (ZMW) *</label>
                    <input
                        id="amount"
                        v-model="form.amount"
                        type="number"
                        min="1"
                        step="0.01"
                        class="gs-input"
                        required
                    />
                </div>

                <div>
                    <label for="milestones" class="gs-label">Milestones</label>
                    <input
                        id="milestones"
                        v-model="milestonesInput"
                        type="text"
                        class="gs-input"
                        placeholder="comma separated milestones"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="gs-btn gs-btn-primary w-full"
                >
                    {{ form.processing ? 'Submitting...' : 'Submit Proposal' }}
                </button>
            </form>

            <div class="gs-surface overflow-hidden">
                <div class="border-b border-[var(--gs-border)] px-6 py-4">
                    <h2 class="text-lg font-semibold text-[var(--gs-text)]">My Proposals</h2>
                </div>
                <div v-if="grants.length === 0" class="px-6 py-12 text-center">
                    <p class="text-sm text-[var(--gs-muted)]">No proposals yet.</p>
                </div>
                <ul v-else class="divide-y divide-[var(--gs-border)]">
                    <li v-for="grant in grants" :key="grant.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-[var(--gs-text)]">{{ grant.title }}</p>
                            <span :class="statusBadge(grant.status)" class="gs-chip">
                                {{ grant.status }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-[var(--gs-muted)]">
                            K{{ Number(grant.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                        </p>
                        <p v-if="grant.rejection_reason" class="mt-1 text-sm text-red-400">
                            {{ grant.rejection_reason }}
                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';

interface Grant {
    id: number;
    title: string;
    amount: number;
    status: string;
    rejection_reason?: string | null;
}

interface Props {
    grants: Grant[];
    stats: { total_approved: number; total_disbursed: number };
}

defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    amount: '',
    milestones: [] as string[],
});

const milestonesInput = ref('');

const submit = () => {
    form.milestones = milestonesInput.value.split(',').map((m) => m.trim()).filter(Boolean);
    form.post(route('growstream.creator.sponsorship.store'));
};

const statusBadge = (status: string): string => {
    switch (status) {
        case 'submitted':
            return 'gs-chip-accent';
        case 'approved':
            return 'bg-blue-500/15 text-blue-400';
        case 'disbursed':
        case 'completed':
            return 'gs-chip-primary';
        case 'rejected':
            return 'bg-red-500/15 text-red-400';
        default:
            return 'gs-chip-primary';
    }
};
</script>
