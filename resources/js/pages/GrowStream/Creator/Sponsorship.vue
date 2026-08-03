<template>
    <AppLayout title="Sponsorship - GrowStream Creator">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Sponsorship Fund</h1>
                <p class="mt-2 text-gray-600">Apply for funding to produce your next project</p>
            </div>

            <form @submit.prevent="submit" class="mb-8 space-y-6 rounded-lg bg-white p-8 shadow">
                <div v-if="form.errors.length > 0" class="rounded-md bg-red-50 p-4">
                    <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Project Title *</label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Describe the project, target audience, and expected outcomes"
                        required
                    ></textarea>
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Requested Amount (ZMW) *</label>
                    <input
                        id="amount"
                        v-model="form.amount"
                        type="number"
                        min="1"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    />
                </div>

                <div>
                    <label for="milestones" class="block text-sm font-medium text-gray-700">Milestones</label>
                    <input
                        id="milestones"
                        v-model="milestonesInput"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="comma separated milestones"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-md bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Submitting...' : 'Submit Proposal' }}
                </button>
            </form>

            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">My Proposals</h2>
                </div>
                <div v-if="grants.length === 0" class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-600">No proposals yet.</p>
                </div>
                <ul v-else class="divide-y divide-gray-200">
                    <li v-for="grant in grants" :key="grant.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ grant.title }}</p>
                            <span
                                :class="{
                                    'bg-amber-100 text-amber-800': grant.status === 'submitted',
                                    'bg-blue-100 text-blue-800': grant.status === 'approved',
                                    'bg-green-100 text-green-800': grant.status === 'disbursed' || grant.status === 'completed',
                                    'bg-red-100 text-red-800': grant.status === 'rejected',
                                }"
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                            >
                                {{ grant.status }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                            K{{ Number(grant.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                        </p>
                        <p v-if="grant.rejection_reason" class="mt-1 text-sm text-red-600">
                            {{ grant.rejection_reason }}
                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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
</script>
