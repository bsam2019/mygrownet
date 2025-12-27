<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { ArrowLeftIcon, EnvelopeIcon, PhoneIcon, ClockIcon, CheckCircleIcon, TrashIcon, ArchiveBoxIcon } from '@heroicons/vue/24/outline';

interface Message {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    subject: string | null;
    message: string;
    status: 'unread' | 'read' | 'replied' | 'archived';
    reply: string | null;
    replied_at: string | null;
    replied_by_user?: { name: string } | null;
    created_at: string;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    message: Message;
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const showDeleteModal = ref(false);

const replyForm = useForm({
    reply: props.message.reply || '',
});

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-ZM', { 
    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' 
});

const getStatusConfig = (status: string) => {
    const configs: Record<string, { bg: string; text: string; label: string }> = {
        unread: { bg: 'bg-blue-100', text: 'text-blue-700', label: 'Unread' },
        read: { bg: 'bg-gray-100', text: 'text-gray-700', label: 'Read' },
        replied: { bg: 'bg-emerald-100', text: 'text-emerald-700', label: 'Replied' },
        archived: { bg: 'bg-amber-100', text: 'text-amber-700', label: 'Archived' },
    };
    return configs[status] || { bg: 'bg-gray-100', text: 'text-gray-600', label: status };
};

const submitReply = () => {
    replyForm.post(`/sites/${props.site.subdomain}/dashboard/messages/${props.message.id}/reply`);
};

const archiveMessage = () => {
    router.put(`/sites/${props.site.subdomain}/dashboard/messages/${props.message.id}/status`, { status: 'archived' });
};

const deleteMessage = () => {
    router.delete(`/sites/${props.site.subdomain}/dashboard/messages/${props.message.id}`);
};

const userInitials = computed(() => props.message.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2));
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="View Message">
        <Head :title="`Message from ${message.name} - ${site.name}`" />

        <div class="max-w-3xl mx-auto">
            <Link :href="`/sites/${site.subdomain}/dashboard/messages`" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                Back to Messages
            </Link>

            <!-- Message Header -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-lg font-bold" :style="{ backgroundColor: primaryColor }">
                            {{ userInitials }}
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ message.name }}</h1>
                            <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                                <a :href="`mailto:${message.email}`" class="flex items-center gap-1 hover:text-blue-600">
                                    <EnvelopeIcon class="w-4 h-4" aria-hidden="true" />
                                    {{ message.email }}
                                </a>
                                <a v-if="message.phone" :href="`tel:${message.phone}`" class="flex items-center gap-1 hover:text-blue-600">
                                    <PhoneIcon class="w-4 h-4" aria-hidden="true" />
                                    {{ message.phone }}
                                </a>
                            </div>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="[getStatusConfig(message.status).bg, getStatusConfig(message.status).text]">
                                    {{ getStatusConfig(message.status).label }}
                                </span>
                                <span class="flex items-center gap-1 text-xs text-gray-400">
                                    <ClockIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                    {{ formatDate(message.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button v-if="message.status !== 'archived'" @click="archiveMessage" class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Archive">
                            <ArchiveBoxIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                        <button @click="showDeleteModal = true" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                            <TrashIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 v-if="message.subject" class="text-lg font-semibold text-gray-900 mb-3">{{ message.subject }}</h2>
                <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ message.message }}</div>
            </div>

            <!-- Previous Reply -->
            <div v-if="message.reply" class="bg-emerald-50 rounded-xl border border-emerald-200 p-6 mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <CheckCircleIcon class="w-5 h-5 text-emerald-600" aria-hidden="true" />
                    <h3 class="font-semibold text-emerald-800">Your Reply</h3>
                    <span class="text-xs text-emerald-600">{{ message.replied_at ? formatDate(message.replied_at) : '' }}</span>
                </div>
                <div class="prose prose-sm max-w-none text-emerald-900 whitespace-pre-wrap">{{ message.reply }}</div>
            </div>

            <!-- Reply Form -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">{{ message.reply ? 'Update Reply' : 'Send Reply' }}</h3>
                <form @submit.prevent="submitReply" class="space-y-4">
                    <div>
                        <textarea v-model="replyForm.reply" rows="6" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Type your reply here..."></textarea>
                        <p v-if="replyForm.errors.reply" class="mt-1 text-sm text-red-600">{{ replyForm.errors.reply }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Reply will be saved. Email notification coming soon.
                        </p>
                        <button type="submit" :disabled="replyForm.processing" class="px-6 py-2.5 text-white font-medium rounded-lg disabled:opacity-50" :style="{ backgroundColor: primaryColor }">
                            {{ replyForm.processing ? 'Saving...' : (message.reply ? 'Update Reply' : 'Save Reply') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showDeleteModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Message</h3>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete this message? This cannot be undone.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="deleteMessage" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
