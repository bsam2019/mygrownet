<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    UserGroupIcon,
    PencilIcon,
    TrashIcon,
    XMarkIcon,
    EnvelopeIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';

interface Service {
    id: number;
    name: string;
    color: string;
}

interface Provider {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    bio: string | null;
    color: string;
    initials: string;
    is_active: boolean;
    accept_online_bookings: boolean;
    services: Service[];
    appointments_count: number;
}

const props = defineProps<{
    providers: Provider[];
    services: Service[];
}>();

const showModal = ref(false);
const editingProvider = ref<Provider | null>(null);
const isSubmitting = ref(false);

const form = ref({
    name: '',
    email: '',
    phone: '',
    bio: '',
    color: '#3b82f6',
    is_active: true,
    accept_online_bookings: true,
    service_ids: [] as number[],
});

const colors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
];

const openCreateModal = () => {
    editingProvider.value = null;
    form.value = {
        name: '',
        email: '',
        phone: '',
        bio: '',
        color: '#3b82f6',
        is_active: true,
        accept_online_bookings: true,
        service_ids: [],
    };
    showModal.value = true;
};

const openEditModal = (provider: Provider) => {
    editingProvider.value = provider;
    form.value = {
        name: provider.name,
        email: provider.email || '',
        phone: provider.phone || '',
        bio: provider.bio || '',
        color: provider.color || '#3b82f6',
        is_active: provider.is_active,
        accept_online_bookings: provider.accept_online_bookings,
        service_ids: provider.services.map(s => s.id),
    };
    showModal.value = true;
};

const submitForm = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    const url = editingProvider.value
        ? route('growbiz.appointments.providers.update', editingProvider.value.id)
        : route('growbiz.appointments.providers.store');

    const method = editingProvider.value ? 'put' : 'post';

    router[method](url, form.value, {
        onSuccess: () => {
            showModal.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const deleteProvider = (provider: Provider) => {
    if (!confirm(`Are you sure you want to remove ${provider.name}?`)) return;
    
    router.delete(route('growbiz.appointments.providers.destroy', provider.id));
};

const toggleService = (serviceId: number) => {
    const index = form.value.service_ids.indexOf(serviceId);
    if (index === -1) {
        form.value.service_ids.push(serviceId);
    } else {
        form.value.service_ids.splice(index, 1);
    }
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('growbiz.appointments.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back to appointments">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Staff Members</h1>
                        <p class="text-sm text-gray-500">{{ providers.length }} team members</p>
                    </div>
                </div>
                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="hidden sm:inline">Add Staff</span>
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="providers.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                <UserGroupIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                <p class="text-gray-500">No staff members added yet</p>
                <button @click="openCreateModal" class="mt-3 text-emerald-600 font-medium">Add your first staff member</button>
            </div>

            <!-- Providers List -->
            <div class="space-y-3">
                <div
                    v-for="provider in providers"
                    :key="provider.id"
                    class="bg-white rounded-xl p-4 border border-gray-100"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold"
                                :style="{ backgroundColor: provider.color }"
                            >
                                {{ provider.initials }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900">{{ provider.name }}</span>
                                    <span
                                        v-if="!provider.is_active"
                                        class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs"
                                    >
                                        Inactive
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                    <span v-if="provider.email" class="flex items-center gap-1">
                                        <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ provider.email }}
                                    </span>
                                    <span v-if="provider.phone" class="flex items-center gap-1">
                                        <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ provider.phone }}
                                    </span>
                                </div>
                                <div v-if="provider.services.length > 0" class="flex flex-wrap gap-1 mt-2">
                                    <span
                                        v-for="service in provider.services"
                                        :key="service.id"
                                        class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs"
                                    >
                                        {{ service.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-400">{{ provider.appointments_count }} bookings</span>
                            <button
                                @click="openEditModal(provider)"
                                class="p-2 hover:bg-gray-100 rounded-lg"
                                aria-label="Edit staff member"
                            >
                                <PencilIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                            </button>
                            <button
                                @click="deleteProvider(provider)"
                                class="p-2 hover:bg-red-50 rounded-lg"
                                aria-label="Remove staff member"
                            >
                                <TrashIcon class="h-4 w-4 text-red-500" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50" @click="showModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div v-if="showModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl max-h-[90vh] overflow-y-auto safe-area-bottom">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ editingProvider ? 'Edit Staff' : 'Add Staff Member' }}</h2>
                        <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input v-model="form.name" type="text" required class="w-full border-gray-200 rounded-lg" placeholder="John Doe" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="form.email" type="email" class="w-full border-gray-200 rounded-lg" placeholder="john@example.com" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input v-model="form.phone" type="tel" class="w-full border-gray-200 rounded-lg" placeholder="+260..." />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                            <textarea v-model="form.bio" rows="2" class="w-full border-gray-200 rounded-lg" placeholder="Brief description..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="color in colors"
                                    :key="color"
                                    type="button"
                                    @click="form.color = color"
                                    class="w-8 h-8 rounded-full border-2 transition-all"
                                    :class="form.color === color ? 'border-gray-900 scale-110' : 'border-transparent'"
                                    :style="{ backgroundColor: color }"
                                    :aria-label="`Select color ${color}`"
                                ></button>
                            </div>
                        </div>
                        <div v-if="services.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Services Provided</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="service in services"
                                    :key="service.id"
                                    type="button"
                                    @click="toggleService(service.id)"
                                    class="px-3 py-1.5 rounded-lg text-sm border transition-colors"
                                    :class="form.service_ids.includes(service.id) 
                                        ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                                        : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                                >
                                    {{ service.name }}
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600" />
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="form.accept_online_bookings" type="checkbox" class="rounded border-gray-300 text-emerald-600" />
                                <span class="text-sm text-gray-700">Accept online bookings</span>
                            </label>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Saving...' : (editingProvider ? 'Update Staff' : 'Add Staff Member') }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
