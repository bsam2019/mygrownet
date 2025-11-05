<template>
    <TransitionRoot as="template" :show="show">
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
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                            <form @submit.prevent="submit">
                                <div>
                                    <div class="text-center sm:text-left">
                                        <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                                            {{ kpi ? 'Edit KPI' : 'Add New KPI' }}
                                        </DialogTitle>
                                    </div>

                                    <div class="mt-4 space-y-4">
                                        <!-- Position -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Position</label>
                                            <select
                                                v-model="form.position_id"
                                                :disabled="!!kpi"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                required
                                            >
                                                <option value="">Select position...</option>
                                                <option v-for="position in positions" :key="position.id" :value="position.id">
                                                    {{ position.title }} ({{ position.department.name }})
                                                </option>
                                            </select>
                                        </div>

                                        <!-- KPI Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">KPI Name</label>
                                            <input
                                                v-model="form.kpi_name"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                required
                                            />
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Description</label>
                                            <textarea
                                                v-model="form.kpi_description"
                                                rows="3"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            ></textarea>
                                        </div>

                                        <!-- Target Value & Unit -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Target Value</label>
                                                <input
                                                    v-model="form.target_value"
                                                    type="number"
                                                    step="0.01"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Unit</label>
                                                <input
                                                    v-model="form.measurement_unit"
                                                    type="text"
                                                    placeholder="e.g., %, hours, count"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                />
                                            </div>
                                        </div>

                                        <!-- Frequency -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Measurement Frequency</label>
                                            <select
                                                v-model="form.measurement_frequency"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                required
                                            >
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="annual">Annual</option>
                                            </select>
                                        </div>

                                        <!-- Active Status (only for edit) -->
                                        <div v-if="kpi" class="flex items-center">
                                            <input
                                                v-model="form.is_active"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <label class="ml-2 block text-sm text-gray-900">Active</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                    <button
                                        type="submit"
                                        :disabled="processing"
                                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:col-start-2 disabled:opacity-50"
                                    >
                                        {{ processing ? 'Saving...' : 'Save' }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="$emit('close')"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
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

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    show: Boolean,
    kpi: Object,
    positions: Array
});

const emit = defineEmits(['close', 'saved']);

const processing = ref(false);
const form = ref({
    position_id: '',
    kpi_name: '',
    kpi_description: '',
    target_value: '',
    measurement_unit: '',
    measurement_frequency: 'monthly',
    is_active: true
});

watch(() => props.kpi, (newKpi) => {
    if (newKpi) {
        form.value = {
            position_id: newKpi.position_id,
            kpi_name: newKpi.kpi_name,
            kpi_description: newKpi.kpi_description || '',
            target_value: newKpi.target_value || '',
            measurement_unit: newKpi.measurement_unit || '',
            measurement_frequency: newKpi.measurement_frequency,
            is_active: newKpi.is_active
        };
    } else {
        form.value = {
            position_id: '',
            kpi_name: '',
            kpi_description: '',
            target_value: '',
            measurement_unit: '',
            measurement_frequency: 'monthly',
            is_active: true
        };
    }
}, { immediate: true });

const submit = () => {
    processing.value = true;

    const url = props.kpi 
        ? route('admin.organization.kpis.update', props.kpi.id)
        : route('admin.organization.kpis.store');

    const method = props.kpi ? 'patch' : 'post';

    router[method](url, form.value, {
        onSuccess: () => {
            emit('saved');
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
};
</script>
