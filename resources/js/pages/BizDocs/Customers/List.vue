<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    UserGroupIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed, watch } from 'vue';

interface Customer {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
    tpin?: string;
    notes?: string;
}

interface Props {
    customers: Customer[];
    totalCount: number;
}

const props = defineProps<Props>();

const searchQuery  = ref('');
const showModal    = ref(false);
const editingCustomer = ref<Customer | null>(null);

const form = useForm({
    name:    '',
    address: '',
    phone:   '',
    email:   '',
    tpin:    '',
    notes:   '',
});

// Auto-format phone number with +260 prefix for Zambian numbers
const formatPhoneNumber = (value: string): string => {
    if (!value) return '';
    
    // Remove all non-digit characters
    const digits = value.replace(/\D/g, '');
    
    // If starts with 260, add +
    if (digits.startsWith('260')) {
        return '+' + digits;
    }
    
    // If starts with 0 (local format), replace with +260
    if (digits.startsWith('0')) {
        return '+260' + digits.substring(1);
    }
    
    // If starts with 9 or 7 (mobile without 0), add +260
    if (digits.match(/^[97]/)) {
        return '+260' + digits;
    }
    
    // If already has +, keep as is
    if (value.startsWith('+')) {
        return value;
    }
    
    // Otherwise, add +260 prefix
    return digits ? '+260' + digits : '';
};

// Watch phone field and auto-format
import { watch } from 'vue';
watch(() => form.phone, (newValue) => {
    if (newValue && !newValue.startsWith('+')) {
        form.phone = formatPhoneNumber(newValue);
    }
});

const filteredCustomers = computed(() =>
    props.customers.filter(c => {
        const q = searchQuery.value.toLowerCase();
        return (
            c.name.toLowerCase().includes(q) ||
            c.phone?.toLowerCase().includes(q) ||
            c.email?.toLowerCase().includes(q)
        );
    })
);

const initials = (name: string) =>
    name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();

const openModal = (customer?: Customer) => {
    if (customer) {
        editingCustomer.value = customer;
        form.name    = customer.name;
        form.address = customer.address || '';
        form.phone   = customer.phone   || '';
        form.email   = customer.email   || '';
        form.tpin    = customer.tpin    || '';
        form.notes   = customer.notes   || '';
    } else {
        editingCustomer.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value       = false;
    editingCustomer.value = null;
    form.reset();
};

const submit = () => {
    if (editingCustomer.value) {
        form.put(`/bizdocs/customers/${editingCustomer.value.id}`, { onSuccess: closeModal });
    } else {
        form.post('/bizdocs/customers', { onSuccess: closeModal });
    }
};
</script>

<template>
    <Head title="Customers" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-7xl mx-auto">

                <!-- Page header -->
                <div class="flex items-center justify-between mb-7">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                                <span class="w-5 h-px bg-blue-500 inline-block"></span>
                                BizDocs
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 leading-none">Customers</h1>
                        <p class="text-sm text-slate-400 mt-1">{{ totalCount }} total records</p>
                    </div>

                    <button
                        @click="openModal()"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        <PlusIcon class="w-4 h-4" />
                        Add Customer
                    </button>
                </div>

                <!-- Search -->
                <div class="relative mb-5">
                    <MagnifyingGlassIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name, phone, or email…"
                        class="w-full pl-10 pr-4 py-2.5 text-sm text-slate-800 bg-white border border-slate-200 rounded-lg placeholder-slate-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all duration-150 shadow-sm" />
                </div>

                <!-- Customer grid -->
                <div v-if="filteredCustomers.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="customer in filteredCustomers"
                        :key="customer.id"
                        @click="openModal(customer)"
                        class="group bg-white border border-slate-200 rounded-xl p-5 cursor-pointer hover:border-blue-300 hover:shadow-md transition-all duration-150">

                        <!-- Card header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Avatar -->
                                <div class="w-9 h-9 rounded-lg bg-slate-100 group-hover:bg-blue-50 flex items-center justify-center flex-shrink-0 transition-colors">
                                    <span class="text-xs font-bold text-slate-500 group-hover:text-blue-600 transition-colors">
                                        {{ initials(customer.name) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-slate-900 truncate leading-snug">
                                        {{ customer.name }}
                                    </h3>
                                    <p v-if="customer.tpin" class="text-xs text-slate-400 mt-0.5">
                                        TPIN: {{ customer.tpin }}
                                    </p>
                                </div>
                            </div>
                            <!-- Edit hint -->
                            <span class="text-xs text-slate-300 group-hover:text-blue-400 transition-colors flex-shrink-0 ml-2 mt-0.5">
                                Edit →
                            </span>
                        </div>

                        <!-- Contact details -->
                        <div class="space-y-1.5">
                            <div v-if="customer.phone" class="flex items-center gap-2 text-xs text-slate-500">
                                <PhoneIcon class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" />
                                {{ customer.phone }}
                            </div>
                            <div v-if="customer.email" class="flex items-center gap-2 text-xs text-slate-500">
                                <EnvelopeIcon class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" />
                                <span class="truncate">{{ customer.email }}</span>
                            </div>
                            <div v-if="customer.address" class="flex items-start gap-2 text-xs text-slate-500">
                                <MapPinIcon class="w-3.5 h-3.5 text-slate-300 flex-shrink-0 mt-px" />
                                <span class="line-clamp-1">{{ customer.address }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="bg-white border border-slate-200 rounded-xl py-16 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <UserGroupIcon class="w-6 h-6 text-slate-400" />
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">
                        {{ searchQuery ? 'No results found' : 'No customers yet' }}
                    </h3>
                    <p class="text-xs text-slate-400 mb-5">
                        {{ searchQuery ? 'Try a different name, phone, or email' : 'Add your first customer to get started' }}
                    </p>
                    <button
                        v-if="!searchQuery"
                        @click="openModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 transition-all duration-200">
                        <PlusIcon class="w-4 h-4" />
                        Add First Customer
                    </button>
                </div>

            </div>
        </div>

        <!-- ── Modal ── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">

                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-title">

                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                        @click="closeModal" />

                    <!-- Panel -->
                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95">

                        <div
                            v-if="showModal"
                            class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">

                            <!-- Modal header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center">
                                        <UserGroupIcon class="w-4 h-4 text-white" />
                                    </div>
                                    <h3 id="modal-title" class="text-sm font-bold text-slate-900">
                                        {{ editingCustomer ? 'Edit Customer' : 'Add New Customer' }}
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                                    aria-label="Close">
                                    <XMarkIcon class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Modal body -->
                            <form @submit.prevent="submit">
                                <div class="px-6 py-5 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                        <!-- Name (full width) -->
                                        <div class="sm:col-span-2">
                                            <label for="modal-name" class="bizdocs-label">
                                                Customer Name <span class="text-red-400">*</span>
                                            </label>
                                            <input
                                                id="modal-name"
                                                v-model="form.name"
                                                type="text"
                                                required
                                                placeholder="Full name or business name"
                                                class="bizdocs-input"
                                                :class="{ 'bizdocs-input-error': form.errors.name }" />
                                            <p v-if="form.errors.name" class="bizdocs-error">
                                                {{ form.errors.name }}
                                            </p>
                                        </div>

                                        <!-- Phone -->
                                        <div>
                                            <label for="modal-phone" class="bizdocs-label">Phone</label>
                                            <input
                                                id="modal-phone"
                                                v-model="form.phone"
                                                type="tel"
                                                placeholder="+260 97X XXX XXX"
                                                class="bizdocs-input" />
                                        </div>

                                        <!-- Email -->
                                        <div>
                                            <label for="modal-email" class="bizdocs-label">Email</label>
                                            <input
                                                id="modal-email"
                                                v-model="form.email"
                                                type="email"
                                                placeholder="customer@example.com"
                                                class="bizdocs-input" />
                                        </div>

                                        <!-- TPIN -->
                                        <div>
                                            <label for="modal-tpin" class="bizdocs-label">
                                                TPIN
                                                <span class="ml-1 text-xs font-normal text-slate-400">(Tax ID)</span>
                                            </label>
                                            <input
                                                id="modal-tpin"
                                                v-model="form.tpin"
                                                type="text"
                                                placeholder="1234567890"
                                                class="bizdocs-input" />
                                        </div>

                                        <!-- Address -->
                                        <div class="sm:col-span-2">
                                            <label for="modal-address" class="bizdocs-label">Address</label>
                                            <textarea
                                                id="modal-address"
                                                v-model="form.address"
                                                rows="2"
                                                placeholder="Street, City, Province"
                                                class="bizdocs-input resize-none" />
                                        </div>

                                        <!-- Notes -->
                                        <div class="sm:col-span-2">
                                            <label for="modal-notes" class="bizdocs-label">Notes</label>
                                            <textarea
                                                id="modal-notes"
                                                v-model="form.notes"
                                                rows="2"
                                                placeholder="Any additional notes about this customer"
                                                class="bizdocs-input resize-none" />
                                        </div>

                                    </div>
                                </div>

                                <!-- Modal footer -->
                                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 transition-all">
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="group inline-flex items-center gap-2 px-5 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">

                                        <span v-if="!form.processing">
                                            {{ editingCustomer ? 'Update Customer' : 'Add Customer' }}
                                        </span>
                                        <span v-else class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                            </svg>
                                            Saving…
                                        </span>

                                        <svg v-if="!form.processing"
                                            class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

    </AppLayout>
</template>

<style scoped>
.bizdocs-label {
    @apply block text-xs font-semibold text-slate-600 mb-1.5 tracking-wide;
}

.bizdocs-input {
    @apply block w-full px-3.5 py-2.5 text-sm text-slate-800 bg-slate-50
           border border-slate-200 rounded-lg placeholder-slate-300
           focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10
           transition-all duration-150 outline-none;
}

.bizdocs-input-error {
    @apply border-red-300 bg-red-50 focus:border-red-400 focus:ring-red-500/10;
}

.bizdocs-error {
    @apply mt-1.5 text-xs text-red-500;
}
</style>