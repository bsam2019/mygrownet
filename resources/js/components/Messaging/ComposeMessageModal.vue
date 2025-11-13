<template>
    <TransitionRoot :show="show" as="template">
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
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
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
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                            <form @submit.prevent="sendMessage">
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                                            New Message
                                        </DialogTitle>
                                        <button
                                            type="button"
                                            @click="$emit('close')"
                                            class="text-gray-400 hover:text-gray-500"
                                        >
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="space-y-4">
                                        <!-- Recipient Type -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Send to
                                            </label>
                                            <div class="flex space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input
                                                        v-model="recipientType"
                                                        type="radio"
                                                        value="admin"
                                                        class="form-radio text-blue-600"
                                                    />
                                                    <span class="ml-2">Admin/Support</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input
                                                        v-model="recipientType"
                                                        type="radio"
                                                        value="upline"
                                                        class="form-radio text-blue-600"
                                                    />
                                                    <span class="ml-2">My Upline</span>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Subject -->
                                        <div>
                                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                                Subject <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="subject"
                                                v-model="form.subject"
                                                type="text"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Enter message subject"
                                                required
                                            />
                                            <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.subject }}
                                            </p>
                                        </div>

                                        <!-- Message Body -->
                                        <div>
                                            <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                                                Message <span class="text-red-500">*</span>
                                            </label>
                                            <textarea
                                                id="body"
                                                v-model="form.body"
                                                rows="6"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Type your message here..."
                                                required
                                            ></textarea>
                                            <p v-if="form.errors.body" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.body }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ form.body.length }} / 10,000 characters
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto disabled:opacity-50"
                                    >
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Send Message
                                    </button>
                                    <button
                                        type="button"
                                        @click="$emit('close')"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                    >
                                        Cancel
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
import { ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import Swal from 'sweetalert2';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();
const recipientType = ref('admin');

const form = useForm({
    recipient_id: 1, // Default to admin (ID 1)
    subject: '',
    body: '',
    parent_id: null,
});

// Update recipient based on type selection
watch(recipientType, (newType) => {
    if (newType === 'admin') {
        form.recipient_id = 1; // Admin user ID
    } else if (newType === 'upline') {
        // Get upline from user data
        const user = page.props.auth?.user as any;
        form.recipient_id = user?.referrer_id || 1;
    }
});

function sendMessage() {
    form.post(route('mygrownet.messages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
            
            // Show success toast
            Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                text: 'Your message has been sent successfully.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        },
    });
}
</script>
