<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { ArrowLeftIcon, FolderIcon, UserIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

interface Props {
    employee: any;
    application: {
        id: number;
        user: { id: number; name: string; email: string; phone: string } | null;
        reference_number: string;
        business_name: string;
        business_description: string;
        business_type: string;
        order_type: string;
        amount_requested: string;
        member_contribution: string;
        total_project_cost: string;
        expected_profit: string;
        completion_period_days: number;
        status: string;
        score: number | null;
        evaluator_notes: string | null;
        documents: any[];
        created_at: string;
    };
}

const props = defineProps<Props>();
const showReviewModal = ref(false);

const form = useForm({
    score: props.application.score || 0,
    notes: '',
    recommendation: 'approve' as 'approve' | 'reject' | 'needs_info',
});

const submitReview = () => {
    form.post(route('employee.portal.delegated.bgf.review', props.application.id), {
        onSuccess: () => { showReviewModal.value = false; },
    });
};

const getStatusClass = (s: string) => ({
    'draft': 'bg-gray-100 text-gray-700',
    'submitted': 'bg-amber-100 text-amber-700',
    'under_review': 'bg-blue-100 text-blue-700',
    'approved': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
    'funded': 'bg-purple-100 text-purple-700',
}[s] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head :title="`BGF - ${application.reference_number}`" />
    <EmployeePortalLayout>
        <template #header>BGF Application</template>
        
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('employee.portal.delegated.bgf.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ application.reference_number }}</h1>
                        <p class="text-gray-500">{{ application.business_name }}</p>
                    </div>
                    <span :class="['px-3 py-1 text-sm font-medium rounded-full capitalize', getStatusClass(application.status)]">
                        {{ application.status.replace('_', ' ') }}
                    </span>
                </div>
                <button v-if="application.status === 'submitted'" @click="showReviewModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Review Application
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Application Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <FolderIcon class="h-5 w-5 text-gray-400" />Business Information
                        </h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <dt class="text-sm text-gray-500">Business Name</dt>
                                <dd class="font-medium text-gray-900">{{ application.business_name }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-sm text-gray-500">Description</dt>
                                <dd class="text-gray-700">{{ application.business_description }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Business Type</dt>
                                <dd class="font-medium text-gray-900 capitalize">{{ application.business_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Order Type</dt>
                                <dd class="font-medium text-gray-900 capitalize">{{ application.order_type }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h2 class="font-semibold text-gray-900 mb-4">Financial Details</h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Amount Requested</dt>
                                <dd class="text-xl font-bold text-blue-600">K{{ Number(application.amount_requested).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Member Contribution</dt>
                                <dd class="text-xl font-bold text-gray-900">K{{ Number(application.member_contribution).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Total Project Cost</dt>
                                <dd class="font-medium text-gray-900">K{{ Number(application.total_project_cost).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Expected Profit</dt>
                                <dd class="font-medium text-green-600">K{{ Number(application.expected_profit).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Completion Period</dt>
                                <dd class="font-medium text-gray-900">{{ application.completion_period_days }} days</dd>
                            </div>
                            <div v-if="application.score">
                                <dt class="text-sm text-gray-500">Score</dt>
                                <dd :class="['text-xl font-bold', application.score >= 70 ? 'text-green-600' : application.score >= 50 ? 'text-amber-600' : 'text-red-600']">
                                    {{ application.score }}/100
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="application.evaluator_notes" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h2 class="font-semibold text-gray-900 mb-4">Evaluator Notes</h2>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ application.evaluator_notes }}</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <UserIcon class="h-5 w-5 text-gray-400" />Applicant
                        </h3>
                        <div v-if="application.user" class="space-y-3">
                            <p class="font-medium text-gray-900">{{ application.user.name }}</p>
                            <p class="text-sm text-gray-500">{{ application.user.email }}</p>
                            <p v-if="application.user.phone" class="text-sm text-gray-500">{{ application.user.phone }}</p>
                        </div>
                    </div>

                    <div v-if="application.documents?.length" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <DocumentTextIcon class="h-5 w-5 text-gray-400" />Documents
                        </h3>
                        <ul class="space-y-2">
                            <li v-for="(doc, i) in application.documents" :key="i" class="text-sm text-blue-600 hover:underline">
                                {{ doc.name || `Document ${i + 1}` }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <Teleport to="body">
            <div v-if="showReviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showReviewModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Review Application</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Score (0-100)</label>
                            <input v-model.number="form.score" type="number" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recommendation</label>
                            <select v-model="form.recommendation" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="approve">Recommend Approval</option>
                                <option value="reject">Recommend Rejection</option>
                                <option value="needs_info">Needs More Information</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Your assessment and notes..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showReviewModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="submitReview" :disabled="form.processing || !form.notes" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            Submit Review
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </EmployeePortalLayout>
</template>
