<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    ChatBubbleLeftRightIcon,
    PaperAirplaneIcon,
    TrashIcon,
    PencilIcon,
    XMarkIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    type: string;
    id: number;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const isSubdomain = computed(() => (page.props as any).routeName?.startsWith('stockflow.sub.'));

const baseUrl = computed(() => {
    return isSubdomain.value ? '/comments' : '/stockflow/comments';
});

const comments = ref<any[]>([]);
const loading = ref(true);
const newComment = ref('');
const editingId = ref<number | null>(null);
const editBody = ref('');
const submitting = ref(false);

const fetchComments = async () => {
    loading.value = true;
    try {
        const res = await fetch(`${baseUrl.value}?type=${props.type}&id=${props.id}`);
        const data = await res.json();
        comments.value = data.comments ?? [];
    } catch (e) {
        comments.value = [];
    } finally {
        loading.value = false;
    }
};

const addComment = async () => {
    if (!newComment.value.trim() || submitting.value) return;
    submitting.value = true;
    try {
        const res = await fetch(baseUrl.value, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token ?? '' },
            body: JSON.stringify({ type: props.type, id: props.id, body: newComment.value.trim() }),
        });
        if (res.ok) {
            newComment.value = '';
            await fetchComments();
        }
    } finally {
        submitting.value = false;
    }
};

const startEdit = (comment: any) => {
    editingId.value = comment.id;
    editBody.value = comment.body;
};

const cancelEdit = () => {
    editingId.value = null;
    editBody.value = '';
};

const saveEdit = async (commentId: number) => {
    if (!editBody.value.trim() || submitting.value) return;
    submitting.value = true;
    try {
        const res = await fetch(`${baseUrl.value}/${commentId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token ?? '' },
            body: JSON.stringify({ body: editBody.value.trim() }),
        });
        if (res.ok) {
            editingId.value = null;
            editBody.value = '';
            await fetchComments();
        }
    } finally {
        submitting.value = false;
    }
};

const deleteComment = async (commentId: number) => {
    if (!confirm('Delete this comment?')) return;
    try {
        await fetch(`${baseUrl.value}/${commentId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': (page.props as any).csrf_token ?? '' },
        });
        await fetchComments();
    } catch {}
};

const commentCount = computed(() => comments.value.length);

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(fetchComments);
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-2">
                <ChatBubbleLeftRightIcon class="h-5 w-5 text-emerald-600" />
                <h3 class="text-sm font-semibold text-gray-900">Comments</h3>
                <span class="ml-auto text-xs text-gray-500">{{ commentCount }} comment{{ commentCount !== 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="px-5 py-4 space-y-4 max-h-80 overflow-y-auto">
            <div v-if="loading" class="text-center py-6">
                <div class="animate-spin h-6 w-6 border-2 border-emerald-600 border-t-transparent rounded-full mx-auto"></div>
            </div>

            <div v-else-if="comments.length === 0" class="text-center py-6">
                <ChatBubbleLeftRightIcon class="h-10 w-10 text-gray-300 mx-auto mb-2" />
                <p class="text-sm text-gray-500">No comments yet</p>
            </div>

            <div v-for="comment in comments" :key="comment.id" class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <span class="text-xs font-semibold text-emerald-700">{{ comment.user?.name?.charAt(0)?.toUpperCase() || '?' }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-gray-900">{{ comment.user?.name || 'Unknown' }}</span>
                        <span class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</span>
                        <div v-if="comment.sa_user_id === user?.id" class="ml-auto flex items-center gap-1">
                            <button @click="startEdit(comment)" class="p-1 text-gray-400 hover:text-emerald-600 rounded">
                                <PencilIcon class="h-3.5 w-3.5" />
                            </button>
                            <button @click="deleteComment(comment.id)" class="p-1 text-gray-400 hover:text-red-600 rounded">
                                <TrashIcon class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <div v-if="editingId === comment.id">
                        <textarea
                            v-model="editBody"
                            rows="2"
                            class="w-full rounded-lg border-gray-200 text-sm resize-none focus:ring-emerald-500 focus:border-emerald-500"
                        ></textarea>
                        <div class="flex items-center gap-2 mt-1">
                            <button
                                @click="saveEdit(comment.id)"
                                :disabled="submitting"
                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50"
                            >
                                <CheckIcon class="h-3.5 w-3.5" /> Save
                            </button>
                            <button
                                @click="cancelEdit"
                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
                            >
                                <XMarkIcon class="h-3.5 w-3.5" /> Cancel
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-700 whitespace-pre-wrap">{{ comment.body }}</p>
                </div>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/30">
            <div class="flex gap-2">
                <input
                    v-model="newComment"
                    @keydown.enter.prevent="addComment"
                    type="text"
                    placeholder="Write a comment..."
                    class="flex-1 rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                />
                <button
                    @click="addComment"
                    :disabled="!newComment.trim() || submitting"
                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <PaperAirplaneIcon class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>
