<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    UserCircleIcon,
    StarIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

interface Comment {
    id: number;
    author_type: string;
    author_name: string;
    comment: string;
    created_at: string;
}

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    description: string;
    category: string;
    status: string;
    priority: string;
    created_at: string;
    updated_at: string;
    satisfaction_rating: number | null;
    rating_feedback: string | null;
}

const props = defineProps<{
    ticket: Ticket;
    comments: Comment[];
}>();

const form = useForm({
    comment: '',
});

const ratingForm = useForm({
    rating: 0,
    feedback: '',
});

const showRatingModal = ref(false);
const hoverRating = ref(0);

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-700',
        in_progress: 'bg-amber-100 text-amber-700',
        waiting: 'bg-purple-100 text-purple-700',
        resolved: 'bg-emerald-100 text-emerald-700',
        closed: 'bg-gray-100 text-gray-700',
    };
    return colors[status] || colors.open;
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const goBack = () => {
    router.get(route('growfinance.support.index'));
};

const addComment = () => {
    form.post(route('growfinance.support.comment', props.ticket.id), {
        onSuccess: () => {
            form.reset();
        },
        preserveScroll: true,
    });
};

const canRate = () => {
    return ['resolved', 'closed'].includes(props.ticket.status) && !props.ticket.satisfaction_rating;
};

const submitRating = () => {
    ratingForm.post(route('growfinance.support.rate', props.ticket.id), {
        onSuccess: () => {
            showRatingModal.value = false;
        },
    });
};
</script>

<template>
    <GrowFinanceLayout>
        <Head :title="`${ticket.ticket_number} - GrowFinance`" />

        <div class="p-4 lg:p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <button
                    @click="goBack"
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    aria-label="Go back to tickets"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900">{{ ticket.subject }}</h1>
                        <span :class="['px-2 py-0.5 rounded text-xs font-medium', getStatusColor(ticket.status)]">
                            {{ ticket.status.replace('_', ' ') }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ ticket.ticket_number }}</p>
                </div>
                <button
                    v-if="canRate()"
                    @click="showRatingModal = true"
                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                >
                    <StarIcon class="h-4 w-4" aria-hidden="true" />
                    Rate Support
                </button>
            </div>

            <!-- Ticket Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                    <span class="px-2 py-0.5 rounded bg-gray-100">{{ ticket.category }}</span>
                    <span>•</span>
                    <span>{{ ticket.priority }} priority</span>
                    <span>•</span>
                    <span>Created {{ formatDateTime(ticket.created_at) }}</span>
                </div>
                <p class="text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
            </div>

            <!-- Rating Display -->
            <div v-if="ticket.satisfaction_rating" class="bg-amber-50 rounded-xl border border-amber-200 p-4 mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-medium text-amber-800">Your Rating:</span>
                    <div class="flex">
                        <StarIconSolid
                            v-for="i in 5"
                            :key="i"
                            :class="[
                                'h-5 w-5',
                                i <= ticket.satisfaction_rating ? 'text-amber-500' : 'text-gray-300'
                            ]"
                            aria-hidden="true"
                        />
                    </div>
                </div>
                <p v-if="ticket.rating_feedback" class="text-sm text-amber-700">{{ ticket.rating_feedback }}</p>
            </div>

            <!-- Comments -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-medium text-gray-900">Conversation</h2>
                </div>
                
                <div v-if="comments.length === 0" class="p-8 text-center text-gray-500">
                    No replies yet
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="comment in comments"
                        :key="comment.id"
                        :class="[
                            'p-4',
                            comment.author_type === 'member' ? 'bg-emerald-50/50' : 'bg-white'
                        ]"
                    >
                        <div class="flex items-start gap-3">
                            <UserCircleIcon class="h-8 w-8 text-gray-400 flex-shrink-0" aria-hidden="true" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ comment.author_name }}
                                        <span v-if="comment.author_type !== 'member'" class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 rounded">
                                            Support
                                        </span>
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ formatDateTime(comment.created_at) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ comment.comment }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Form -->
            <div v-if="!['closed', 'resolved'].includes(ticket.status)" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <form @submit.prevent="addComment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Add Reply</label>
                        <textarea
                            v-model="form.comment"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Type your reply..."
                        />
                        <p v-if="form.errors.comment" class="text-sm text-red-600 mt-1">{{ form.errors.comment }}</p>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing || !form.comment.trim()"
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                        >
                            <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rating Modal -->
        <Teleport to="body">
            <div v-if="showRatingModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showRatingModal = false" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Rate Your Support Experience</h2>
                        
                        <form @submit.prevent="submitRating" class="space-y-4">
                            <div class="flex justify-center gap-2">
                                <button
                                    v-for="i in 5"
                                    :key="i"
                                    type="button"
                                    @click="ratingForm.rating = i"
                                    @mouseenter="hoverRating = i"
                                    @mouseleave="hoverRating = 0"
                                    class="p-1 transition-transform hover:scale-110"
                                >
                                    <component
                                        :is="(hoverRating || ratingForm.rating) >= i ? StarIconSolid : StarIcon"
                                        :class="[
                                            'h-8 w-8',
                                            (hoverRating || ratingForm.rating) >= i ? 'text-amber-500' : 'text-gray-300'
                                        ]"
                                        aria-hidden="true"
                                    />
                                </button>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Feedback (optional)</label>
                                <textarea
                                    v-model="ratingForm.feedback"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Tell us about your experience..."
                                />
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="showRatingModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="ratingForm.processing || !ratingForm.rating"
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                                >
                                    Submit Rating
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </GrowFinanceLayout>
</template>
