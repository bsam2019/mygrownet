<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import FileUpload from '@/components/CMS/FileUpload.vue';

interface Props {
    show: boolean;
    customerId: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'close': [];
}>();

const form = useForm({
    file: null as File | null,
    document_type: 'other' as 'contract' | 'design' | 'quote' | 'other',
    description: '',
});

const fileError = ref('');

const handleFileChange = (file: File | null) => {
    form.file = file;
    fileError.value = '';
};

const handleFileError = (error: string) => {
    fileError.value = error;
};

const submit = () => {
    if (!form.file) {
        fileError.value = 'Please select a file';
        return;
    }

    form.post(route('cms.customers.documents.upload', props.customerId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const close = () => {
    form.reset();
    fileError.value = '';
    emit('close');
};
</script>

<template>
    <TransitionRoot as="template" :show="show">
        <Dialog as="div" class="relative z-50" @close="close">
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
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                            <div class="absolute right-0 top-0 pr-4 pt-4">
                                <button
                                    type="button"
                                    class="rounded-md bg-white text-gray-400 hover:text-gray-500"
                                    @click="close"
                                >
                                    <span class="sr-only">Close</span>
                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                </button>
                            </div>

                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 w-full text-center sm:mt-0 sm:text-left">
                                    <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                                        Upload Document
                                    </DialogTitle>

                                    <form @submit.prevent="submit" class="mt-6 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Document Type *
                                            </label>
                                            <select
                                                v-model="form.document_type"
                                                required
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            >
                                                <option value="contract">Contract</option>
                                                <option value="design">Design</option>
                                                <option value="quote">Quote</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>

                                        <FileUpload
                                            :model-value="form.file"
                                            @update:model-value="handleFileChange"
                                            @error="handleFileError"
                                            label="Select File"
                                            accept="image/*,application/pdf,.doc,.docx"
                                            :max-size="5"
                                            :error="fileError"
                                        />

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Description (Optional)
                                            </label>
                                            <textarea
                                                v-model="form.description"
                                                rows="3"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Add a description for this document..."
                                            />
                                        </div>

                                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                                            <button
                                                type="submit"
                                                :disabled="form.processing || !form.file"
                                                class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 sm:w-auto"
                                            >
                                                {{ form.processing ? 'Uploading...' : 'Upload' }}
                                            </button>
                                            <button
                                                type="button"
                                                class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                                @click="close"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
