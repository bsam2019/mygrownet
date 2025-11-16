<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-[200000]">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/50" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-0 relative z-10">
                    <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[85vh] flex flex-col">
                        <!-- Header -->
                        <div class="sticky top-0 text-white px-6 py-4 rounded-t-3xl flex-shrink-0 bg-gradient-to-r from-blue-600 to-indigo-600">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold">Support Tickets</h3>
                                <button @click="closeModal" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-3 overflow-y-auto flex-1">
                            <!-- Filter Tabs -->
                            <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
                                <button
                                    v-for="status in ['all', 'open', 'in_progress', 'resolved']"
                                    :key="status"
                                    @click="filterStatus = status"
                                    :class="[
                                        'px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                                        filterStatus === status
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ formatStatusLabel(status) }}
                                </button>
                            </div>

                            <!-- Tickets List -->
                            <div v-if="filteredTickets.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm">No tickets found</p>
                            </div>

                            <div v-else class="space-y-2">
                                <button
                                    v-for="ticket in filteredTickets"
                                    :key="ticket.id"
                                    @click="selectTicket(ticket)"
                                    class="w-full text-left bg-gray-50 hover:bg-gray-100 rounded-lg p-4 transition-colors active:scale-98"
                                >
                                    <div class="flex items-start justify-between mb-2">
                                        <span :class="['text-xs font-medium px-2 py-1 rounded-full', getStatusColor(ticket.status)]">
                                            {{ getStatusLabel(ticket.status) }}
                                        </span>
                                        <span :class="['text-xs font-medium px-2 py-1 rounded-full', getPriorityColor(ticket.priority)]">
                                            {{ getPriorityLabel(ticket.priority) }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ ticket.subject }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ formatTime(ticket.createdAt) }}</p>
                                </button>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-gray-200 px-6 py-4 flex gap-3">
                            <button
                                @click="showCreateForm = true"
                                class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-blue-700 active:scale-95 transition-all"
                            >
                                New Ticket
                            </button>
                            <button
                                @click="closeModal"
                                class="px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <!-- Ticket Detail Modal -->
    <SupportTicketDetailModal
        v-if="selectedTicket"
        :is-open="!!selectedTicket"
        :ticket="selectedTicket"
        @close="selectedTicket = null"
        @updated="handleTicketUpdated"
    />

    <!-- Create Ticket Modal -->
    <CreateSupportTicketModal
        :is-open="showCreateForm"
        @close="showCreateForm = false"
        @created="handleTicketCreated"
    />

    <Toast
        :show="showToast"
        :message="toastMessage"
        :type="toastType"
        @close="showToast = false"
    />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import SupportTicketDetailModal from './SupportTicketDetailModal.vue';
import CreateSupportTicketModal from './CreateSupportTicketModal.vue';
import Toast from './Toast.vue';

interface Ticket {
    id: number;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    createdAt: string;
}

interface Props {
    isOpen: boolean;
    tickets: Ticket[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
}>();

const filterStatus = ref<'all' | 'open' | 'in_progress' | 'resolved'>('all');
const selectedTicket = ref<Ticket | null>(null);
const showCreateForm = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error' | 'warning' | 'info'>('success');

const filteredTickets = computed(() => {
    if (filterStatus.value === 'all') return props.tickets;
    return props.tickets.filter(t => t.status === filterStatus.value);
});

const showToastMessage = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

function closeModal() {
    emit('close');
}

function selectTicket(ticket: Ticket) {
    selectedTicket.value = ticket;
}

function handleTicketUpdated() {
    showToastMessage('Ticket updated successfully', 'success');
}

function handleTicketCreated() {
    showCreateForm.value = false;
    showToastMessage('Ticket created successfully', 'success');
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

const formatStatusLabel = (s: string) => ({
    all: 'All',
    open: 'Open',
    in_progress: 'In Progress',
    resolved: 'Resolved',
}[s] || s);

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
