<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import {
    QuestionMarkCircleIcon,
    ChatBubbleLeftRightIcon,
    PlusIcon,
    HandThumbUpIcon,
    CheckCircleIcon,
    ClockIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Question {
    id: number;
    category: string;
    category_label: string;
    subject: string;
    question: string;
    status: string;
    is_public: boolean;
    upvotes: number;
    created_at: string;
    latest_answer?: {
        answer: string;
        answered_at: string;
        answered_by?: { name: string };
    };
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

interface Props {
    investor: Investor;
    myQuestions: {
        data: Question[];
        links: any;
    };
    publicQuestions: {
        data: Question[];
        links: any;
    };
    categories: Record<string, number>;
    selectedCategory: string | null;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}

const props = defineProps<Props>();

const showNewQuestionModal = ref(false);
const activeTab = ref<'my' | 'faq'>('my');

const form = useForm({
    category: 'financial',
    subject: '',
    question: '',
});

const submitQuestion = () => {
    form.post(route('investor.qa.store'), {
        onSuccess: () => {
            showNewQuestionModal.value = false;
            form.reset();
        },
    });
};

const upvoteQuestion = async (questionId: number) => {
    try {
        await fetch(route('investor.qa.upvote', questionId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
    } catch (e) {
        console.error('Failed to upvote');
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'answered':
            return { class: 'bg-green-100 text-green-800', icon: CheckCircleIcon, text: 'Answered' };
        case 'pending':
            return { class: 'bg-yellow-100 text-yellow-800', icon: ClockIcon, text: 'Pending' };
        default:
            return { class: 'bg-gray-100 text-gray-800', icon: QuestionMarkCircleIcon, text: status };
    }
};

const categoryOptions = [
    { value: 'financial', label: 'Financial' },
    { value: 'operations', label: 'Operations' },
    { value: 'strategy', label: 'Strategy' },
    { value: 'governance', label: 'Governance' },
    { value: 'other', label: 'Other' },
];
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Q&A" 
        :active-page="activePage || 'questions'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Q&A - Investor Portal" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Questions & Answers</h1>
                    <p class="mt-1 text-gray-600">
                        Ask questions and browse frequently asked questions
                    </p>
                </div>
                <button
                    @click="showNewQuestionModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Ask a Question
                </button>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'my'"
                        :class="[
                            activeTab === 'my'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        My Questions
                    </button>
                    <button
                        @click="activeTab = 'faq'"
                        :class="[
                            activeTab === 'faq'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        Public FAQ
                    </button>
                </nav>
            </div>

            <!-- My Questions Tab -->
            <div v-if="activeTab === 'my'" class="space-y-4">
                <div v-if="myQuestions.data.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <ChatBubbleLeftRightIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" aria-hidden="true" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No questions yet</h3>
                    <p class="text-gray-500 mb-4">Ask your first question to get started</p>
                    <button
                        @click="showNewQuestionModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Ask a Question
                    </button>
                </div>

                <div
                    v-for="question in myQuestions.data"
                    :key="question.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                                {{ question.category_label }}
                            </span>
                            <span
                                :class="getStatusBadge(question.status).class"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium"
                            >
                                <component :is="getStatusBadge(question.status).icon" class="h-3 w-3" aria-hidden="true" />
                                {{ getStatusBadge(question.status).text }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">{{ question.created_at }}</span>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ question.subject }}</h3>
                    <p class="text-gray-600 mb-4">{{ question.question }}</p>

                    <div v-if="question.latest_answer" class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                        <p class="text-sm font-medium text-green-800 mb-2">Answer:</p>
                        <p class="text-sm text-green-700">{{ question.latest_answer.answer }}</p>
                        <p class="text-xs text-green-600 mt-2">
                            Answered {{ question.latest_answer.answered_at }}
                            <span v-if="question.latest_answer.answered_by">
                                by {{ question.latest_answer.answered_by.name }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Public FAQ Tab -->
            <div v-if="activeTab === 'faq'" class="space-y-4">
                <!-- Category Filter -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <a
                        :href="route('investor.qa')"
                        :class="[
                            !selectedCategory ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                            'px-3 py-1.5 rounded-full text-sm font-medium transition-colors'
                        ]"
                    >
                        All
                    </a>
                    <a
                        v-for="(count, category) in categories"
                        :key="category"
                        :href="route('investor.qa', { category })"
                        :class="[
                            selectedCategory === category ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                            'px-3 py-1.5 rounded-full text-sm font-medium transition-colors'
                        ]"
                    >
                        {{ category }} ({{ count }})
                    </a>
                </div>

                <div v-if="publicQuestions.data.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <QuestionMarkCircleIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" aria-hidden="true" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No public questions yet</h3>
                    <p class="text-gray-500">Check back later for frequently asked questions</p>
                </div>

                <div
                    v-for="question in publicQuestions.data"
                    :key="question.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                >
                    <div class="flex items-start justify-between mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ question.category_label }}
                        </span>
                        <button
                            @click="upvoteQuestion(question.id)"
                            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-blue-600"
                            aria-label="Upvote this question"
                        >
                            <HandThumbUpIcon class="h-4 w-4" aria-hidden="true" />
                            {{ question.upvotes }}
                        </button>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ question.subject }}</h3>
                    <p class="text-gray-600 mb-4">{{ question.question }}</p>

                    <div v-if="question.latest_answer" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">{{ question.latest_answer.answer }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Question Modal -->
        <div v-if="showNewQuestionModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showNewQuestionModal = false"></div>
                
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Ask a Question</h2>
                        <button
                            @click="showNewQuestionModal = false"
                            class="p-2 hover:bg-gray-100 rounded-full"
                            aria-label="Close modal"
                        >
                            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>

                    <form @submit.prevent="submitQuestion" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select
                                v-model="form.category"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option v-for="opt in categoryOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input
                                v-model="form.subject"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Brief summary of your question"
                            />
                            <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Your Question</label>
                            <textarea
                                v-model="form.question"
                                rows="4"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe your question in detail..."
                            ></textarea>
                            <p v-if="form.errors.question" class="mt-1 text-sm text-red-600">{{ form.errors.question }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                type="button"
                                @click="showNewQuestionModal = false"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                Submit Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
