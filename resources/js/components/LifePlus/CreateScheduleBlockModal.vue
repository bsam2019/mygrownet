<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    XMarkIcon,
    ClockIcon,
    TagIcon,
    CalendarIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    date: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created'): void;
}>();

const form = ref({
    title: '',
    description: '',
    date: props.date,
    start_time: '',
    end_time: '',
    category: 'other' as 'work' | 'personal' | 'health' | 'learning' | 'social' | 'other',
    is_recurring: false,
    recurrence_pattern: 'daily' as 'daily' | 'weekly' | 'weekdays' | 'weekends',
    recurrence_end_date: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const categories = [
    { value: 'work', label: 'Work', color: '#3b82f6', icon: '💼' },
    { value: 'personal', label: 'Personal', color: '#8b5cf6', icon: '🏠' },
    { value: 'health', label: 'Health', color: '#10b981', icon: '💪' },
    { value: 'learning', label: 'Learning', color: '#f59e0b', icon: '📚' },
    { value: 'social', label: 'Social', color: '#ec4899', icon: '👥' },
    { value: 'other', label: 'Other', color: '#6b7280', icon: '📌' },
];

const selectedCategory = computed(() => {
    return categories.find(c => c.value === form.value.category) || categories[5];
});

const recurrenceOptions = [
    { value: 'daily', label: 'Every day' },
    { value: 'weekly', label: 'Every week' },
    { value: 'weekdays', label: 'Weekdays only' },
    { value: 'weekends', label: 'Weekends only' },
];

const handleSubmit = () => {
    errors.value = {};
    isSubmitting.value = true;

    router.post(route('lifeplus.schedule.store'), form.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('created');
        },
        onError: (err) => {
            errors.value = err as Record<string, string>;
            isSubmitting.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
                @click="emit('close')"
            ></div>

            <!-- Modal -->
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-xl transition-all w-full max-w-lg">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900">New Schedule Block</h2>
                    <button
                        @click="emit('close')"
                        class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                        aria-label="Close modal"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                            Title *
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., Team Meeting"
                        />
                        <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="2"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Add details..."
                        ></textarea>
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                            <CalendarIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                            Date *
                        </label>
                        <input
                            id="date"
                            v-model="form.date"
                            type="date"
                            required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                        <p v-if="errors.date" class="mt-1 text-sm text-red-600">{{ errors.date }}</p>
                    </div>

                    <!-- Time Range -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                <ClockIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                                Start Time *
                            </label>
                            <input
                                id="start_time"
                                v-model="form.start_time"
                                type="time"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p v-if="errors.start_time" class="mt-1 text-sm text-red-600">{{ errors.start_time }}</p>
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                End Time *
                            </label>
                            <input
                                id="end_time"
                                v-model="form.end_time"
                                type="time"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p v-if="errors.end_time" class="mt-1 text-sm text-red-600">{{ errors.end_time }}</p>
                        </div>
                    </div>
                    <p v-if="errors.time" class="text-sm text-red-600">{{ errors.time }}</p>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <TagIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                            Category *
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="category in categories"
                                :key="category.value"
                                type="button"
                                @click="form.category = category.value as any"
                                class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all"
                                :class="form.category === category.value 
                                    ? 'border-current shadow-md' 
                                    : 'border-gray-200 hover:border-gray-300'"
                                :style="{ 
                                    color: form.category === category.value ? category.color : '#6b7280',
                                    backgroundColor: form.category === category.value ? `${category.color}10` : 'transparent'
                                }"
                            >
                                <span class="text-2xl">{{ category.icon }}</span>
                                <span class="text-xs font-semibold">{{ category.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Recurring -->
                    <div class="border-t border-gray-100 pt-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                v-model="form.is_recurring"
                                type="checkbox"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <div class="flex items-center gap-2">
                                <ArrowPathIcon class="h-4 w-4 text-gray-600" aria-hidden="true" />
                                <span class="text-sm font-semibold text-gray-700">Make this recurring</span>
                            </div>
                        </label>

                        <div v-if="form.is_recurring" class="mt-4 space-y-3 pl-8">
                            <div>
                                <label for="recurrence_pattern" class="block text-sm font-medium text-gray-700 mb-2">
                                    Repeat
                                </label>
                                <select
                                    id="recurrence_pattern"
                                    v-model="form.recurrence_pattern"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option v-for="option in recurrenceOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="recurrence_end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Until
                                </label>
                                <input
                                    id="recurrence_end_date"
                                    v-model="form.recurrence_end_date"
                                    type="date"
                                    :min="form.date"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="emit('close')"
                            class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Creating...' : 'Create Block' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
