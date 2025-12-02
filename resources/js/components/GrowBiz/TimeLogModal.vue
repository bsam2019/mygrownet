<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
                <!-- Backdrop -->
                <div 
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="$emit('close')"
                />
                
                <!-- Modal -->
                <div class="relative w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl transform transition-all">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Log Time</h3>
                        <button 
                            @click="$emit('close')"
                            class="p-2 rounded-full hover:bg-gray-100 transition-colors"
                            aria-label="Close modal"
                        >
                            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Content -->
                    <form @submit.prevent="submit" class="p-4 space-y-4">
                        <!-- Quick Time Buttons -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Select</label>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="time in quickTimes" 
                                    :key="time"
                                    type="button"
                                    @click="hours = time"
                                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                                    :class="hours === time 
                                        ? 'bg-blue-100 border-blue-500 text-blue-700' 
                                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                                >
                                    {{ formatTime(time) }}
                                </button>
                            </div>
                        </div>

                        <!-- Custom Hours Input -->
                        <div>
                            <label for="hours" class="block text-sm font-medium text-gray-700 mb-1">
                                Hours Worked
                            </label>
                            <input
                                id="hours"
                                v-model.number="hours"
                                type="number"
                                step="0.25"
                                min="0.25"
                                max="24"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter hours"
                                required
                            />
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Notes (optional)
                            </label>
                            <textarea
                                id="notes"
                                v-model="notes"
                                rows="2"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                placeholder="What did you work on?"
                            />
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="!hours || hours <= 0 || loading"
                            class="w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="loading">Logging...</span>
                            <span v-else>Log {{ hours ? formatTime(hours) : 'Time' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    submit: [data: { hours: number; notes: string }];
}>();

const hours = ref<number>(1);
const notes = ref('');
const loading = ref(false);

const quickTimes = [0.25, 0.5, 1, 2, 4, 8];

const formatTime = (h: number): string => {
    if (h < 1) return `${h * 60}m`;
    if (h === 1) return '1 hour';
    return `${h} hours`;
};

const submit = () => {
    if (!hours.value || hours.value <= 0) return;
    
    loading.value = true;
    emit('submit', { hours: hours.value, notes: notes.value });
};

// Reset form when modal closes
watch(() => props.show, (newVal) => {
    if (!newVal) {
        hours.value = 1;
        notes.value = '';
        loading.value = false;
    }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .relative {
    transform: translateY(100%);
}

@media (min-width: 640px) {
    .modal-enter-from .relative {
        transform: scale(0.95);
    }
}
</style>
