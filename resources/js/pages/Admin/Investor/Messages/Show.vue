<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex items-center gap-4 mb-6">
        <Link
          :href="route('admin.investor-messages.index')"
          class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
        >
          <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ investor.name }}</h1>
          <p class="text-gray-600">{{ investor.email }}</p>
        </div>
      </div>

      <!-- Conversation Thread -->
      <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
          <h2 class="font-semibold text-gray-900">Conversation</h2>
        </div>

        <div class="divide-y divide-gray-100 max-h-[60vh] overflow-y-auto">
          <div
            v-for="message in messages"
            :key="message.id"
            class="p-4"
            :class="message.direction === 'outbound' ? 'bg-blue-50' : 'bg-white'"
          >
            <div class="flex items-start gap-3">
              <!-- Avatar -->
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                :class="message.direction === 'inbound' ? 'bg-gray-200' : 'bg-blue-200'"
              >
                <UserIcon v-if="message.direction === 'inbound'" class="h-5 w-5 text-gray-600" aria-hidden="true" />
                <BuildingOfficeIcon v-else class="h-5 w-5 text-blue-600" aria-hidden="true" />
              </div>

              <!-- Content -->
              <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-sm font-medium text-gray-900">
                    {{ message.direction === 'inbound' ? investor.name : (message.admin_name || 'Admin') }}
                  </p>
                  <span class="text-xs text-gray-500">{{ message.created_at_human }}</span>
                </div>
                <p class="text-sm font-medium text-gray-800 mb-2">{{ message.subject }}</p>
                <div class="prose prose-sm max-w-none text-gray-700" v-html="formatContent(message.content)"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="messages.length === 0" class="p-12 text-center">
          <ChatBubbleLeftRightIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" aria-hidden="true" />
          <p class="text-gray-600">No messages in this conversation yet</p>
        </div>
      </div>

      <!-- Reply Form -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Send Reply</h3>
        <form @submit.prevent="sendReply">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
              <input
                v-model="replyForm.subject"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Message subject"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
              <textarea
                v-model="replyForm.content"
                rows="4"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Type your reply..."
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end mt-4">
            <button
              type="submit"
              :disabled="sending"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ sending ? 'Sending...' : 'Send Reply' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
  ArrowLeftIcon,
  UserIcon,
  BuildingOfficeIcon,
  ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';

interface Message {
  id: number;
  subject: string;
  content: string;
  direction: 'inbound' | 'outbound';
  is_read: boolean;
  admin_name?: string;
  created_at_human: string;
}

interface Investor {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  investor: Investor;
  messages: Message[];
}>();

const sending = ref(false);

const lastMessage = computed(() => props.messages[props.messages.length - 1]);

const replyForm = ref({
  subject: lastMessage.value ? `Re: ${lastMessage.value.subject.replace(/^Re: /, '')}` : '',
  content: '',
});

const formatContent = (content: string): string => {
  return content.replace(/\n/g, '<br>');
};

const sendReply = () => {
  if (!replyForm.value.subject.trim() || !replyForm.value.content.trim()) return;
  
  sending.value = true;
  
  router.post(route('admin.investor-messages.store'), {
    investor_account_id: props.investor.id,
    subject: replyForm.value.subject,
    content: replyForm.value.content,
    parent_id: lastMessage.value?.id,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      replyForm.value.content = '';
    },
    onFinish: () => {
      sending.value = false;
    },
  });
};
</script>
