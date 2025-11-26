<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">{{ getInitials(investor.name) }}</span>
              </div>
              <div>
                <h1 class="text-xl font-bold text-gray-900">Messages</h1>
                <p class="text-sm text-gray-600">Investor Portal</p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link :href="route('investor.dashboard')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              Dashboard
            </Link>
            <Link :href="route('investor.documents')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              Documents
            </Link>
            <Link :href="route('investor.logout')" method="post" as="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
              Logout
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Compose Button -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-900">
          Your Conversations
          <span v-if="unreadCount > 0" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-sm rounded-full">
            {{ unreadCount }} unread
          </span>
        </h2>
        <button
          @click="showComposeModal = true"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <PencilSquareIcon class="h-5 w-5 mr-2" aria-hidden="true" />
          New Message
        </button>
      </div>

      <!-- Messages List -->
      <div v-if="messages.length > 0" class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="divide-y divide-gray-200">
          <div
            v-for="message in messages"
            :key="message.id"
            @click="viewMessage(message)"
            class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
            :class="{ 'bg-blue-50': !message.is_read && message.direction === 'outbound' }"
          >
            <div class="flex items-start gap-4">
              <!-- Avatar -->
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                :class="message.direction === 'inbound' ? 'bg-blue-100' : 'bg-green-100'"
              >
                <UserIcon v-if="message.direction === 'inbound'" class="h-5 w-5 text-blue-600" aria-hidden="true" />
                <BuildingOfficeIcon v-else class="h-5 w-5 text-green-600" aria-hidden="true" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-sm font-medium text-gray-900">
                    {{ message.sender_label }}
                  </p>
                  <span class="text-xs text-gray-500">{{ message.created_at_human }}</span>
                </div>
                <p class="text-sm font-medium text-gray-800 mb-1">{{ message.subject }}</p>
                <p class="text-sm text-gray-600 truncate">{{ message.preview }}</p>
              </div>

              <!-- Unread indicator -->
              <div v-if="!message.is_read && message.direction === 'outbound'" class="flex-shrink-0">
                <span class="w-2 h-2 bg-blue-600 rounded-full block"></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-lg p-12 text-center">
        <ChatBubbleLeftRightIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" aria-hidden="true" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No messages yet</h3>
        <p class="text-gray-600 mb-6">Start a conversation with the MyGrowNet team</p>
        <button
          @click="showComposeModal = true"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <PencilSquareIcon class="h-5 w-5 mr-2" aria-hidden="true" />
          Send Your First Message
        </button>
      </div>

      <!-- Info Banner -->
      <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-start gap-4">
          <InformationCircleIcon class="h-6 w-6 text-blue-600 flex-shrink-0" aria-hidden="true" />
          <div>
            <h3 class="text-sm font-semibold text-blue-900 mb-1">Direct Communication</h3>
            <p class="text-sm text-blue-800">
              Use this messaging system to communicate directly with the MyGrowNet team about your investment, 
              ask questions, or request information. We typically respond within 24-48 hours.
            </p>
          </div>
        </div>
      </div>
    </main>

    <!-- Compose Modal -->
    <Teleport to="body">
      <div v-if="showComposeModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showComposeModal = false"></div>
          
          <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900">New Message</h3>
              <button @click="showComposeModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close modal">
                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
              </button>
            </div>

            <form @submit.prevent="sendMessage">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                  <input
                    v-model="composeForm.subject"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="What's this about?"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                  <textarea
                    v-model="composeForm.content"
                    rows="6"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Type your message here..."
                  ></textarea>
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  @click="showComposeModal = false"
                  class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="sending"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                  {{ sending ? 'Sending...' : 'Send Message' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- View Message Modal -->
    <Teleport to="body">
      <div v-if="selectedMessage" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="selectedMessage = null"></div>
          
          <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">{{ selectedMessage.subject }}</h3>
              <button @click="selectedMessage = null" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close message">
                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
              </button>
            </div>

            <!-- Message Content -->
            <div class="p-6 overflow-y-auto max-h-[50vh]">
              <div class="flex items-center gap-3 mb-4">
                <div 
                  class="w-10 h-10 rounded-full flex items-center justify-center"
                  :class="selectedMessage.direction === 'inbound' ? 'bg-blue-100' : 'bg-green-100'"
                >
                  <UserIcon v-if="selectedMessage.direction === 'inbound'" class="h-5 w-5 text-blue-600" aria-hidden="true" />
                  <BuildingOfficeIcon v-else class="h-5 w-5 text-green-600" aria-hidden="true" />
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ selectedMessage.sender_label }}</p>
                  <p class="text-xs text-gray-500">{{ selectedMessage.created_at_human }}</p>
                </div>
              </div>
              <div class="prose prose-sm max-w-none text-gray-700" v-html="formatContent(selectedMessage.content)"></div>
            </div>

            <!-- Reply Section -->
            <div class="p-4 border-t border-gray-200 bg-gray-50">
              <form @submit.prevent="sendReply">
                <textarea
                  v-model="replyContent"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Type your reply..."
                ></textarea>
                <div class="flex justify-end mt-3">
                  <button
                    type="submit"
                    :disabled="!replyContent.trim() || sending"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                  >
                    {{ sending ? 'Sending...' : 'Send Reply' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
  ChatBubbleLeftRightIcon,
  PencilSquareIcon,
  XMarkIcon,
  UserIcon,
  BuildingOfficeIcon,
  InformationCircleIcon,
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

const composeForm = ref({
  subject: '',
  content: '',
});

const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

const formatContent = (content: string): string => {
  // Convert newlines to <br> tags
  return content.replace(/\n/g, '<br>');
};

const viewMessage = (message: Message) => {
  selectedMessage.value = message;
  
  // Mark as read if it's an unread outbound message
  if (!message.is_read && message.direction === 'outbound') {
    router.post(route('investor.messages.read', message.id), {}, {
      preserveScroll: true,
      preserveState: true,
    });
  }
};

const sendMessage = () => {
  if (!composeForm.value.subject.trim() || !composeForm.value.content.trim()) return;
  
  sending.value = true;
  
  router.post(route('investor.messages.store'), composeForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showComposeModal.value = false;
      composeForm.value = { subject: '', content: '' };
    },
    onFinish: () => {
      sending.value = false;
    },
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
    onSuccess: () => {
      replyContent.value = '';
      selectedMessage.value = null;
    },
    onFinish: () => {
      sending.value = false;
    },
  });
};
</script>
