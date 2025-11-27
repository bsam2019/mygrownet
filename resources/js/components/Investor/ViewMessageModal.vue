<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="message" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('update:message', null)"></div>
          
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
              <h3 class="text-lg font-semibold text-gray-900 truncate pr-4">{{ message.subject }}</h3>
              <button @click="$emit('update:message', null)" class="p-2 hover:bg-gray-100 rounded-xl transition-colors flex-shrink-0" aria-label="Close message">
                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
              </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
              <div class="flex items-center gap-4 mb-6">
                <div 
                  class="w-12 h-12 rounded-xl flex items-center justify-center"
                  :class="message.direction === 'inbound' ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gradient-to-br from-emerald-500 to-teal-600'"
                >
                  <UserIcon v-if="message.direction === 'inbound'" class="h-6 w-6 text-white" aria-hidden="true" />
                  <BuildingOfficeIcon v-else class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ message.sender_label }}</p>
                  <p class="text-sm text-gray-500">{{ message.created_at_human }}</p>
                </div>
              </div>
              <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ message.content }}</div>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50">
              <form @submit.prevent="$emit('send-reply')">
                <textarea
                  :value="reply"
                  @input="$emit('update:reply', ($event.target as HTMLTextAreaElement).value)"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow resize-none bg-white"
                  placeholder="Type your reply..."
                ></textarea>
                <div class="flex justify-end mt-3">
                  <button type="submit" :disabled="!reply?.trim() || sending" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium disabled:opacity-50">
                    {{ sending ? 'Sending...' : 'Send Reply' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { XMarkIcon, UserIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

interface Message {
  id: number;
  subject: string;
  content: string;
  direction: 'inbound' | 'outbound';
  sender_label: string;
  created_at_human: string;
}

defineProps<{
  message: Message | null;
  sending: boolean;
  reply: string;
}>();

defineEmits<{
  'update:message': [value: Message | null];
  'update:reply': [value: string];
  'send-reply': [];
}>();
</script>
