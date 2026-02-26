<template>
    <Head title="Support Tickets" />
    
    <div class="min-h-screen bg-gray-50">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg sticky top-0 z-10">
            <div class="px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button @click="router.visit(route('mygrownet.mobile-dashboard'))" class="p-2 hover:bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold">Support</h1>
                    </div>
                    <button @click="router.visit(route('mygrownet.support.create', { mobile: 1 }))" class="p-2 bg-white/20 hover:bg-white/30 rounded-lg">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex border-t border-white/20 overflow-x-auto">
                <button @click="filterStatus = 'all'" :class="['px-4 py-3 text-sm font-medium transition-colors relative whitespace-nowrap', filterStatus === 'all' ? 'text-white' : 'text-white/60']">
                    All
                    <div v-if="filterStatus === 'all'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-white"></div>
                </button>
                <button @click="filterStatus = 'open'" :class="['px-4 py-3 text-sm font-medium transition-colors relative whitespace-nowrap', filterStatus === 'open' ? 'text-white' : 'text-white/60']">
                    Open
                    <span v-if="openCount > 0" class="ml-1 px-1.5 py-0.5 bg-blue-500 text-white text-xs rounded-full">{{ openCount }}</span>
                    <div v-if="filterStatus === 'open'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-white"></div>
                </button>
                <button @click="filterStatus = 'in_progress'" :class="['px-4 py-3 text-sm font-medium transition-colors relative whitespace-nowrap', filterStatus === 'in_progress' ? 'text-white' : 'text-white/60']">
                    In Progress
                    <div v-if="filterStatus === 'in_progress'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-white"></div>
                </button>
                <button @click="filterStatus = 'resolved'" :class="['px-4 py-3 text-sm font-medium transition-colors relative whitespace-nowrap', filterStatus === 'resolved' ? 'text-white' : 'text-white/60']">
                    Resolved
                    <div v-if="filterStatus === 'resolved'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-white"></div>
                </button>
            </div>
        </div>

        <div class="p-4 space-y-2 pb-20">
            <div v-if="filteredTickets.length === 0" class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 font-medium">No tickets found</p>
                <p class="text-gray-400 text-sm mt-1">Create a ticket to get support</p>
            </div>

            <div v-for="ticket in filteredTickets" :key="ticket.id" @click="openTicket(ticket)" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 active:scale-98 transition-transform">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div :class="['w-12 h-12 rounded-full flex items-center justify-center', getPriorityGradient(ticket.priority)]">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <span :class="['text-xs font-medium px-2 py-1 rounded-full', getStatusColor(ticket.status)]">{{ getStatusLabel(ticket.status) }}</span>
                            <span class="text-xs text-gray-500 ml-2 flex-shrink-0">{{ formatTime(ticket.createdAt) }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 mb-1 line-clamp-1">{{ ticket.subject }}</p>
                        <p class="text-sm text-gray-500 line-clamp-2 mb-2">{{ ticket.description }}</p>
                        <div class="flex items-center gap-2">
                            <span :class="['text-xs px-2 py-0.5 rounded-full', getPriorityColor(ticket.priority)]">{{ getPriorityLabel(ticket.priority) }}</span>
                            <span class="text-xs text-gray-500">{{ getCategoryLabel(ticket.category) }}</span>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </div>

        <BottomNavigation :active-tab="'support'" />
        <Toast :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
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
  if (flash?.success) showToastMessage(flash.success, 'success');
  if (flash?.error) showToastMessage(flash.error, 'error');
}, { deep: true });

interface Ticket {
    id: number;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    createdAt: string;
}

const props = defineProps<{ tickets: Ticket[]; }>();
const filterStatus = ref<'all' | 'open' | 'in_progress' | 'resolved'>('all');

const filteredTickets = computed(() => filterStatus.value === 'all' ? props.tickets : props.tickets.filter(t => t.status === filterStatus.value));
const openCount = computed(() => props.tickets.filter(t => t.status === 'open').length);

const getStatusColor = (s: string) => ({ open: 'bg-blue-100 text-blue-800', in_progress: 'bg-amber-100 text-amber-800', waiting: 'bg-purple-100 text-purple-800', resolved: 'bg-green-100 text-green-800', closed: 'bg-gray-100 text-gray-800' }[s] || 'bg-blue-100 text-blue-800');
const getStatusLabel = (s: string) => ({ open: 'Open', in_progress: 'In Progress', waiting: 'Waiting', resolved: 'Resolved', closed: 'Closed' }[s] || s);
const getPriorityColor = (p: string) => ({ low: 'bg-gray-100 text-gray-600', medium: 'bg-blue-100 text-blue-600', high: 'bg-amber-100 text-amber-600', urgent: 'bg-red-100 text-red-600' }[p] || 'bg-blue-100 text-blue-600');
const getPriorityGradient = (p: string) => ({ low: 'bg-gradient-to-br from-gray-400 to-gray-500', medium: 'bg-gradient-to-br from-blue-500 to-blue-600', high: 'bg-gradient-to-br from-amber-500 to-amber-600', urgent: 'bg-gradient-to-br from-red-500 to-red-600' }[p] || 'bg-gradient-to-br from-blue-500 to-blue-600');
const getPriorityLabel = (p: string) => p.charAt(0).toUpperCase() + p.slice(1);
const getCategoryLabel = (c: string) => ({ technical: 'Technical', financial: 'Financial', account: 'Account', general: 'General' }[c] || c);

function formatTime(d: string) {
    const date = new Date(d), now = new Date(), diff = now.getTime() - date.getTime();
    const mins = Math.floor(diff / 60000), hours = Math.floor(diff / 3600000), days = Math.floor(diff / 86400000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function openTicket(ticket: Ticket) {
    router.visit(route('mygrownet.support.show', { id: ticket.id, mobile: 1 }));
}
</script>
