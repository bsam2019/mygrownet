<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    PlusIcon,
    ListBulletIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

interface Service {
    id: number;
    name: string;
    color: string;
    duration_minutes: number;
    formatted_price: string;
}

interface Provider {
    id: number;
    name: string;
    color: string;
}

interface CalendarEvent {
    id: number;
    title: string;
    start: string;
    end: string;
    backgroundColor: string;
    extendedProps: {
        booking_reference: string;
        service_name: string;
        provider_name: string;
        status: string;
        status_label: string;
    };
}

const props = defineProps<{
    services: Service[];
    providers: Provider[];
    settings: Record<string, any>;
}>();

const currentDate = ref(new Date());
const viewMode = ref<'month' | 'week' | 'day'>('week');
const selectedProvider = ref<number | null>(null);
const events = ref<CalendarEvent[]>([]);
const isLoading = ref(false);
const selectedEvent = ref<CalendarEvent | null>(null);

const currentMonth = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const currentWeek = computed(() => {
    const start = getWeekStart(currentDate.value);
    const end = new Date(start);
    end.setDate(end.getDate() + 6);
    return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
});

const weekDays = computed(() => {
    const start = getWeekStart(currentDate.value);
    const days = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(start);
        date.setDate(date.getDate() + i);
        days.push({
            date,
            dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
            dayNumber: date.getDate(),
            isToday: isSameDay(date, new Date()),
            dateStr: date.toISOString().split('T')[0],
        });
    }
    return days;
});

const timeSlots = computed(() => {
    const slots = [];
    for (let hour = 7; hour <= 20; hour++) {
        slots.push({
            hour,
            label: `${hour > 12 ? hour - 12 : hour}:00 ${hour >= 12 ? 'PM' : 'AM'}`,
        });
    }
    return slots;
});

const getWeekStart = (date: Date) => {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    d.setDate(diff);
    d.setHours(0, 0, 0, 0);
    return d;
};

const isSameDay = (d1: Date, d2: Date) => {
    return d1.getFullYear() === d2.getFullYear() &&
           d1.getMonth() === d2.getMonth() &&
           d1.getDate() === d2.getDate();
};

const navigate = (direction: number) => {
    const newDate = new Date(currentDate.value);
    if (viewMode.value === 'week') {
        newDate.setDate(newDate.getDate() + (direction * 7));
    } else if (viewMode.value === 'month') {
        newDate.setMonth(newDate.getMonth() + direction);
    } else {
        newDate.setDate(newDate.getDate() + direction);
    }
    currentDate.value = newDate;
};

const goToToday = () => {
    currentDate.value = new Date();
};

const fetchEvents = async () => {
    isLoading.value = true;
    const start = getWeekStart(currentDate.value);
    const end = new Date(start);
    end.setDate(end.getDate() + 6);

    try {
        const response = await fetch(route('growbiz.appointments.calendar.events', {
            start: start.toISOString().split('T')[0],
            end: end.toISOString().split('T')[0],
            provider_id: selectedProvider.value || undefined,
        }));
        events.value = await response.json();
    } catch (error) {
        console.error('Failed to fetch events:', error);
    } finally {
        isLoading.value = false;
    }
};

const getEventsForSlot = (dateStr: string, hour: number) => {
    return events.value.filter(event => {
        const eventDate = event.start.split('T')[0];
        const eventHour = parseInt(event.start.split('T')[1].split(':')[0]);
        return eventDate === dateStr && eventHour === hour;
    });
};

const getEventStyle = (event: CalendarEvent) => {
    const startTime = event.start.split('T')[1];
    const endTime = event.end.split('T')[1];
    const startMinutes = parseInt(startTime.split(':')[1]);
    const startHour = parseInt(startTime.split(':')[0]);
    const endHour = parseInt(endTime.split(':')[0]);
    const endMinutes = parseInt(endTime.split(':')[1]);
    
    const duration = (endHour * 60 + endMinutes) - (startHour * 60 + startMinutes);
    const height = Math.max(duration / 60 * 48, 24); // 48px per hour, min 24px
    const top = startMinutes / 60 * 48;

    return {
        height: `${height}px`,
        top: `${top}px`,
        backgroundColor: event.backgroundColor,
    };
};

const openEvent = (event: CalendarEvent) => {
    router.visit(route('growbiz.appointments.show', event.id));
};

watch([currentDate, selectedProvider], fetchEvents);

onMounted(fetchEvents);
</script>

<template>
    <GrowBizLayout>
        <div class="flex flex-col h-[calc(100vh-8rem)]">
            <!-- Header -->
            <div class="flex-shrink-0 p-4 bg-white border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <button @click="navigate(-1)" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Previous">
                            <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                        <button @click="navigate(1)" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Next">
                            <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                        <h2 class="text-lg font-semibold text-gray-900 ml-2">{{ currentWeek }}</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="goToToday" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">
                            Today
                        </button>
                        <Link
                            :href="route('growbiz.appointments.index')"
                            class="p-2 hover:bg-gray-100 rounded-lg"
                            aria-label="List view"
                        >
                            <ListBulletIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>
                    </div>
                </div>

                <!-- Provider Filter -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2">
                    <button
                        @click="selectedProvider = null"
                        class="flex-shrink-0 px-3 py-1.5 text-sm font-medium rounded-full transition-colors"
                        :class="selectedProvider === null ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        All Staff
                    </button>
                    <button
                        v-for="provider in providers"
                        :key="provider.id"
                        @click="selectedProvider = provider.id"
                        class="flex-shrink-0 px-3 py-1.5 text-sm font-medium rounded-full transition-colors"
                        :class="selectedProvider === provider.id ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        {{ provider.name }}
                    </button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="flex-1 overflow-auto">
                <div class="min-w-[700px]">
                    <!-- Day Headers -->
                    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 grid grid-cols-8">
                        <div class="p-2 text-xs text-gray-400 border-r border-gray-100"></div>
                        <div
                            v-for="day in weekDays"
                            :key="day.dateStr"
                            class="p-2 text-center border-r border-gray-100"
                            :class="{ 'bg-emerald-50': day.isToday }"
                        >
                            <p class="text-xs text-gray-500">{{ day.dayName }}</p>
                            <p
                                class="text-lg font-semibold"
                                :class="day.isToday ? 'text-emerald-600' : 'text-gray-900'"
                            >
                                {{ day.dayNumber }}
                            </p>
                        </div>
                    </div>

                    <!-- Time Grid -->
                    <div class="relative">
                        <div v-for="slot in timeSlots" :key="slot.hour" class="grid grid-cols-8 border-b border-gray-100" style="height: 48px;">
                            <div class="p-1 text-xs text-gray-400 border-r border-gray-100 text-right pr-2">
                                {{ slot.label }}
                            </div>
                            <div
                                v-for="day in weekDays"
                                :key="`${day.dateStr}-${slot.hour}`"
                                class="relative border-r border-gray-100"
                                :class="{ 'bg-emerald-50/30': day.isToday }"
                            >
                                <!-- Events -->
                                <div
                                    v-for="event in getEventsForSlot(day.dateStr, slot.hour)"
                                    :key="event.id"
                                    @click="openEvent(event)"
                                    class="absolute left-0.5 right-0.5 rounded px-1 py-0.5 text-xs text-white cursor-pointer overflow-hidden hover:opacity-90 transition-opacity"
                                    :style="getEventStyle(event)"
                                >
                                    <p class="font-medium truncate">{{ event.title }}</p>
                                    <p class="truncate opacity-80">{{ event.extendedProps.service_name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAB -->
            <Link
                :href="route('growbiz.appointments.index')"
                class="fixed bottom-24 right-4 w-14 h-14 bg-emerald-600 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-emerald-700 transition-colors"
                aria-label="New appointment"
            >
                <PlusIcon class="h-6 w-6" aria-hidden="true" />
            </Link>
        </div>
    </GrowBizLayout>
</template>
