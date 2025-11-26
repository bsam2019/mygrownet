<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Investor Messages</h1>
          <p class="text-gray-600 mt-1">
            Communicate with investors
            <span v-if="unreadCount > 0" class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-sm rounded-full">
              {{ unreadCount }} unread
            </span>
          </p>
        </div>
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
            class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
            :class="{ 'bg-blue-50': !message.is_read && message.direction === 'inbound' }"
            @click="viewConversation(message.investor_account_id)"
          >
            <div class="flex items-start gap-4">
              <!-- Avatar -->
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                :class="message.direction === 'inbound' ? 'bg-blue-100' : 'bg-green-100'"
              >
                <ArrowDownIcon v-if="message.direction === 'inbound'" class="h-5 w-5 text-blue-600" aria-hidden="true" />
                <ArrowUpIcon v-else class="h-5 w-5 text-green-600" aria-hidden="true" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-sm font-medium text-gray-900">
                    {{ message.investor_name }}
                    <span class="text-gray-500 font-normal">{{ message.investor_email }}</span>
                  </p>
                  <span class="text-xs text-gray-500">{{ message.created_at_human }}</span>
                </div>
                <p class="text-sm font-medium text-gray-800 mb-1">
                  <span v-if="message.direction === 'outbound'" class="text-green-600">[Sent] </span>
                  {{ message.subject }}
                </p>
                <p class="text-sm text-gray-600 truncate">{{ message.preview }}</p>
              </div>

              <!-- Unread indicator -->
              <div v-if="!message.is_read && message.direction === 'inbound'" class="flex-shrink-0">
                <span class="w-2 h-2 bg-red-600 rounded-full block"></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-lg p-12 text-center">
        <ChatBubbleLeftRightIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" aria-hidden="true" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No messages yet</h3>
        <p class="text-gray-600 mb-6">Start a conversation with an investor</p>
        <button
          @click="showComposeModal = true"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <PencilSquareIcon class="h-5 w-5 mr-2" aria-hidden="true" />
          Send First Message
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.has_more" class="mt-6 flex justify-center">
        <button
          @click="loadMore"
          class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
        >
          Load More
        </button>
      </div>
    </div>

    <!-- Compose Modal -->
    <Teleport to="body">
      <div v-if="showComposeModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showComposeModal = false"></div>
          
          <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900">New Message to Investor</h3>
              <button @click="showComposeModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close modal">
                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
              </button>
            </div>

            <form @submit.prevent="sendMessage">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Investor</label>
                  <select
                    v-model="composeForm.investor_account_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select an investor...</option>
                    <option v-for="inv in investors" :key="inv.id" :value="inv.id">
                      {{ inv.name }} ({{ inv.email }})
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                  <input
                    v-model="composeForm.subject"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Message subject"
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
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
  ChatBubbleLeftRightIcon,
  PencilSquareIcon,
  XMarkIcon,
  ArrowDownIcon,
  ArrowUpIcon,
} from '@heroicons/vue/24/outline';

interface Message {
  id: number;
  investor_account_id: number;
  investor_name: string;
  investor_email: string;
  subject: string;
  preview: string;
  direction: 'inbound' | 'outbound';
  is_read: boolean;
  created_at_human: string;
}

interface Investor {
  id: number;
  name: string;
  email: string;
}

interface Pagination {
  current_page: number;
  per_page: number;
  has_more: boolean;
}

const props = defineProps<{
  messages: Message[];
  unreadCount: number;
  investors: Investor[];
  pagination: Pagination;
}>();

const showComposeModal = ref(false);
const sending = ref(false);

const composeForm = ref({
  investor_account_id: '',
  subject: '',
  content: '',
});

const viewConversation = (investorAccountId: number) => {
  router.visit(route('admin.investor-messages.show', investorAccountId));
};

const sendMessage = () => {
  if (!composeForm.value.investor_account_id || !composeForm.value.subject.trim() || !composeForm.value.content.trim()) return;
  
  sending.value = true;
  
  router.post(route('admin.investor-messages.store'), composeForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showComposeModal.value = false;
      composeForm.value = { investor_account_id: '', subject: '', content: '' };
    },
    onFinish: () => {
      sending.value = false;
    },
  });
};

const loadMore = () => {
  router.visit(route('admin.investor-messages.index', { page: props.pagination.current_page + 1 }), {
    preserveScroll: true,
    preserveState: true,
  });
};
</script>
