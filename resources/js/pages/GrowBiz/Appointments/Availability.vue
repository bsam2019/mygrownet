<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    TrashIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Schedule {
    [key: number]: {
        id?: number;
        day_of_week: number;
        day_name: string;
        start_time: string;
        end_time: string;
        is_available: boolean;
        formatted_hours: string;
    };
}

interface Exception {
    id: number;
    date: string;
    type: string;
    type_label: string;
    start_time: string | null;
    end_time: string | null;
    reason: string | null;
}

interface Provider {
    id: number;
    name: string;
}

const props = defineProps<{
    schedule: Schedule;
    providers: Provider[];
    exceptions: Exception[];
}>();

const selectedProvider = ref<number | null>(null);
const showExceptionModal = ref(false);
const isSubmitting = ref(false);

const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const scheduleForm = ref<{ [key: number]: { start_time: string; end_time: string; is_available: boolean } }>({});

// Initialize schedule form
const initScheduleForm = () => {
    for (let i = 0; i < 7; i++) {
        const existing = props.schedule[i];
        scheduleForm.value[i] = {
            start_time: existing?.start_time?.substring(0, 5) || '09:00',
            end_time: existing?.end_time?.substring(0, 5) || '17:00',
            is_available: existing?.is_available ?? (i >= 1 && i <= 5), // Mon-Fri default
        };
    }
};
initScheduleForm();

const exceptionForm = ref({
    date: new Date().toISOString().split('T')[0],
    type: 'closed' as 'closed' | 'modified_hours' | 'extra_availability',
    start_time: '09:00',
    end_time: '17:00',
    reason: '',
});

const saveSchedule = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.appointments.availability.schedule'), {
        provider_id: selectedProvider.value,
        schedule: scheduleForm.value,
    }, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const addException = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.appointments.availability.exceptions.store'), {
        provider_id: selectedProvider.value,
        ...exceptionForm.value,
    }, {
        onSuccess: () => {
            showExceptionModal.value = false;
            exceptionForm.value = {
                date: new Date().toISOString().split('T')[0],
                type: 'closed',
                start_time: '09:00',
                end_time: '17:00',
                reason: '',
            };
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const deleteException = (id: number) => {
    if (!confirm('Remove this exception?')) return;
    router.delete(route('growbiz.appointments.availability.exceptions.destroy', id));
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
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
                        <h1 class="text-xl font-bold text-gray-900">Availability</h1>
                        <p class="text-sm text-gray-500">Set your business hours</p>
                    </div>
                </div>
            </div>

            <!-- Provider Selector -->
            <div v-if="providers.length > 0" class="bg-white rounded-xl p-4 border border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule For</label>
                <select v-model="selectedProvider" class="w-full border-gray-200 rounded-lg" @change="initScheduleForm">
                    <option :value="null">Business (Default)</option>
                    <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>

            <!-- Weekly Schedule -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Weekly Schedule</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="(day, index) in dayNames"
                        :key="index"
                        class="p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        v-model="scheduleForm[index].is_available"
                                        type="checkbox"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                                <span class="font-medium text-gray-900" :class="{ 'text-gray-400': !scheduleForm[index].is_available }">
                                    {{ day }}
                                </span>
                            </div>
                            <div v-if="scheduleForm[index].is_available" class="flex items-center gap-2">
                                <input
                                    v-model="scheduleForm[index].start_time"
                                    type="time"
                                    class="text-sm border-gray-200 rounded-lg w-28"
                                />
                                <span class="text-gray-400">to</span>
                                <input
                                    v-model="scheduleForm[index].end_time"
                                    type="time"
                                    class="text-sm border-gray-200 rounded-lg w-28"
                                />
                            </div>
                            <span v-else class="text-sm text-gray-400">Closed</span>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    <button
                        @click="saveSchedule"
                        :disabled="isSubmitting"
                        class="w-full py-2.5 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Saving...' : 'Save Schedule' }}
                    </button>
                </div>
            </div>

            <!-- Exceptions -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Special Dates</h2>
                    <button
                        @click="showExceptionModal = true"
                        class="flex items-center gap-1 px-3 py-1.5 text-sm text-emerald-600 hover:bg-emerald-50 rounded-lg"
                    >
                        <PlusIcon class="h-4 w-4" aria-hidden="true" />
                        Add
                    </button>
                </div>
                <div v-if="exceptions.length === 0" class="p-8 text-center text-gray-500">
                    <ClockIcon class="h-8 w-8 mx-auto text-gray-300 mb-2" aria-hidden="true" />
                    <p>No special dates set</p>
                    <p class="text-sm">Add holidays or modified hours</p>
                </div>
                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="exception in exceptions"
                        :key="exception.id"
                        class="p-4 flex items-center justify-between"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ formatDate(exception.date) }}</p>
                            <p class="text-sm text-gray-500">
                                <span
                                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium mr-2"
                                    :class="{
                                        'bg-red-100 text-red-700': exception.type === 'closed',
                                        'bg-yellow-100 text-yellow-700': exception.type === 'modified_hours',
                                        'bg-green-100 text-green-700': exception.type === 'extra_availability',
                                    }"
                                >
                                    {{ exception.type_label }}
                                </span>
                                <span v-if="exception.start_time && exception.end_time">
                                    {{ exception.start_time }} - {{ exception.end_time }}
                                </span>
                                <span v-if="exception.reason" class="text-gray-400">· {{ exception.reason }}</span>
                            </p>
                        </div>
                        <button
                            @click="deleteException(exception.id)"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
                            aria-label="Delete exception"
                        >
                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exception Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="showExceptionModal" class="fixed inset-0 z-50 bg-black/50" @click="showExceptionModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-to-class="translate-y-full"
            >
                <div v-if="showExceptionModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl p-4 safe-area-bottom">
                    <h2 class="text-lg font-semibold mb-4">Add Special Date</h2>
                    <form @submit.prevent="addException" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input v-model="exceptionForm.date" type="date" required class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select v-model="exceptionForm.type" class="w-full border-gray-200 rounded-lg">
                                <option value="closed">Closed</option>
                                <option value="modified_hours">Modified Hours</option>
                                <option value="extra_availability">Extra Availability</option>
                            </select>
                        </div>
                        <div v-if="exceptionForm.type !== 'closed'" class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <input v-model="exceptionForm.start_time" type="time" class="w-full border-gray-200 rounded-lg" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input v-model="exceptionForm.end_time" type="time" class="w-full border-gray-200 rounded-lg" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                            <input v-model="exceptionForm.reason" type="text" class="w-full border-gray-200 rounded-lg" placeholder="e.g., Public Holiday" />
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showExceptionModal = false" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmitting" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl disabled:opacity-50">
                                {{ isSubmitting ? 'Adding...' : 'Add Exception' }}
                            </button>
                        </div>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
