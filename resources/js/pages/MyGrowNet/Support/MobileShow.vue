<template>
    <Head :title="`Ticket #${ticket.id}`" />
    
    <div class="min-h-screen bg-gray-50">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg sticky top-0 z-10">
            <div class="px-4 py-4">
                <div class="flex items-center gap-3">
                    <button
                        @click="router.visit(route('mygrownet.support.index', { mobile: 1 }))"
                        class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="flex-1">
                        <h1 class="text-lg font-bold">Ticket #{{ ticket.id }}</h1>
                        <p class="text-xs text-white/80">{{ getCategoryLabel(ticket.category) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusColor(ticket.status)]">
                        {{ getStatusLabel(ticket.status) }}
                    </span>
                    <span :class="['px-3 py-1 text-sm font-medium rounded-full', getPriorityColor(ticket.priority)]">
                        {{ getPriorityLabel(ticket.priority) }}
                    </span>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">{{ ticket.subject }}</h2>
                <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ ticket.description }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                    Created {{ formatDateTime(ticket.createdAt) }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Comments</h3>
                
                <div v-if="comments.length === 0" class="text-center py-6 text-gray-500 text-sm">
                    No comments yet
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="comment in comments"
                        :key="comment.id"
                        :class="['rounded-lg p-3', comment.userId === ticket.userId ? 'bg-blue-50' : 'bg-gray-50']"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span :class="['text-xs font-medium', comment.userId === ticket.userId ? 'text-blue-900' : 'text-gray-900']">
                                {{ comment.userId === ticket.userId ? 'You' : 'Support Team' }}
                            </span>
                            <span class="text-xs text-gray-500">{{ formatTime(comment.createdAt) }}</span>
                        </div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ comment.comment }}</p>
                    </div>
                </div>
            </div>

            <div v-if="!ticket.status.includes('closed')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form @submit.prevent="submitComment">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Add Comment</label>
                    <textarea
                        v-model="commentForm.comment"
                        rows="4"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                        placeholder="Type your message..."
                        required
                    ></textarea>
                    <p v-if="commentForm.errors.comment" class="mt-1 text-sm text-red-600">
                        {{ commentForm.errors.comment }}
                    </p>
                    <button
                        type="submit"
                        :disabled="commentForm.processing"
                        class="mt-3 w-full bg-blue-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ commentForm.processing ? 'Sending...' : 'Send Comment' }}
                    </button>
                </form>
            </div>

            <div v-else class="bg-gray-100 rounded-xl p-4 text-center">
                <p class="text-sm text-gray-600">This ticket is closed</p>
                <p class="text-xs text-gray-500 mt-1">No further comments can be added</p>
            </div>
        </div>

        <BottomNavigation :active-tab="'support'" />

        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />
    </div>
</template>

<script setup lang="ts">
import { useForm, Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import BottomNavigation from '@/components/Mobile/BottomNavigation.vue';
import Toast from '@/components/Mobile/Toast.vue';

defineOptions({ layout: null });

const page = usePage();
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error' | 'warning' | 'info'>('success');

const showToastMessage = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
};

watch(() => page.props.flash, (flash: any) => {
  if (flash?.success) {
    showToastMessage(flash.success, 'success');
  }
  if (flash?.error) {
    showToastMessage(flash.error, 'error');
  }
}, { deep: true });

const props = defineProps<{
    ticket: { id: number; userId: number; subject: string; description: string; category: string; priority: string; status: string; createdAt: string; };
    comments: Array<{ id: number; userId: number; comment: string; createdAt: string; }>;
}>();

const commentForm = useForm({ comment: '' });

function submitComment() {
    commentForm.post(route('mygrownet.support.comment', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
}

const getStatusColor = (s: string) => ({ open: 'bg-blue-100 text-blue-800', in_progress: 'bg-amber-100 text-amber-800', waiting: 'bg-purple-100 text-purple-800', resolved: 'bg-green-100 text-green-800', closed: 'bg-gray-100 text-gray-800' }[s] || 'bg-blue-100 text-blue-800');
const getStatusLabel = (s: string) => ({ open: 'Open', in_progress: 'In Progress', waiting: 'Waiting', resolved: 'Resolved', closed: 'Closed' }[s] || s);
const getPriorityColor = (p: string) => ({ low: 'bg-gray-100 text-gray-600', medium: 'bg-blue-100 text-blue-600', high: 'bg-amber-100 text-amber-600', urgent: 'bg-red-100 text-red-600' }[p] || 'bg-blue-100 text-blue-600');
const getPriorityLabel = (p: string) => p.charAt(0).toUpperCase() + p.slice(1);
const getCategoryLabel = (c: string) => ({ technical: 'Technical Support', financial: 'Financial Issue', account: 'Account Management', general: 'General Inquiry' }[c] || c);

function formatTime(d: string) {
    const date = new Date(d), now = new Date(), diff = now.getTime() - date.getTime();
    const mins = Math.floor(diff / 60000), hours = Math.floor(diff / 3600000), days = Math.floor(diff / 86400000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}
</script>
