<template>
  <InvestorLayout :investor="investor" page-title="Messages" active-page="messages" :unread-messages="unreadCount">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Messages</h1>
        <p class="mt-1 text-gray-600">
          Your conversations with the MyGrowNet team
          <span v-if="unreadCount > 0" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-full">
            {{ unreadCount }} unread
          </span>
        </p>
      </div>
      <button
        @click="showComposeModal = true"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg shadow-blue-500/25"
      >
        <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
        New Message
      </button>
    </div>

    <!-- Messages List -->
    <div v-if="messages.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="divide-y divide-gray-100">
        <div
          v-for="message in messages"
          :key="message.id"
          @click="viewMessage(message)"
          class="p-5 hover:bg-gray-50/50 cursor-pointer transition-all group"
          :class="{ 'bg-blue-50/50': !message.is_read && message.direction === 'outbound' }"
        >
          <div class="flex items-start gap-4">
            <div 
              class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105"
              :class="message.direction === 'inbound' ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gradient-to-br from-emerald-500 to-teal-600'"
            >
              <UserIcon v-if="message.direction === 'inbound'" class="h-6 w-6 text-white" aria-hidden="true" />
              <BuildingOfficeIcon v-else class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-semibold text-gray-900">{{ message.sender_label }}</p>
                <span class="text-xs text-gray-500">{{ message.created_at_human }}</span>
              </div>
              <p class="text-sm font-medium text-gray-800 mb-1">{{ message.subject }}</p>
              <p class="text-sm text-gray-500 truncate">{{ message.preview }}</p>
            </div>
            <div v-if="!message.is_read && message.direction === 'outbound'" class="flex-shrink-0 self-center">
              <span class="w-3 h-3 bg-blue-600 rounded-full block ring-4 ring-blue-100"></span>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-300 group-hover:text-gray-500 transition-colors flex-shrink-0 self-center" aria-hidden="true" />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
      <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <ChatBubbleLeftRightIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No messages yet</h3>
      <p class="text-gray-500 mb-6">Start a conversation with the MyGrowNet team</p>
      <button @click="showComposeModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
        <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
        Send Your First Message
      </button>
    </div>

    <!-- Info Banner -->
    <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6">
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
          <InformationCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
        </div>
        <div>
          <h3 class="font-semibold text-blue-900 mb-1">Direct Communication</h3>
          <p class="text-sm text-blue-700">Use this messaging system to communicate directly with the MyGrowNet team. We typically respond within 24-48 hours.</p>
        </div>
      </div>
    </div>

    <!-- Compose Modal -->
    <ComposeModal v-model:show="showComposeModal" :sending="sending" @submit="sendMessage" v-model:form="composeForm" />
    
    <!-- View Message Modal -->
    <ViewMessageModal v-model:message="selectedMessage" :sending="sending" v-model:reply="replyContent" @send-reply="sendReply" />
  </InvestorLayout>
</template>


<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import ComposeModal from '@/components/Investor/ComposeModal.vue';
import ViewMessageModal from '@/components/Investor/ViewMessageModal.vue';
import {
  ChatBubbleLeftRightIcon,
  PencilSquareIcon,
  UserIcon,
  BuildingOfficeIcon,
  InformationCircleIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline';

interface Message {
  id: number;
  subject: string;
  content: string;
  preview: string;
  direction: 'inbound' | 'outbound';
  is_read: boolean;
  sender_label: string;
  created_at_human: string;
  parent_id: number | null;
}

interface Investor {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  investor: Investor;
  messages: Message[];
  unreadCount: number;
}>();

const showComposeModal = ref(false);
const selectedMessage = ref<Message | null>(null);
const sending = ref(false);
const replyContent = ref('');
const composeForm = ref({ subject: '', content: '' });

const viewMessage = (message: Message) => {
  selectedMessage.value = message;
  if (!message.is_read && message.direction === 'outbound') {
    router.post(route('investor.messages.read', message.id), {}, { preserveScroll: true, preserveState: true });
  }
};

const sendMessage = () => {
  if (!composeForm.value.subject.trim() || !composeForm.value.content.trim()) return;
  sending.value = true;
  router.post(route('investor.messages.store'), composeForm.value, {
    preserveScroll: true,
    onSuccess: () => { showComposeModal.value = false; composeForm.value = { subject: '', content: '' }; },
    onFinish: () => { sending.value = false; },
  });
};

const sendReply = () => {
  if (!replyContent.value.trim() || !selectedMessage.value) return;
  sending.value = true;
  router.post(route('investor.messages.store'), {
    subject: `Re: ${selectedMessage.value.subject}`,
    content: replyContent.value,
    parent_id: selectedMessage.value.id,
  }, {
    preserveScroll: true,
    onSuccess: () => { replyContent.value = ''; selectedMessage.value = null; },
    onFinish: () => { sending.value = false; },
  });
};
</script>
