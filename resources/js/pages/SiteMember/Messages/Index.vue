<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { EnvelopeIcon, EnvelopeOpenIcon, TrashIcon, EyeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface Message {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    subject: string | null;
    message: string;
    status: 'unread' | 'read' | 'replied' | 'archived';
    created_at: string;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    messages: { data: Message[]; links: any; meta: any };
    unreadCount: number;
    filters: { status?: string; search?: string };
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const deletingMessage = ref<Message | null>(null);
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const getStatusConfig = (status: string) => {
    const configs: Record<string, { bg: string; text: string; label: string }> = {
        unread: { bg: 'bg-blue-100', text: 'text-blue-700', label: 'Unread' },
        read: { bg: 'bg-gray-100', text: 'text-gray-700', label: 'Read' },
        replied: { bg: 'bg-emerald-100', text: 'text-emerald-700', label: 'Replied' },
        archived: { bg: 'bg-amber-100', text: 'text-amber-700', label: 'Archived' },
    };
    return configs[status] || { bg: 'bg-gray-100', text: 'text-gray-600', label: status };
};

const applyFilters = () => {
    router.get(`/sites/${props.site.subdomain}/dashboard/messages`, {
        status: statusFilter.value || undefined,
        search: searchQuery.value || undefined,
    }, { preserveState: true });
};

const deleteMessage = () => {
    if (!deletingMessage.value) return;
    router.delete(`/sites/${props.site.subdomain}/dashboard/messages/${deletingMessage.value.id}`, {
        onSuccess: () => { deletingMessage.value = null; },
    });
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Messages">
        <Head :title="`Messages - ${site.name}`" />

        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Contact Messages</h1>
                    <p class="text-gray-500">
                        {{ unreadCount > 0 ? `${unreadCount} unread message${unreadCount > 1 ? 's' : ''}` : 'All messages read' }}
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" aria-hidden="true" />
                        <input v-model="searchQuery" @keyup.enter="applyFilters" type="text" placeholder="Search messages..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <select v-model="statusFilter" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                    </select>
                </div>
            </div>

            <!-- Messages List -->
            <div v-if="messages.data.length > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    <div v-for="msg in messages.data" :key="msg.id" class="p-5 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1 min-w-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :class="msg.status === 'unread' ? 'bg-blue-100' : 'bg-gray-100'">
                                    <component :is="msg.status === 'unread' ? EnvelopeIcon : EnvelopeOpenIcon" :class="msg.status === 'unread' ? 'text-blue-600' : 'text-gray-400'" class="w-5 h-5" aria-hidden="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p :class="msg.status === 'unread' ? 'font-semibold text-gray-900' : 'font-medium text-gray-700'">{{ msg.name }}</p>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="[getStatusConfig(msg.status).bg, getStatusConfig(msg.status).text]">
                                            {{ getStatusConfig(msg.status).label }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500">{{ msg.email }}</p>
                                    <p v-if="msg.subject" class="text-sm font-medium text-gray-800 mt-1">{{ msg.subject }}</p>
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ msg.message }}</p>
                                    <p class="text-xs text-gray-400 mt-2">{{ formatDate(msg.created_at) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <Link :href="`/sites/${site.subdomain}/dashboard/messages/${msg.id}`" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                    <EyeIcon class="w-5 h-5" aria-hidden="true" />
                                </Link>
                                <button @click="deletingMessage = msg" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                    <TrashIcon class="w-5 h-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <EnvelopeIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No messages yet</h3>
                <p class="text-gray-500">Messages from your contact form will appear here.</p>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="deletingMessage" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="deletingMessage = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Message</h3>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete this message from {{ deletingMessage.name }}?</p>
                    <div class="flex justify-end gap-3">
                        <button @click="deletingMessage = null" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="deleteMessage" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
