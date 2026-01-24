<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-end md:items-center md:justify-center"
                @click.self="close"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

                <!-- Modal -->
                <div class="relative w-full md:max-w-lg bg-white rounded-t-3xl md:rounded-2xl shadow-2xl p-6 pb-8 md:pb-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Check-In for {{ personName }}</h3>
                        <button
                            @click="close"
                            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                            aria-label="Close check-in modal"
                        >
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Status Options -->
                    <div class="space-y-3 mb-6">
                        <button
                            v-for="status in statusOptions"
                            :key="status.value"
                            @click="selectStatus(status.value)"
                            class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-all hover:shadow-md"
                            :class="[
                                form.status === status.value
                                    ? `border-${status.color}-500 bg-${status.color}-50`
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <span class="text-4xl">{{ status.emoji }}</span>
                            <div class="flex-1 text-left">
                                <div class="font-semibold text-gray-900">{{ status.label }}</div>
                            </div>
                            <div
                                v-if="form.status === status.value"
                                class="flex-shrink-0"
                            >
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </div>

                    <!-- Optional Note -->
                    <div class="mb-6">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-2">
                            Add a note (optional)
                        </label>
                        <textarea
                            id="note"
                            v-model="form.note"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="How are you feeling? Any additional details..."
                        ></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button
                            @click="close"
                            type="button"
                            class="flex-1 px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submit"
                            :disabled="!form.status || form.processing"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg font-medium hover:from-purple-600 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                        >
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>Submit Check-In</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
    personName: string;
    familySlug: string;
    personSlug: string;
}>();

const emit = defineEmits<{
    close: [];
}>();

const statusOptions = [
    { value: 'well', label: 'I am well', emoji: '😊', color: 'green' },
    { value: 'unwell', label: 'I am not feeling well', emoji: '😐', color: 'amber' },
    { value: 'need_assistance', label: 'I need assistance', emoji: '🆘', color: 'red' },
];

const form = reactive({
    status: '',
    note: '',
    processing: false,
});

const selectStatus = (status: string) => {
    form.status = status;
};

const submit = () => {
    if (!form.status) return;

    form.processing = true;

    router.post(
        route('ubumi.families.persons.check-ins.store', {
            family: props.familySlug,
            person: props.personSlug,
        }),
        {
            status: form.status,
            note: form.note || null,
        },
        {
            onSuccess: () => {
                form.status = '';
                form.note = '';
                emit('close');
            },
            onFinish: () => {
                form.processing = false;
            },
        }
    );
};

const close = () => {
    if (!form.processing) {
        emit('close');
    }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-active > div:last-child,
.modal-leave-active > div:last-child {
    transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div:last-child {
    transform: translateY(100%);
}

.modal-leave-to > div:last-child {
    transform: translateY(100%);
}

@media (min-width: 768px) {
    .modal-enter-from > div:last-child,
    .modal-leave-to > div:last-child {
        transform: translateY(0) scale(0.95);
    }
}
</style>
