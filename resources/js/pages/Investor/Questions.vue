<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import {
    ChatBubbleLeftRightIcon,
    HandThumbUpIcon,
    PlusIcon,
    TagIcon,
    CheckBadgeIcon,
    UserCircleIcon,
} from '@heroicons/vue/24/outline';
import { HandThumbUpIcon as HandThumbUpSolidIcon } from '@heroicons/vue/24/solid';

interface Question {
    id: number;
    subject: string;
    question: string;
    category: string;
    status: string;
    is_anonymous: boolean;
    is_featured: boolean;
    upvotes: number;
    has_upvoted: boolean;
    author_name: string;
    created_at: string;
    latest_answer?: {
        answer: string;
        answered_by: string;
        answered_by_title: string;
        answered_at: string;
    };
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    publishedQuestions: Question[];
    featuredQuestions: Question[];
    myQuestions: Question[];
    categories: Record<string, string>;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

const activeTab = ref<'all' | 'featured' | 'mine'>('all');
const showAskModal = ref(false);
const selectedCategory = ref<string>('');

const askForm = useForm({
    subject: '',
    question: '',
    category: 'general',
    is_anonymous: false,
});

const submitQuestion = () => {
    askForm.post(route('investor.questions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAskModal.value = false;
            askForm.reset();
        },
    });
};

const toggleUpvote = async (questionId: number, hasUpvoted: boolean) => {
    if (hasUpvoted) {
        router.delete(route('investor.questions.upvote.remove', questionId), {
            preserveScroll: true,
        });
    } else {
        router.post(route('investor.questions.upvote', questionId), {}, {
            preserveScroll: true,
        });
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
        financial: 'bg-green-100 text-green-800',
        operations: 'bg-blue-100 text-blue-800',
        strategy: 'bg-purple-100 text-purple-800',
        dividends: 'bg-yellow-100 text-yellow-800',
        governance: 'bg-indigo-100 text-indigo-800',
        legal: 'bg-red-100 text-red-800',
        general: 'bg-gray-100 text-gray-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        answered: 'bg-green-100 text-green-800',
        published: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const filteredQuestions = () => {
    let questions = props.publishedQuestions;
    if (selectedCategory.value) {
        questions = questions.filter(q => q.category === selectedCategory.value);
    }
    return questions;
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Q&A" 
        :active-page="activePage || 'questions'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Q&A Portal" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Q&A Portal</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Ask questions and get answers from management
                    </p>
                </div>
                <button
                    @click="showAskModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    Ask a Question
                </button>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in [
                            { id: 'all', label: 'All Questions' },
                            { id: 'featured', label: 'Featured' },
                            { id: 'mine', label: 'My Questions' },
                        ]"
                        :key="tab.id"
                        @click="activeTab = tab.id as any"
                        :class="[
                            activeTab === tab.id
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'py-4 px-1 border-b-2 font-medium text-sm',
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Category Filter -->
            <div v-if="activeTab === 'all'" class="mb-6 flex flex-wrap gap-2">
                <button
                    @click="selectedCategory = ''"
                    :class="[
                        'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                        !selectedCategory ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    All
                </button>
                <button
                    v-for="(label, key) in categories"
                    :key="key"
                    @click="selectedCategory = key"
                    :class="[
                        'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                        selectedCategory === key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    {{ label }}
                </button>
            </div>

            <!-- Questions List -->
            <div class="space-y-4">
                <!-- All Questions -->
                <template v-if="activeTab === 'all'">
                    <div
                        v-for="question in filteredQuestions()"
                        :key="question.id"
                        class="bg-white rounded-lg shadow p-6"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Upvote -->
                            <div class="flex flex-col items-center">
                                <button
                                    @click="toggleUpvote(question.id, question.has_upvoted)"
                                    :class="[
                                        'p-2 rounded-full transition-colors',
                                        question.has_upvoted
                                            ? 'text-blue-600 bg-blue-50'
                                            : 'text-gray-400 hover:text-blue-600 hover:bg-blue-50',
                                    ]"
                                    :aria-label="question.has_upvoted ? 'Remove upvote' : 'Upvote question'"
                                >
                                    <component
                                        :is="question.has_upvoted ? HandThumbUpSolidIcon : HandThumbUpIcon"
                                        class="h-5 w-5"
                                        aria-hidden="true"
                                    />
                                </button>
                                <span class="text-sm font-medium text-gray-700">{{ question.upvotes }}</span>
                            </div>

                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getCategoryColor(question.category)]">
                                        {{ categories[question.category] }}
                                    </span>
                                    <span v-if="question.is_featured" class="inline-flex items-center text-yellow-600">
                                        <CheckBadgeIcon class="h-4 w-4" aria-hidden="true" />
                                    </span>
                                </div>

                                <h3 class="text-lg font-medium text-gray-900">{{ question.subject }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ question.question }}</p>

                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <UserCircleIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ question.author_name }}
                                    </span>
                                    <span>{{ formatDate(question.created_at) }}</span>
                                </div>

                                <!-- Answer -->
                                <div v-if="question.latest_answer" class="mt-4 bg-blue-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <CheckBadgeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                        <span class="text-sm font-medium text-blue-900">Official Answer</span>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ question.latest_answer.answer }}</p>
                                    <p class="mt-2 text-xs text-gray-500">
                                        — {{ question.latest_answer.answered_by }}
                                        <span v-if="question.latest_answer.answered_by_title">, {{ question.latest_answer.answered_by_title }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredQuestions().length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                        <ChatBubbleLeftRightIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No questions yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Be the first to ask a question!</p>
                    </div>
                </template>

                <!-- Featured Questions -->
                <template v-if="activeTab === 'featured'">
                    <div
                        v-for="question in featuredQuestions"
                        :key="question.id"
                        class="bg-white rounded-lg shadow border-l-4 border-yellow-400 p-6"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <CheckBadgeIcon class="h-5 w-5 text-yellow-500" aria-hidden="true" />
                            <span class="text-sm font-medium text-yellow-700">Featured Question</span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">{{ question.subject }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ question.question }}</p>

                        <div v-if="question.latest_answer" class="mt-4 bg-blue-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700">{{ question.latest_answer.answer }}</p>
                            <p class="mt-2 text-xs text-gray-500">
                                — {{ question.latest_answer.answered_by }}
                            </p>
                        </div>
                    </div>

                    <div v-if="featuredQuestions.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                        <CheckBadgeIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No featured questions</h3>
                        <p class="mt-1 text-sm text-gray-500">Featured Q&As will appear here.</p>
                    </div>
                </template>

                <!-- My Questions -->
                <template v-if="activeTab === 'mine'">
                    <div
                        v-for="question in myQuestions"
                        :key="question.id"
                        class="bg-white rounded-lg shadow p-6"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getStatusColor(question.status)]">
                                {{ question.status }}
                            </span>
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getCategoryColor(question.category)]">
                                {{ categories[question.category] }}
                            </span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">{{ question.subject }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ question.question }}</p>
                        <p class="mt-2 text-xs text-gray-500">Asked on {{ formatDate(question.created_at) }}</p>

                        <div v-if="question.latest_answer" class="mt-4 bg-green-50 rounded-lg p-4">
                            <p class="text-sm font-medium text-green-800 mb-2">Answer:</p>
                            <p class="text-sm text-gray-700">{{ question.latest_answer.answer }}</p>
                        </div>
                    </div>

                    <div v-if="myQuestions.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                        <ChatBubbleLeftRightIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No questions yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Questions you ask will appear here.</p>
                        <button
                            @click="showAskModal = true"
                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                        >
                            Ask Your First Question
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Ask Question Modal -->
        <div v-if="showAskModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showAskModal = false"></div>
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ask a Question</h3>

                    <form @submit.prevent="submitQuestion" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select
                                v-model="askForm.category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option v-for="(label, key) in categories" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subject</label>
                            <input
                                v-model="askForm.subject"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Brief summary of your question"
                            />
                            <p v-if="askForm.errors.subject" class="mt-1 text-sm text-red-600">{{ askForm.errors.subject }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Your Question</label>
                            <textarea
                                v-model="askForm.question"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Provide details about your question..."
                            ></textarea>
                            <p v-if="askForm.errors.question" class="mt-1 text-sm text-red-600">{{ askForm.errors.question }}</p>
                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="askForm.is_anonymous"
                                type="checkbox"
                                id="is_anonymous"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="is_anonymous" class="ml-2 block text-sm text-gray-700">
                                Submit anonymously
                            </label>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="showAskModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="askForm.processing"
                                class="flex-1 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ askForm.processing ? 'Submitting...' : 'Submit Question' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
