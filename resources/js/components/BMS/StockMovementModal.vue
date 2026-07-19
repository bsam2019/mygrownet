<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Props {
    show: boolean;
    inventoryId: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'close': [];
}>();

const form = useForm({
    movement_type: 'purchase',
    quantity: 0,
    unit_cost: 0,
    reference_number: '',
    notes: '',
});

const movementTypes = [
    { value: 'purchase', label: 'Purchase', description: 'Stock received from supplier' },
    { value: 'usage', label: 'Usage', description: 'Stock used for job or internal use' },
    { value: 'adjustment', label: 'Adjustment', description: 'Stock count correction' },
    { value: 'return', label: 'Return', description: 'Stock returned from job or customer' },
    { value: 'damage', label: 'Damage', description: 'Stock damaged or lost' },
    { value: 'transfer', label: 'Transfer', description: 'Stock transferred to another location' },
];

const submit = () => {
    form.post(route('cms.inventory.movement', props.inventoryId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const close = () => {
    form.reset();
    emit('close');
};

const isNegativeMovement = (type: string) => {
    return ['usage', 'damage'].includes(type);
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
                                    aria-label="Close stock movement modal"
                                    @click="close"
                                >
                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                </button>
                            </div>

                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <ClockIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                                </div>
                                <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                                        Record Stock Movement
                                    </DialogTitle>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Add or remove stock from inventory
                                    </p>

                                    <form @submit.prevent="submit" class="mt-6 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Movement Type *
                                            </label>
                                            <div class="space-y-2">
                                                <label
                                                    v-for="type in movementTypes"
                                                    :key="type.value"
                                                    class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                                    :class="form.movement_type === type.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                                                >
                                                    <input
                                                        v-model="form.movement_type"
                                                        type="radio"
                                                        :value="type.value"
                                                        class="mt-1 text-blue-600 focus:ring-blue-500"
                                                    />
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">{{ type.label }}</p>
                                                        <p class="text-xs text-gray-500">{{ type.description }}</p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Quantity *
                                            </label>
                                            <input
                                                v-model.number="form.quantity"
                                                type="number"
                                                required
                                                :min="isNegativeMovement(form.movement_type) ? undefined : 0"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                :placeholder="isNegativeMovement(form.movement_type) ? 'Enter negative value (e.g., -10)' : 'Enter positive value'"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">
                                                <span v-if="isNegativeMovement(form.movement_type)">
                                                    Use negative value to reduce stock (e.g., -10)
                                                </span>
                                                <span v-else>
                                                    Use positive value to increase stock
                                                </span>
                                            </p>
                                        </div>

                                        <div v-if="form.movement_type === 'purchase'">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Unit Cost (K)
                                            </label>
                                            <input
                                                v-model.number="form.unit_cost"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="0.00"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">
                                                Will update the item's unit cost
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Reference Number
                                            </label>
                                            <input
                                                v-model="form.reference_number"
                                                type="text"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="e.g., PO-12345, INV-67890"
                                            />
                                        </div>

                                        <div>
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

                                        <div class="flex gap-3 justify-end pt-4">
                                            <button
                                                type="button"
                                                class="px-4 py-2 text-sm font-semibold text-gray-900 bg-white rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                                @click="close"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                :disabled="form.processing"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 disabled:opacity-50"
                                            >
                                                {{ form.processing ? 'Recording...' : 'Record Movement' }}
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
