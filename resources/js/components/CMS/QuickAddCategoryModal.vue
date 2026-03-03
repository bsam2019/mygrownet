<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <!-- Header -->
              <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                      <TagIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div>
                      <DialogTitle class="text-lg font-semibold text-white">
                        Quick Add Category
                      </DialogTitle>
                      <p class="text-sm text-blue-100">Create a new expense category</p>
                    </div>
                  </div>
                  <button
                    @click="$emit('close')"
                    class="rounded-lg p-1 text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                  >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </div>
              </div>

              <!-- Form -->
              <form @submit.prevent="submit" class="p-6 space-y-5">
                <!-- Category Name -->
                <FormInput
                  v-model="form.name"
                  label="Category Name"
                  placeholder="e.g., Office Supplies, Travel, Marketing"
                  required
                  :error="form.errors.name"
                  help-text="A clear, descriptive name for this expense category"
                />

                <!-- Description -->
                <FormInput
                  v-model="form.description"
                  label="Description"
                  type="textarea"
                  :rows="2"
                  placeholder="Optional description of what expenses belong in this category"
                  :error="form.errors.description"
                />

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-500/30 transition-all"
                  >
                    <PlusCircleIcon v-if="!form.processing" class="h-5 w-5" aria-hidden="true" />
                    <ArrowPathIcon v-else class="h-5 w-5 animate-spin" aria-hidden="true" />
                    <span v-if="form.processing">Creating...</span>
                    <span v-else>Create Category</span>
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { TagIcon, XMarkIcon, PlusCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import FormInput from '@/components/CMS/FormInput.vue';

interface Props {
  open: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'success', category: any): void;
}>();

const form = useForm({
  name: '',
  description: '',
  requires_approval: false,
  approval_limit: null,
});

const submit = () => {
  form.post(route('cms.expense-categories.store'), {
    preserveScroll: true,
    onSuccess: (page) => {
      // Get the newly created category from the response
      const categories = page.props.expenseCategories as any[];
      const newCategory = categories[categories.length - 1];
      
      emit('success', newCategory);
      form.reset();
    },
  });
};
</script>
