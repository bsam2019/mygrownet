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
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('update:show', false)"></div>
          
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                  <PencilSquareIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">New Message</h3>
              </div>
              <button @click="$emit('update:show', false)" class="p-2 hover:bg-gray-100 rounded-xl transition-colors" aria-label="Close modal">
                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
              </button>
            </div>

            <form @submit.prevent="$emit('submit')" class="p-5">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                  <input
                    :value="form.subject"
                    @input="$emit('update:form', { ...form, subject: ($event.target as HTMLInputElement).value })"
                    type="text"
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                    placeholder="What's this about?"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                  <textarea
                    :value="form.content"
                    @input="$emit('update:form', { ...form, content: ($event.target as HTMLTextAreaElement).value })"
                    rows="6"
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow resize-none"
                    placeholder="Type your message here..."
                  ></textarea>
                </div>
              </div>
              <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="$emit('update:show', false)" class="px-5 py-2.5 text-gray-700 hover:bg-gray-100 rounded-xl transition-colors font-medium">
                  Cancel
                </button>
                <button type="submit" :disabled="sending" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium disabled:opacity-50">
                  {{ sending ? 'Sending...' : 'Send Message' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { PencilSquareIcon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps<{
  show: boolean;
  sending: boolean;
  form: { subject: string; content: string };
}>();

defineEmits<{
  'update:show': [value: boolean];
  'update:form': [value: { subject: string; content: string }];
  submit: [];
}>();
</script>
