<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    CalendarDaysIcon,
    MapPinIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Event {
    id: number;
    title: string;
    content: string | null;
    excerpt: string | null;
    location: string | null;
    event_date: string | null;
    formatted_event_date: string | null;
    poster: { id: number; name: string } | null;
    posted_at: string;
}

const props = defineProps<{
    events: Event[];
}>();

const showAddModal = ref(false);

const form = useForm({
    type: 'event',
    title: '',
    content: '',
    location: '',
    event_date: '',
});

const submitEvent = () => {
    form.post(route('lifeplus.community.posts.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
            form.type = 'event';
        },
    });
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.community.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to community"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">Local Events</h1>
            <button 
                @click="showAddModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-purple-500 text-white rounded-xl font-medium hover:bg-purple-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Add
            </button>
        </div>

        <!-- Events List -->
        <div class="space-y-3">
            <div v-if="events.length === 0" class="text-center py-12">
                <CalendarDaysIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No events yet</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-3 text-purple-600 font-medium"
                >
                    Add the first event
                </button>
            </div>

            <Link 
                v-for="event in events" 
                :key="event.id"
                :href="route('lifeplus.community.posts.show', event.id)"
                class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex flex-col items-center justify-center flex-shrink-0">
                        <CalendarDaysIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900">{{ event.title }}</h3>
                        <p v-if="event.excerpt" class="text-sm text-gray-600 mt-1 line-clamp-2">
                            {{ event.excerpt }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-500">
                            <span v-if="event.formatted_event_date" class="flex items-center gap-1 text-purple-600 font-medium">
                                <ClockIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ event.formatted_event_date }}
                            </span>
                            <span v-if="event.location" class="flex items-center gap-1">
                                <MapPinIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ event.location }}
                            </span>
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Add Event Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center">
                    <div 
                        class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom max-h-[90vh] overflow-y-auto"
                        @click.stop
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Add Event</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitEvent" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                                <input 
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="Event name"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea 
                                    v-model="form.content"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="Event details..."
                                ></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date & Time</label>
                                <input 
                                    v-model="form.event_date"
                                    type="datetime-local"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input 
                                    v-model="form.location"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="e.g., Community Hall, Chilenje"
                                />
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-purple-500 text-white rounded-xl font-medium hover:bg-purple-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Event' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
