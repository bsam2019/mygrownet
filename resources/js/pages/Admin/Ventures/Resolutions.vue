<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { PlusIcon, CheckCircleIcon, XCircleIcon, PlayIcon } from '@heroicons/vue/24/outline';

interface Resolution {
    id: number;
    title: string;
    description: string;
    type: string;
    status: string;
    voting_ends_at: string | null;
    votes_for: number;
    votes_against: number;
    votes_abstain: number;
    pass_threshold_percentage: number;
    result_notes: string | null;
    created_at: string;
    creator: { name: string };
}

interface Props {
    venture: { id: number; title: string; slug: string };
    resolutions: Resolution[];
}

const props = defineProps<Props>();
const showForm = ref(false);

const form = ref({ title: '', description: '', type: 'ordinary', voting_ends_at: '', pass_threshold_percentage: 50 });
const submittingForm = ref(false);
const submitting = ref<Record<string, boolean>>({});

const createResolution = () => {
    submittingForm.value = true;
    router.post(route('admin.ventures.resolutions.create', props.venture.id), form.value, {
        onSuccess: () => { showForm.value = false; form.value = { title: '', description: '', type: 'ordinary', voting_ends_at: '', pass_threshold_percentage: 50 }; },
        onFinish: () => { submittingForm.value = false; },
    });
};

const openVoting = (id: number) => {
    if (!confirm('Open voting for this resolution? Shareholders will be able to vote.')) return;
    router.post(route('admin.ventures.resolutions.open-voting', id));
};

const tallyResults = (id: number) => {
    if (!confirm('Tally the votes and finalize this resolution?')) return;
    router.post(route('admin.ventures.resolutions.tally', id));
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800', voting: 'bg-green-100 text-green-800',
        passed: 'bg-blue-100 text-blue-800', rejected: 'bg-red-100 text-red-800', cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Resolutions - ${venture.title}`" />
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Resolutions</h1>
                        <p class="mt-1 text-sm text-gray-600">{{ venture.title }}</p>
                    </div>
                    <button @click="showForm = !showForm" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500">
                        <PlusIcon class="inline h-4 w-4" /> New Resolution
                    </button>
                </div>

                <div v-if="showForm" class="mb-6 rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">Create Resolution</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="form.description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="ordinary">Ordinary</option>
                                    <option value="special">Special</option>
                                    <option value="board">Board</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Voting Ends</label>
                                <input v-model="form.voting_ends_at" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pass Threshold (%)</label>
                                <input v-model="form.pass_threshold_percentage" type="number" min="1" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                            </div>
                        </div>
                        <button @click="createResolution" :disabled="submittingForm" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 disabled:opacity-50">
                            {{ submittingForm ? 'Creating...' : 'Create Resolution' }}
                        </button>
                    </div>
                </div>

                <div v-if="!resolutions.length" class="rounded-lg bg-white p-12 text-center shadow">
                    <p class="text-sm text-gray-500">No resolutions yet for this venture.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="resolution in resolutions" :key="resolution.id" class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span :class="getStatusColor(resolution.status)" class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ resolution.status }}</span>
                                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium capitalize text-blue-800">{{ resolution.type }}</span>
                                </div>
                                <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ resolution.title }}</h3>
                                <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ resolution.description }}</p>
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                    <span>By {{ resolution.creator.name }}</span>
                                    <span>{{ new Date(resolution.created_at).toLocaleDateString() }}</span>
                                    <span v-if="resolution.voting_ends_at">Ends {{ new Date(resolution.voting_ends_at).toLocaleDateString() }}</span>
                                    <span>Threshold: {{ resolution.pass_threshold_percentage }}%</span>
                                </div>
                                <div v-if="resolution.status === 'voting' || resolution.status === 'passed' || resolution.status === 'rejected'" class="mt-3 flex items-center gap-4 text-sm">
                                    <span class="text-green-600">For: {{ resolution.votes_for.toFixed(1) }}%</span>
                                    <span class="text-red-600">Against: {{ resolution.votes_against.toFixed(1) }}%</span>
                                    <span class="text-gray-600">Abstain: {{ resolution.votes_abstain.toFixed(1) }}%</span>
                                </div>
                                <div v-if="resolution.result_notes" class="mt-2 rounded bg-gray-50 p-2 text-xs text-gray-600">{{ resolution.result_notes }}</div>
                            </div>
                            <div class="ml-4 flex gap-2">
                                <button v-if="resolution.status === 'draft'" @click="openVoting(resolution.id)" class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-green-500">
                                    <PlayIcon class="inline h-4 w-4" /> Open Voting
                                </button>
                                <button v-if="resolution.status === 'voting'" @click="tallyResults(resolution.id)" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500">
                                    <CheckCircleIcon class="inline h-4 w-4" /> Tally
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
