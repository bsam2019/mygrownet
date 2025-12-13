<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ClockIcon,
    CurrencyDollarIcon,
    XMarkIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

interface Service {
    id: number;
    name: string;
    description: string | null;
    duration_minutes: number;
    buffer_minutes: number;
    formatted_duration: string;
    price: number;
    formatted_price: string;
    category: string | null;
    color: string;
    is_active: boolean;
    allow_online_booking: boolean;
}

const props = defineProps<{
    services: Service[];
    categories: string[];
    providers: { id: number; name: string }[];
}>();

const showModal = ref(false);
const editingService = ref<Service | null>(null);
const isSubmitting = ref(false);

const form = ref({
    name: '',
    description: '',
    duration_minutes: 60,
    buffer_minutes: 0,
    price: 0,
    category: '',
    color: '#3b82f6',
    is_active: true,
    allow_online_booking: true,
});

const colorOptions = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
];

const openCreateModal = () => {
    editingService.value = null;
    form.value = {
        name: '',
        description: '',
        duration_minutes: 60,
        buffer_minutes: 0,
        price: 0,
        category: '',
        color: '#3b82f6',
        is_active: true,
        allow_online_booking: true,
    };
    showModal.value = true;
};

const openEditModal = (service: Service) => {
    editingService.value = service;
    form.value = {
        name: service.name,
        description: service.description || '',
        duration_minutes: service.duration_minutes,
        buffer_minutes: service.buffer_minutes,
        price: service.price,
        category: service.category || '',
        color: service.color,
        is_active: service.is_active,
        allow_online_booking: service.allow_online_booking,
    };
    showModal.value = true;
};

const saveService = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    const url = editingService.value
        ? route('growbiz.appointments.services.update', editingService.value.id)
        : route('growbiz.appointments.services.store');

    const method = editingService.value ? 'put' : 'post';

    router[method](url, form.value, {
        onSuccess: () => {
            showModal.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const deleteService = (service: Service) => {
    if (!confirm(`Delete "${service.name}"? This cannot be undone.`)) return;

    router.delete(route('growbiz.appointments.services.destroy', service.id));
};

const toggleActive = (service: Service) => {
    router.put(route('growbiz.appointments.services.update', service.id), {
        is_active: !service.is_active,
    }, { preserveScroll: true });
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
                        <h1 class="text-xl font-bold text-gray-900">Services</h1>
                        <p class="text-sm text-gray-500">{{ services.length }} services</p>
                    </div>
                </div>
                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    <span>Add Service</span>
                </button>
            </div>

            <!-- Services List -->
            <div class="space-y-3">
                <div v-if="services.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <CurrencyDollarIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No services yet</p>
                    <button @click="openCreateModal" class="mt-3 text-emerald-600 font-medium">Add your first service</button>
                </div>

                <div
                    v-for="service in services"
                    :key="service.id"
                    class="bg-white rounded-xl p-4 border border-gray-100"
                    :class="{ 'opacity-60': !service.is_active }"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-3 h-12 rounded-full flex-shrink-0"
                                :style="{ backgroundColor: service.color }"
                            ></div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900">{{ service.name }}</h3>
                                    <span v-if="!service.is_active" class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded-full">
                                        Inactive
                                    </span>
                                    <span v-if="service.category" class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs rounded-full">
                                        {{ service.category }}
                                    </span>
                                </div>
                                <p v-if="service.description" class="text-sm text-gray-500 mt-1 line-clamp-2">
                                    {{ service.description }}
                                </p>
                                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ service.formatted_duration }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <CurrencyDollarIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ service.formatted_price }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                @click="openEditModal(service)"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"
                                aria-label="Edit service"
                            >
                                <PencilIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                            <button
                                @click="deleteService(service)"
                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                aria-label="Delete service"
                            >
                                <TrashIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Modal -->
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
                        <h2 class="text-lg font-semibold">{{ editingService ? 'Edit Service' : 'New Service' }}</h2>
                        <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="saveService" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Name *</label>
                            <input v-model="form.name" type="text" required class="w-full border-gray-200 rounded-lg" placeholder="e.g., Haircut" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="2" class="w-full border-gray-200 rounded-lg" placeholder="Brief description..."></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes) *</label>
                                <input v-model.number="form.duration_minutes" type="number" min="5" max="480" required class="w-full border-gray-200 rounded-lg" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buffer Time</label>
                                <input v-model.number="form.buffer_minutes" type="number" min="0" max="60" class="w-full border-gray-200 rounded-lg" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price (ZMW) *</label>
                                <input v-model.number="form.price" type="number" min="0" step="0.01" required class="w-full border-gray-200 rounded-lg" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <input v-model="form.category" type="text" list="categories" class="w-full border-gray-200 rounded-lg" placeholder="e.g., Hair" />
                                <datalist id="categories">
                                    <option v-for="cat in categories" :key="cat" :value="cat" />
                                </datalist>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="color in colorOptions"
                                    :key="color"
                                    type="button"
                                    @click="form.color = color"
                                    class="w-8 h-8 rounded-full border-2 transition-all"
                                    :style="{ backgroundColor: color }"
                                    :class="form.color === color ? 'border-gray-900 scale-110' : 'border-transparent'"
                                    :aria-label="`Select color ${color}`"
                                ></button>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.allow_online_booking" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                <span class="text-sm text-gray-700">Allow online booking</span>
                            </label>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Saving...' : (editingService ? 'Update Service' : 'Create Service') }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
