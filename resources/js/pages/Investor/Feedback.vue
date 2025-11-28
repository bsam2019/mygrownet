<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import {
    ChatBubbleBottomCenterTextIcon,
    PlusIcon,
    StarIcon,
    CheckCircleIcon,
    ClockIcon,
    XMarkIcon,
    SparklesIcon,
    LightBulbIcon,
    ExclamationCircleIcon,
    HandThumbUpIcon,
    QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid';

interface Feedback {
    id: number;
    feedback_type: string;
    subject: string;
    message: string;
    rating: number | null;
    status: string;
    admin_response: string | null;
    responded_at: string | null;
    created_at: string;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    myFeedback: Feedback[];
    feedbackTypes: Record<string, string>;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

const showFeedbackModal = ref(false);
const hoverRating = ref(0);

const feedbackForm = useForm({
    feedback_type: 'suggestion',
    subject: '',
    message: '',
    rating: null as number | null,
});

const submitFeedback = () => {
    feedbackForm.post(route('investor.feedback.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showFeedbackModal.value = false;
            feedbackForm.reset();
        },
    });
};

const setRating = (rating: number) => {
    feedbackForm.rating = feedbackForm.rating === rating ? null : rating;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        new: 'bg-blue-100 text-blue-800',
        reviewed: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-purple-100 text-purple-800',
        resolved: 'bg-green-100 text-green-800',
        closed: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getTypeIcon = (type: string) => {
    return ChatBubbleBottomCenterTextIcon;
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Feedback" 
        :active-page="activePage || 'feedback'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Feedback" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Feedback</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Share your thoughts and help us improve
                    </p>
                </div>
                <button
                    @click="showFeedbackModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    Submit Feedback
                </button>
            </div>

            <!-- Feedback List -->
            <div class="space-y-4">
                <div
                    v-for="feedback in myFeedback"
                    :key="feedback.id"
                    class="bg-white rounded-lg shadow p-6"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                                {{ feedbackTypes[feedback.feedback_type] || feedback.feedback_type }}
                            </span>
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(feedback.status)]">
                                {{ feedback.status.replace('_', ' ') }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-500">{{ formatDate(feedback.created_at) }}</span>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900">{{ feedback.subject }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ feedback.message }}</p>

                    <!-- Rating -->
                    <div v-if="feedback.rating" class="mt-3 flex items-center gap-1">
                        <component
                            v-for="i in 5"
                            :key="i"
                            :is="i <= feedback.rating ? StarSolidIcon : StarIcon"
                            :class="['h-4 w-4', i <= feedback.rating ? 'text-yellow-400' : 'text-gray-300']"
                            aria-hidden="true"
                        />
                    </div>

                    <!-- Admin Response -->
                    <div v-if="feedback.admin_response" class="mt-4 bg-green-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <CheckCircleIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            <span class="text-sm font-medium text-green-800">Response from Team</span>
                        </div>
                        <p class="text-sm text-gray-700">{{ feedback.admin_response }}</p>
                        <p v-if="feedback.responded_at" class="mt-2 text-xs text-gray-500">
                            Responded on {{ formatDate(feedback.responded_at) }}
                        </p>
                    </div>

                    <!-- Pending Response -->
                    <div v-else-if="feedback.status !== 'closed'" class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                        <ClockIcon class="h-4 w-4" aria-hidden="true" />
                        <span>Awaiting response</span>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="myFeedback.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                    <ChatBubbleBottomCenterTextIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No feedback submitted</h3>
                    <p class="mt-1 text-sm text-gray-500">Your feedback helps us improve the platform.</p>
                    <button
                        @click="showFeedbackModal = true"
                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                    >
                        Submit Your First Feedback
                    </button>
                </div>
            </div>
        </div>

        <!-- Feedback Modal -->
        <div v-if="showFeedbackModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <!-- Backdrop -->
                <div 
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                    @click="showFeedbackModal = false"
                ></div>
                
                <!-- Modal Content -->
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto transform transition-all">
                    <!-- Header with gradient -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-lg">
                                    <ChatBubbleBottomCenterTextIcon class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">Submit Feedback</h3>
                                    <p class="text-sm text-blue-100">We value your input</p>
                                </div>
                            </div>
                            <button
                                @click="showFeedbackModal = false"
                                class="p-2 hover:bg-white/20 rounded-full transition-colors"
                                aria-label="Close feedback modal"
                            >
                                <XMarkIcon class="h-5 w-5 text-white" aria-hidden="true" />
                            </button>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form @submit.prevent="submitFeedback" class="p-6 space-y-5">
                        <!-- Feedback Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Feedback Type
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="(label, key) in feedbackTypes"
                                    :key="key"
                                    type="button"
                                    @click="feedbackForm.feedback_type = key"
                                    :class="[
                                        'flex items-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all',
                                        feedbackForm.feedback_type === key
                                            ? 'border-blue-500 bg-blue-50 text-blue-700'
                                            : 'border-gray-200 hover:border-gray-300 text-gray-600 hover:bg-gray-50'
                                    ]"
                                >
                                    <component
                                        :is="key === 'suggestion' ? LightBulbIcon : key === 'bug' ? ExclamationCircleIcon : key === 'praise' ? HandThumbUpIcon : QuestionMarkCircleIcon"
                                        class="h-5 w-5"
                                        aria-hidden="true"
                                    />
                                    {{ label }}
                                </button>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="feedback-subject" class="block text-sm font-medium text-gray-700 mb-2">
                                Subject
                            </label>
                            <input
                                id="feedback-subject"
                                v-model="feedbackForm.subject"
                                type="text"
                                class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-colors placeholder:text-gray-400"
                                placeholder="What's this about?"
                            />
                            <p v-if="feedbackForm.errors.subject" class="mt-1.5 text-sm text-red-600">
                                {{ feedbackForm.errors.subject }}
                            </p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="feedback-message" class="block text-sm font-medium text-gray-700 mb-2">
                                Your Message
                            </label>
                            <textarea
                                id="feedback-message"
                                v-model="feedbackForm.message"
                                rows="4"
                                class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-colors placeholder:text-gray-400 resize-none"
                                placeholder="Tell us more about your feedback..."
                            ></textarea>
                            <p v-if="feedbackForm.errors.message" class="mt-1.5 text-sm text-red-600">
                                {{ feedbackForm.errors.message }}
                            </p>
                        </div>

                        <!-- Rating -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                How would you rate your experience? 
                                <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    v-for="i in 5"
                                    :key="i"
                                    type="button"
                                    @click="setRating(i)"
                                    @mouseenter="hoverRating = i"
                                    @mouseleave="hoverRating = 0"
                                    class="p-1.5 rounded-lg hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition-all"
                                    :aria-label="`Rate ${i} stars`"
                                >
                                    <component
                                        :is="(hoverRating || feedbackForm.rating || 0) >= i ? StarSolidIcon : StarIcon"
                                        :class="[
                                            'h-8 w-8 transition-all transform',
                                            (hoverRating || feedbackForm.rating || 0) >= i 
                                                ? 'text-yellow-400 scale-110' 
                                                : 'text-gray-300 hover:text-yellow-200',
                                        ]"
                                        aria-hidden="true"
                                    />
                                </button>
                            </div>
                            <p v-if="feedbackForm.rating" class="text-center text-sm text-gray-500 mt-2">
                                {{ feedbackForm.rating === 5 ? 'Excellent!' : feedbackForm.rating === 4 ? 'Great!' : feedbackForm.rating === 3 ? 'Good' : feedbackForm.rating === 2 ? 'Fair' : 'Poor' }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="showFeedbackModal = false"
                                class="flex-1 px-5 py-3 border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="feedbackForm.processing || !feedbackForm.subject || !feedbackForm.message"
                                class="flex-1 px-5 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-blue-500/25 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2"
                            >
                                <SparklesIcon v-if="!feedbackForm.processing" class="h-5 w-5" aria-hidden="true" />
                                <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ feedbackForm.processing ? 'Submitting...' : 'Submit Feedback' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
