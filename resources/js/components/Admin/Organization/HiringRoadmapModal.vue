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
                                            {{ item ? 'Edit Hiring Plan' : 'Add to Hiring Roadmap' }}
                                        </DialogTitle>
                                    </div>

                                    <div class="mt-4 space-y-4">
                                        <!-- Position -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Position</label>
                                            <select
                                                v-model="form.position_id"
                                                :disabled="!!item"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                required
                                            >
                                                <option value="">Select position...</option>
                                                <option v-for="position in positions" :key="position.id" :value="position.id">
                                                    {{ position.title }} ({{ position.department.name }})
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Phase & Priority -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Phase</label>
                                                <select
                                                    v-model="form.phase"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                    required
                                                >
                                                    <option value="phase_1">Phase 1 (0-6 months)</option>
                                                    <option value="phase_2">Phase 2 (6-18 months)</option>
                                                    <option value="phase_3">Phase 3 (18+ months)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Priority</label>
                                                <select
                                                    v-model="form.priority"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                    required
                                                >
                                                    <option value="critical">Critical</option>
                                                    <option value="high">High</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="low">Low</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Target Date & Headcount -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Target Hire Date</label>
                                                <input
                                                    v-model="form.target_hire_date"
                                                    type="date"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Headcount</label>
                                                <input
                                                    v-model="form.headcount"
                                                    type="number"
                                                    min="1"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                    required
                                                />
                                            </div>
                                        </div>

                                        <!-- Status (only for edit) -->
                                        <div v-if="item">
                                            <label class="block text-sm font-medium text-gray-700">Status</label>
                                            <select
                                                v-model="form.status"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                required
                                            >
                                                <option value="planned">Planned</option>
                                                <option value="in_progress">In Progress</option>
                                                <option value="hired">Hired</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>

                                        <!-- Budget -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Budget Allocated (K)</label>
                                            <input
                                                v-model="form.budget_allocated"
                                                type="number"
                                                step="0.01"
                                                placeholder="e.g., 20000"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            />
                                        </div>

                                        <!-- Notes -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                                            <textarea
                                                v-model="form.notes"
                                                rows="3"
                                                placeholder="Additional information about this hiring plan..."
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            ></textarea>
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
    item: Object,
    positions: Array
});

const emit = defineEmits(['close', 'saved']);

const processing = ref(false);
const form = ref({
    position_id: '',
    phase: 'phase_1',
    target_hire_date: '',
    priority: 'medium',
    headcount: 1,
    status: 'planned',
    budget_allocated: '',
    notes: ''
});

watch(() => props.item, (newItem) => {
    if (newItem) {
        form.value = {
            position_id: newItem.position_id,
            phase: newItem.phase,
            target_hire_date: newItem.target_hire_date || '',
            priority: newItem.priority,
            headcount: newItem.headcount,
            status: newItem.status,
            budget_allocated: newItem.budget_allocated || '',
            notes: newItem.notes || ''
        };
    } else {
        form.value = {
            position_id: '',
            phase: 'phase_1',
            target_hire_date: '',
            priority: 'medium',
            headcount: 1,
            status: 'planned',
            budget_allocated: '',
            notes: ''
        };
    }
}, { immediate: true });

const submit = () => {
    processing.value = true;

    const url = props.item 
        ? route('admin.organization.hiring.update', props.item.id)
        : route('admin.organization.hiring.store');

    const method = props.item ? 'patch' : 'post';

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
