<template>
  <MemberLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Live Events</h1>
          <p class="mt-1 text-sm text-gray-600">
            Attend events to earn LGR activity credits and connect with the community
          </p>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
              <button
                @click="changeFilter('upcoming')"
                :class="[
                  'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                  filter === 'upcoming'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                Upcoming Events
              </button>
              <button
                @click="changeFilter('past')"
                :class="[
                  'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                  filter === 'past'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                Past Events
              </button>
              <button
                @click="changeFilter('all')"
                :class="[
                  'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                  filter === 'all'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                All Events
              </button>
            </nav>
          </div>
        </div>

        <!-- Events List -->
        <div class="space-y-6">
          <div
            v-for="event in events"
            :key="event.id"
            class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden"
          >
            <div class="p-6">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <!-- Event Type Badge -->
                  <div class="mb-2">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getEventTypeColor(event.event_type)
                    ]">
                      {{ formatEventType(event.event_type) }}
                    </span>
                  </div>

                  <!-- Title -->
                  <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    {{ event.title }}
                  </h3>

                  <!-- Description -->
                  <p v-if="event.description" class="text-sm text-gray-600 mb-4">
                    {{ event.description }}
                  </p>

                  <!-- Event Details -->
                  <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-4">
                    <div class="flex items-center gap-1">
                      <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                      <span>{{ formatDate(event.scheduled_at) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                      <ClockIcon class="h-4 w-4" aria-hidden="true" />
                      <span>{{ event.duration_minutes }} minutes</span>
                    </div>
                    <div v-if="event.max_attendees" class="flex items-center gap-1">
                      <UsersIcon class="h-4 w-4" aria-hidden="true" />
                      <span>Max {{ event.max_attendees }} attendees</span>
                    </div>
                  </div>

                  <!-- Status Badges -->
                  <div class="flex items-center gap-2">
                    <span v-if="isRegistered(event.id)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      Registered
                    </span>
                    <span v-if="hasAttended(event.id)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      <CheckCircleIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                      Attended
                    </span>
                  </div>
                </div>

                <!-- Action Button -->
                <div class="ml-4">
                  <Link
                    :href="route('events.show', event.slug)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm"
                  >
                    View Details
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="events.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3>
          <p class="mt-1 text-sm text-gray-500">
            Check back soon for upcoming events
          </p>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { CalendarIcon, ClockIcon, UsersIcon, CheckCircleIcon } from 'lucide-vue-next';

interface Event {
  id: number;
  title: string;
  slug: string;
  description: string | null;
  event_type: string;
  scheduled_at: string;
  duration_minutes: number;
  max_attendees: number | null;
}

interface Registration {
  live_event_id: number;
}

interface Attendance {
  live_event_id: number;
}

interface Props {
  events: Event[];
  registrations: Registration[];
  attendances: Attendance[];
  filter: string;
}

const props = defineProps<Props>();

const isRegistered = (eventId: number): boolean => {
  return props.registrations.some(r => r.live_event_id === eventId);
};

const hasAttended = (eventId: number): boolean => {
  return props.attendances.some(a => a.live_event_id === eventId);
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatEventType = (type: string): string => {
  return type.charAt(0).toUpperCase() + type.slice(1);
};

const getEventTypeColor = (type: string): string => {
  const colors: Record<string, string> = {
    webinar: 'bg-purple-100 text-purple-800',
    workshop: 'bg-blue-100 text-blue-800',
    training: 'bg-green-100 text-green-800',
    meeting: 'bg-yellow-100 text-yellow-800',
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const changeFilter = (newFilter: string) => {
  router.get(route('events.index'), { filter: newFilter }, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>
