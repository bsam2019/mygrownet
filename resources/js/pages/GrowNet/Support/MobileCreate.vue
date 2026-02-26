<template>
    <Head title="Create Ticket" />
    
    <div class="min-h-screen bg-gray-50">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg sticky top-0 z-10">
            <div class="px-4 py-4">
                <div class="flex items-center gap-3">
                    <button
                        @click="router.visit(route('mygrownet.support.index', { mobile: 1 }))"
                        class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold">Create Support Ticket</h1>
                </div>
            </div>
        </div>

        <div class="p-4">
            <form @submit.prevent="submit" class="space-y-4">
                <input type="hidden" name="mobile" value="1" />
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.category"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                        <option value="">Select a category</option>
                        <option value="technical">Technical Support</option>
                        <option value="financial">Financial Issue</option>
                        <option value="account">Account Management</option>
                        <option value="general">General Inquiry</option>
                    </select>
                    <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">
                        {{ form.errors.category }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Priority</label>
                    <select
                        v-model="form.priority"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.subject"
                        type="text"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Brief description of your issue"
                        required
                    />
                    <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                        {{ form.errors.subject }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="6"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Please provide detailed information..."
                        required
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                        {{ form.errors.description }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">Minimum 10 characters</p>
                </div>

                <div class="flex gap-3 pb-20">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Ticket' }}
                    </button>
                    <button
                        type="button"
                        @click="router.visit(route('mygrownet.support.index', { mobile: 1 }))"
                        class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <BottomNavigation :active-tab="'support'" />

        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />
    </div>
</template>

<script setup lang="ts">
import { useForm, Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
  if (flash?.success) {
    showToastMessage(flash.success, 'success');
  }
  if (flash?.error) {
    showToastMessage(flash.error, 'error');
  }
}, { deep: true });

const form = useForm({
    category: '',
    priority: 'medium',
    subject: '',
    description: '',
    mobile: 1,
});

function submit() {
    form.post(route('mygrownet.support.store'), {
        onSuccess: () => {
            router.visit(route('mygrownet.support.index', { mobile: 1 }));
        }
    });
}
</script>
