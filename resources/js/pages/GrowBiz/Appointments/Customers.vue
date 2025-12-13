<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    CalendarDaysIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Customer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    initials: string;
    total_bookings: number;
    no_shows: number;
    cancellations: number;
    reliability_score: number;
    last_visit_at: string | null;
}

const props = defineProps<{
    customers: Customer[];
    search: string | null;
}>();

const searchQuery = ref(props.search || '');
const showModal = ref(false);
const isSubmitting = ref(false);

const form = ref({
    name: '',
    email: '',
    phone: '',
    notes: '',
});

const doSearch = () => {
    router.get(route('growbiz.appointments.customers'), {
        search: searchQuery.value || undefined,
    }, { preserveState: true });
};

const createCustomer = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.appointments.customers.store'), form.value, {
        onSuccess: () => {
            showModal.value = false;
            form.value = { name: '', email: '', phone: '', notes: '' };
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const getReliabilityColor = (score: number) => {
    if (score >= 80) return 'text-green-600 bg-green-50';
    if (score >= 60) return 'text-yellow-600 bg-yellow-50';
    return 'text-red-600 bg-red-50';
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return 'Never';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('growbiz.appointments.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back">
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Customers</h1>
                        <p class="text-sm text-gray-500">{{ customers.length }} customers</p>
                    </div>
                </div>
                <button
                    @click="showModal = true"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    <span>Add</span>
                </button>
            </div>

            <!-- Search -->
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search customers..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                    @keyup.enter="doSearch"
                />
            </div>

            <!-- Customers List -->
            <div class="space-y-3">
                <div v-if="customers.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <UserIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No customers found</p>
                    <button @click="showModal = true" class="mt-3 text-emerald-600 font-medium">Add your first customer</button>
                </div>

                <Link
                    v-for="customer in customers"
                    :key="customer.id"
                    :href="route('growbiz.appointments.customers.show', customer.id)"
                    class="block bg-white rounded-xl p-4 border border-gray-100 hover:border-emerald-200 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-semibold text-emerald-600">{{ customer.initials }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 truncate">{{ customer.name }}</h3>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                                    :class="getReliabilityColor(customer.reliability_score)"
                                >
                                    {{ customer.reliability_score }}%
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-gray-500">
                                <span v-if="customer.phone" class="flex items-center gap-1">
                                    <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ customer.phone }}
                                </span>
                                <span v-if="customer.email" class="flex items-center gap-1 truncate">
                                    <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ customer.email }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right text-sm">
                            <p class="font-medium text-gray-900">{{ customer.total_bookings }} visits</p>
                            <p class="text-gray-400">Last: {{ formatDate(customer.last_visit_at) }}</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Add Customer Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50" @click="showModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-to-class="translate-y-full"
            >
                <div v-if="showModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl p-4 safe-area-bottom">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Add Customer</h2>
                        <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="createCustomer" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input v-model="form.name" type="text" required class="w-full border-gray-200 rounded-lg" placeholder="John Doe" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.phone" type="tel" class="w-full border-gray-200 rounded-lg" placeholder="+260..." />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" class="w-full border-gray-200 rounded-lg" placeholder="email@example.com" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full border-gray-200 rounded-lg" placeholder="Any notes about this customer..."></textarea>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Adding...' : 'Add Customer' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
