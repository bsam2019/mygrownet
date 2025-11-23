<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-[200001]">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/50" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-0 relative z-10">
                    <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[85vh] flex flex-col">
                        <!-- Header -->
                        <div class="sticky top-0 text-white px-6 py-4 rounded-t-3xl flex-shrink-0 bg-gradient-to-r from-blue-600 to-indigo-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold">Ticket #{{ ticket.id }}</h3>
                                    <p class="text-xs text-white/80">{{ getCategoryLabel(ticket.category) }}</p>
                                </div>
                                <button @click="closeModal" aria-label="Close ticket detail modal" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-4 overflow-y-auto flex-1">
                            <!-- Status & Priority -->
                            <div class="flex gap-2">
                                <span :class="['text-xs font-medium px-2 py-1 rounded-full', getStatusColor(ticket.status)]">
                                    {{ getStatusLabel(ticket.status) }}
                                </span>
                                <span :class="['text-xs font-medium px-2 py-1 rounded-full', getPriorityColor(ticket.priority)]">
                                    {{ getPriorityLabel(ticket.priority) }}
                                </span>
                            </div>

                            <!-- Subject & Description -->
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">{{ ticket.subject }}</h4>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
                            </div>

                            <!-- Comments Section -->
                            <div class="border-t border-gray-200 pt-4">
                                <h4 class="font-semibold text-gray-900 mb-3">Comments</h4>
                                <div v-if="comments.length === 0" class="text-center py-4 text-gray-500 text-sm">
                                    No comments yet
                                </div>
                                <div v-else class="space-y-3">
                                    <div v-for="comment in comments" :key="comment.id" :class="['rounded-lg p-3', comment.userId === userId ? 'bg-blue-50' : 'bg-gray-50']">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-medium text-gray-900">{{ comment.userId === userId ? 'You' : 'Support Team' }}</span>
                                            <span class="text-xs text-gray-500">{{ formatTime(comment.createdAt) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ comment.comment }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Comment Form -->
                            <div v-if="!ticket.status.includes('closed')" class="border-t border-gray-200 pt-4">
                                <textarea
                                    v-model="newComment"
                                    rows="3"
                                    placeholder="Add a comment..."
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                />
                                <button
                                    @click="submitComment"
                                    :disabled="!newComment.trim() || submitting"
                                    class="mt-2 w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 active:scale-95 transition-all"
                                >
                                    {{ submitting ? 'Sending...' : 'Send Comment' }}
                                </button>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-gray-200 px-6 py-4">
                            <button @click="closeModal" class="w-full px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <Toast :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { router, usePage } from '@inertiajs/vue3';
import Toast from './Toast.vue';

interface Ticket {
    id: number;
    userId: number;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    createdAt: string;
}

interface Comment {
    id: number;
    userId: number;
    comment: string;
    createdAt: string;
}

interface Props {
    isOpen: boolean;
    ticket: Ticket;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'updated'): void;
}>();

const page = usePage();
const userId = (page.props.auth as any)?.user?.id || 0;
const comments = ref<Comment[]>([]);
const newComment = ref('');
const submitting = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error' | 'warning' | 'info'>('success');

const showToastMessage = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

onMounted(() => {
    fetchComments();
});

async function fetchComments() {
    try {
        const response = await fetch(`/api/support/tickets/${props.ticket.id}/comments`);
        if (response.ok) {
            comments.value = await response.json();
        }
    } catch (error) {
        console.error('Failed to fetch comments:', error);
    }
}

function submitComment() {
    if (!newComment.value.trim()) return;

    submitting.value = true;
    router.post(
        `/mygrownet/support/${props.ticket.id}/comment`,
        { comment: newComment.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                newComment.value = '';
                showToastMessage('Comment added successfully', 'success');
                fetchComments();
                emit('updated');
            },
            onError: () => {
                showToastMessage('Failed to add comment', 'error');
            },
            onFinish: () => {
                submitting.value = false;
            },
        }
    );
}

function closeModal() {
    emit('close');
}

const getStatusColor = (s: string) => ({
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-amber-100 text-amber-800',
    waiting: 'bg-purple-100 text-purple-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
}[s] || 'bg-blue-100 text-blue-800');

const getStatusLabel = (s: string) => ({
    open: 'Open',
    in_progress: 'In Progress',
    waiting: 'Waiting',
    resolved: 'Resolved',
    closed: 'Closed',
}[s] || s);

const getPriorityColor = (p: string) => ({
    low: 'bg-gray-100 text-gray-600',
    medium: 'bg-blue-100 text-blue-600',
    high: 'bg-amber-100 text-amber-600',
    urgent: 'bg-red-100 text-red-600',
}[p] || 'bg-blue-100 text-blue-600');

const getPriorityLabel = (p: string) => p.charAt(0).toUpperCase() + p.slice(1);

const getCategoryLabel = (c: string) => ({
    technical: 'Technical Support',
    financial: 'Financial Issue',
    account: 'Account Management',
    general: 'General Inquiry',
}[c] || c);

function formatTime(d: string) {
    const date = new Date(d), now = new Date(), diff = now.getTime() - date.getTime();
    const mins = Math.floor(diff / 60000), hours = Math.floor(diff / 3600000), days = Math.floor(diff / 86400000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
</script>
