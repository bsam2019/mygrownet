<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    CalendarDaysIcon,
    PlusIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ClockIcon,
    MapPinIcon,
    VideoCameraIcon,
} from '@heroicons/vue/24/outline';

interface CalendarEvent {
    id: number;
    title: string;
    description: string | null;
    type: string;
    start_time: string;
    end_time: string;
    is_all_day: boolean;
    location: string | null;
    meeting_link: string | null;
}

interface Props {
    summary: {
        today_count: number;
        week_count: number;
        upcoming: CalendarEvent[];
    };
}

const props = defineProps<Props>();

const currentDate = ref(new Date());
const selectedDate = ref<Date | null>(null);
const events = ref<CalendarEvent[]>([]);
const showEventModal = ref(false);

const currentMonth = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days = [];

    // Add empty slots for days before the first day of the month
    for (let i = 0; i < firstDay.getDay(); i++) {
        days.push(null);
    }

    // Add all days of the month
    for (let i = 1; i <= lastDay.getDate(); i++) {
        days.push(new Date(year, month, i));
    }

    return days;
});

const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
    fetchEvents();
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
    fetchEvents();
};

const isToday = (date: Date | null) => {
    if (!date) return false;
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

const isSelected = (date: Date | null) => {
    if (!date || !selectedDate.value) return false;
    return date.toDateString() === selectedDate.value.toDateString();
};

const selectDate = (date: Date | null) => {
    if (date) {
        selectedDate.value = date;
    }
};

const fetchEvents = async () => {
    const start = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
    const end = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0);

    try {
        const response = await fetch(route('employee.portal.calendar.events', {
            start: start.toISOString(),
            end: end.toISOString(),
        }));
        const data = await response.json();
        events.value = data.events;
    } catch (error) {
        console.error('Failed to fetch events:', error);
    }
};

const getEventsForDate = (date: Date | null) => {
    if (!date) return [];
    return events.value.filter(event => {
        const eventDate = new Date(event.start_time);
        return eventDate.toDateString() === date.toDateString();
    });
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        meeting: 'bg-blue-500',
        training: 'bg-purple-500',
        deadline: 'bg-red-500',
        holiday: 'bg-green-500',
        personal: 'bg-amber-500',
        team: 'bg-indigo-500',
    };
    return colors[type] || 'bg-gray-500';
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

onMounted(() => {
    fetchEvents();
});
</script>

<template>
    <Head title="Calendar" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Calendar</h1>
                    <p class="text-gray-500 mt-1">Manage your schedule and events</p>
                </div>
                <button @click="showEventModal = true"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    New Event
                </button>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Today's Events</p>
                            <p class="text-2xl font-bold text-blue-600">{{ summary.today_count }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <CalendarDaysIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">This Week</p>
                            <p class="text-2xl font-bold text-purple-600">{{ summary.week_count }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-5 text-white">
                    <p class="text-sm text-blue-100">Next Event</p>
                    <p v-if="summary.upcoming.length > 0" class="font-semibold mt-1">
                        {{ summary.upcoming[0].title }}
                    </p>
                    <p v-if="summary.upcoming.length > 0" class="text-sm text-blue-200 mt-1">
                        {{ formatTime(summary.upcoming[0].start_time) }}
                    </p>
                    <p v-else class="text-blue-200 mt-1">No upcoming events</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Calendar -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
                    <!-- Calendar Header -->
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">{{ currentMonth }}</h2>
                        <div class="flex items-center gap-2">
                            <button @click="previousMonth" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Previous month">
                                <ChevronLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            </button>
                            <button @click="currentDate = new Date()" class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                                Today
                            </button>
                            <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Next month">
                                <ChevronRightIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="p-5">
                        <!-- Day Headers -->
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day"
                                class="text-center text-sm font-medium text-gray-500 py-2">
                                {{ day }}
                            </div>
                        </div>

                        <!-- Days -->
                        <div class="grid grid-cols-7 gap-1">
                            <div v-for="(date, index) in daysInMonth" :key="index"
                                @click="selectDate(date)"
                                :class="[
                                    'min-h-[80px] p-2 rounded-lg cursor-pointer transition-colors',
                                    date ? 'hover:bg-gray-50' : '',
                                    isToday(date) ? 'bg-blue-50 border-2 border-blue-500' : 'border border-gray-100',
                                    isSelected(date) ? 'ring-2 ring-blue-500' : '',
                                ]">
                                <span v-if="date" :class="[
                                    'text-sm font-medium',
                                    isToday(date) ? 'text-blue-600' : 'text-gray-900',
                                ]">
                                    {{ date.getDate() }}
                                </span>

                                <!-- Event Dots -->
                                <div v-if="date" class="mt-1 space-y-1">
                                    <div v-for="event in getEventsForDate(date).slice(0, 2)" :key="event.id"
                                        :class="[getTypeColor(event.type), 'h-1.5 rounded-full']">
                                    </div>
                                    <p v-if="getEventsForDate(date).length > 2" class="text-xs text-gray-500">
                                        +{{ getEventsForDate(date).length - 2 }} more
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-5 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Upcoming Events</h2>
                    </div>

                    <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                        <div v-for="event in summary.upcoming" :key="event.id" class="p-4">
                            <div class="flex items-start gap-3">
                                <div :class="[getTypeColor(event.type), 'w-1 h-full rounded-full min-h-[40px]']"></div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">{{ event.title }}</h3>
                                    <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                                        <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                        <span>{{ formatTime(event.start_time) }}</span>
                                    </div>
                                    <div v-if="event.location" class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                                        <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                        <span>{{ event.location }}</span>
                                    </div>
                                    <div v-if="event.meeting_link" class="flex items-center gap-2 mt-1">
                                        <VideoCameraIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                                        <a :href="event.meeting_link" target="_blank" class="text-sm text-blue-600 hover:underline">
                                            Join Meeting
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="summary.upcoming.length === 0" class="p-8 text-center text-gray-500">
                            <CalendarDaysIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                            <p>No upcoming events</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
