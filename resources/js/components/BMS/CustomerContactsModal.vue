<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon, UserIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Contact {
    id: number;
    name: string;
    title: string | null;
    email: string | null;
    phone: string | null;
    mobile: string | null;
    is_primary: boolean;
    notes: string | null;
}

interface Props {
    show: boolean;
    customerId: number;
    contacts: Contact[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'close': [];
}>();

const showForm = ref(false);
const editingContact = ref<Contact | null>(null);

const form = useForm({
    name: '',
    title: '',
    email: '',
    phone: '',
    mobile: '',
    is_primary: false,
    notes: '',
});

const isEditing = computed(() => editingContact.value !== null);

const startAdd = () => {
    form.reset();
    editingContact.value = null;
    showForm.value = true;
};

const startEdit = (contact: Contact) => {
    editingContact.value = contact;
    form.name = contact.name;
    form.title = contact.title || '';
    form.email = contact.email || '';
    form.phone = contact.phone || '';
    form.mobile = contact.mobile || '';
    form.is_primary = contact.is_primary;
    form.notes = contact.notes || '';
    showForm.value = true;
};

const cancelForm = () => {
    form.reset();
    editingContact.value = null;
    showForm.value = false;
};

const submit = () => {
    if (isEditing.value && editingContact.value) {
        form.put(route('cms.customers.contacts.update', {
            customer: props.customerId,
            contact: editingContact.value.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                cancelForm();
            },
        });
    } else {
        form.post(route('cms.customers.contacts.store', props.customerId), {
            preserveScroll: true,
            onSuccess: () => {
                cancelForm();
            },
        });
    }
};

const deleteContact = (contact: Contact) => {
    if (confirm(`Are you sure you want to delete ${contact.name}?`)) {
        form.delete(route('cms.customers.contacts.delete', {
            customer: props.customerId,
            contact: contact.id
        }), {
            preserveScroll: true,
        });
    }
};

const close = () => {
    cancelForm();
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
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                            <div class="absolute right-0 top-0 pr-4 pt-4">
                                <button
                                    type="button"
                                    class="rounded-md bg-white text-gray-400 hover:text-gray-500"
                                    aria-label="Close contacts modal"
                                    @click="close"
                                >
                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                </button>
                            </div>

                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 w-full text-center sm:mt-0 sm:text-left">
                                    <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 mb-6">
                                        Contact Persons
                                    </DialogTitle>

                                    <!-- Contact List -->
                                    <div v-if="!showForm" class="space-y-4">
                                        <div v-if="contacts.length > 0" class="space-y-3">
                                            <div
                                                v-for="contact in contacts"
                                                :key="contact.id"
                                                class="flex items-start justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
                                            >
                                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                                    <UserIcon class="h-6 w-6 text-gray-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                {{ contact.name }}
                                                            </p>
                                                            <span
                                                                v-if="contact.is_primary"
                                                                class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                                                            >
                                                                Primary
                                                            </span>
                                                        </div>
                                                        <p v-if="contact.title" class="text-xs text-gray-500">
                                                            {{ contact.title }}
                                                        </p>
                                                        <div class="mt-2 space-y-1">
                                                            <p v-if="contact.email" class="text-xs text-gray-600">
                                                                📧 {{ contact.email }}
                                                            </p>
                                                            <p v-if="contact.phone" class="text-xs text-gray-600">
                                                                📞 {{ contact.phone }}
                                                            </p>
                                                            <p v-if="contact.mobile" class="text-xs text-gray-600">
                                                                📱 {{ contact.mobile }}
                                                            </p>
                                                        </div>
                                                        <p v-if="contact.notes" class="mt-2 text-xs text-gray-500 italic">
                                                            {{ contact.notes }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 ml-3">
                                                    <button
                                                        @click="startEdit(contact)"
                                                        class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                                                        aria-label="Edit contact"
                                                    >
                                                        <PencilIcon class="h-4 w-4" aria-hidden="true" />
                                                    </button>
                                                    <button
                                                        @click="deleteContact(contact)"
                                                        class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                                        aria-label="Delete contact"
                                                    >
                                                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-else class="text-sm text-gray-500 text-center py-8">
                                            No contact persons yet
                                        </p>

                                        <button
                                            @click="startAdd"
                                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                                        >
                                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                            Add Contact Person
                                        </button>
                                    </div>

                                    <!-- Contact Form -->
                                    <form v-else @submit.prevent="submit" class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">
                                                    Name *
                                                </label>
                                                <input
                                                    v-model="form.name"
                                                    type="text"
                                                    required
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="Full name"
                                                />
                                            </div>

                                            <div class="col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">
                                                    Job Title
                                                </label>
                                                <input
                                                    v-model="form.title"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="e.g., Project Manager"
                                                />
                                            </div>

                                            <div class="col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">
                                                    Email
                                                </label>
                                                <input
                                                    v-model="form.email"
                                                    type="email"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="email@example.com"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">
                                                    Phone
                                                </label>
                                                <input
                                                    v-model="form.phone"
                                                    type="tel"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="+260..."
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">
                                                    Mobile
                                                </label>
                                                <input
                                                    v-model="form.mobile"
                                                    type="tel"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="+260..."
                                                />
                                            </div>

                                            <div class="col-span-2">
                                                <label class="flex items-center gap-2">
                                                    <input
                                                        v-model="form.is_primary"
                                                        type="checkbox"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    />
                                                    <span class="text-sm font-medium text-gray-700">
                                                        Set as primary contact
                                                    </span>
                                                </label>
                                            </div>

                                            <div class="col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">
                                                    Notes
                                                </label>
                                                <textarea
                                                    v-model="form.notes"
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="Additional notes..."
                                                />
                                            </div>
                                        </div>

                                        <div class="flex gap-3 justify-end pt-4">
                                            <button
                                                type="button"
                                                class="px-4 py-2 text-sm font-semibold text-gray-900 bg-white rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                                @click="cancelForm"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                :disabled="form.processing"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 disabled:opacity-50"
                                            >
                                                {{ form.processing ? 'Saving...' : (isEditing ? 'Update Contact' : 'Add Contact') }}
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
