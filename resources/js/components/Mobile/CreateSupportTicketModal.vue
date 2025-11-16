<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-[200002]">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/50" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-0 relative z-10">
                    <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[85vh] flex flex-col">
                        <!-- Header -->
                        <div class="sticky top-0 text-white px-6 py-4 rounded-t-3xl flex-shrink-0 bg-gradient-to-r from-blue-600 to-indigo-600">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold">Create Support Ticket</h3>
                                <button @click="closeModal" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-4 overflow-y-auto flex-1">
                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                <select v-model="form.category" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a category</option>
                                    <option value="technical">Technical Support</option>
                                    <option value="financial">Financial Issue</option>
                                    <option value="account">Account Management</option>
                                    <option value="general">General Inquiry</option>
                                </select>
                                <p v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category }}</p>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select v-model="form.priority" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                <input v-model="form.subject" type="text" placeholder="Brief description" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
                                <p v-if="errors.subject" class="mt-1 text-sm text-red-600">{{ errors.subject }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                                <textarea v-model="form.description" rows="4" placeholder="Describe your issue..." class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
                                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-gray-200 px-6 py-4 flex gap-3">
                            <button
                                @click="submitForm"
                                :disabled="submitting"
                                class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 active:scale-95 transition-all"
                            >
                                {{ submitting ? 'Creating...' : 'Create Ticket' }}
                            </button>
                            <button @click="closeModal" class="px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <Toast :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { router, usePage } from '@inertiajs/vue3';
import Toast from './Toast.vue';

interface Props {
    isOpen: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created'): void;
}>();

const page = usePage();
const form = ref({
    category: '',
    priority: 'medium',
    subject: '',
    description: '',
});

const errors = ref<Record<string, string>>({});
const submitting = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error' | 'warning' | 'info'>('success');

// Watch for flash messages from Inertia
watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        showToastMessage(flash.success, 'success');
        form.value = { category: '', priority: 'medium', subject: '', description: '' };
        emit('created');
        setTimeout(() => closeModal(), 500);
    }
    if (flash?.error) {
        showToastMessage(flash.error, 'error');
    }
}, { deep: true });

const showToastMessage = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

function submitForm() {
    errors.value = {};
    submitting.value = true;

    console.log('Submitting ticket form:', form.value);

    router.post(
        '/mygrownet/support',
        form.value,
        {
            preserveScroll: true,
            onSuccess: (page) => {
                console.log('✅ Ticket created successfully!', page);
                showToastMessage('Ticket created successfully', 'success');
                form.value = { category: '', priority: 'medium', subject: '', description: '' };
                emit('created');
                setTimeout(() => closeModal(), 500);
            },
            onError: (errors) => {
                console.error('❌ Ticket creation failed:', errors);
                if (errors && typeof errors === 'object') {
                    const errorMessages = Object.values(errors).flat().join(', ');
                    showToastMessage(errorMessages || 'Failed to create ticket', 'error');
                } else {
                    showToastMessage('Failed to create ticket. Please try again.', 'error');
                }
            },
            onFinish: () => {
                submitting.value = false;
            },
        }
    );
}

function closeModal() {
    emit('close');
}
</script>
