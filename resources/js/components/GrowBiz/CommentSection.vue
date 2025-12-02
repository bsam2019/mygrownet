<template>
    <div class="space-y-4">
        <!-- Add Comment Form -->
        <form @submit.prevent="submitComment" class="space-y-3">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                        <UserIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                    </div>
                </div>
                <div class="flex-1">
                    <textarea
                        v-model="newComment"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none text-sm"
                        placeholder="Add a comment..."
                        :disabled="isSubmitting"
                    />
                    <div class="flex justify-end mt-2">
                        <button
                            type="submit"
                            :disabled="!newComment.trim() || isSubmitting"
                            class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
                        >
                            <ArrowPathIcon v-if="isSubmitting" class="h-4 w-4 animate-spin" aria-hidden="true" />
                            <span>{{ isSubmitting ? 'Posting...' : 'Post Comment' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Comments List -->
        <div v-if="comments.length === 0" class="text-center py-6 text-gray-500">
            <ChatBubbleLeftRightIcon class="h-10 w-10 mx-auto mb-2 text-gray-300" aria-hidden="true" />
            <p class="text-sm">No comments yet. Be the first to comment!</p>
        </div>

        <div v-else class="space-y-4">
            <TransitionGroup name="comment-list">
                <div 
                    v-for="comment in comments" 
                    :key="comment.id" 
                    class="flex gap-3 group"
                >
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <span class="text-xs font-medium text-gray-600">
                                {{ getInitials(comment.user?.name) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="bg-gray-50 rounded-xl px-4 py-3">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ comment.user?.name || 'Unknown User' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ formatTime(comment.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ comment.content }}</p>
                        </div>
                        
                        <!-- Comment Actions -->
                        <div class="flex items-center gap-3 mt-1 ml-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button 
                                v-if="canDelete(comment)"
                                @click="handleDelete(comment.id)"
                                :disabled="deletingId === comment.id"
                                class="text-xs text-red-600 hover:text-red-700 disabled:opacity-50"
                            >
                                {{ deletingId === comment.id ? 'Deleting...' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    UserIcon, 
    ChatBubbleLeftRightIcon,
    ArrowPathIcon 
} from '@heroicons/vue/24/outline';

interface Comment {
    id: number;
    content: string;
    user?: { id: number; name: string };
    created_at: string;
}

interface Props {
    comments: Comment[];
    taskId: number;
    currentUserId?: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    commentAdded: [];
    commentDeleted: [commentId: number];
}>();

const newComment = ref('');
const isSubmitting = ref(false);
const deletingId = ref<number | null>(null);

const submitComment = () => {
    if (!newComment.value.trim() || isSubmitting.value) return;
    
    isSubmitting.value = true;
    
    router.post(route('growbiz.tasks.comments.store', props.taskId), {
        content: newComment.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newComment.value = '';
            emit('commentAdded');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const handleDelete = (commentId: number) => {
    if (deletingId.value) return;
    
    deletingId.value = commentId;
    
    router.delete(route('growbiz.tasks.comments.destroy', [props.taskId, commentId]), {
        preserveScroll: true,
        onSuccess: () => {
            emit('commentDeleted', commentId);
        },
        onFinish: () => {
            deletingId.value = null;
        },
    });
};

const canDelete = (comment: Comment): boolean => {
    return props.currentUserId === comment.user?.id;
};

const getInitials = (name?: string): string => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatTime = (dateStr: string): string => {
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
};
</script>

<style scoped>
.comment-list-enter-active,
.comment-list-leave-active {
    transition: all 0.3s ease;
}
.comment-list-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}
.comment-list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
