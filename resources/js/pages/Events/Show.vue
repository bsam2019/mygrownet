<template>
  <MemberLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-6">
          <Link
            :href="route('events.index')"
            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
            Back to Events
          </Link>
        </div>

        <!-- Event Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="mb-2">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  getEventTypeColor(event.event_type)
                ]">
                  {{ formatEventType(event.event_type) }}
                </span>
              </div>
              <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ event.title }}
              </h1>
              <p v-if="event.description" class="text-gray-600 mb-4">
                {{ event.description }}
              </p>
            </div>
          </div>

          <!-- Event Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <CalendarIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
              </div>
              <div>
                <p class="text-xs text-gray-500">Date & Time</p>
                <p class="text-sm font-medium text-gray-900">{{ formatDate(event.scheduled_at) }}</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <ClockIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
              </div>
              <div>
                <p class="text-xs text-gray-500">Duration</p>
                <p class="text-sm font-medium text-gray-900">{{ event.duration_minutes }} minutes</p>
              </div>
            </div>

            <div v-if="event.max_attendees" class="flex items-center gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <UsersIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
              </div>
              <div>
                <p class="text-xs text-gray-500">Capacity</p>
                <p class="text-sm font-medium text-gray-900">{{ statistics.registrations }}/{{ event.max_attendees }} registered</p>
              </div>
            </div>

            <div v-if="event.host_name" class="flex items-center gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <UserIcon class="h-5 w-5 text-yellow-600" aria-hidden="true" />
              </div>
              <div>
                <p class="text-xs text-gray-500">Host</p>
                <p class="text-sm font-medium text-gray-900">{{ event.host_name }}</p>
              </div>
            </div>
          </div>

          <!-- Meeting Link (if registered) -->
          <div v-if="isRegistered && event.meeting_link" class="mt-6 pt-6 border-t border-gray-200">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-start gap-3">
                <VideoIcon class="h-5 w-5 text-blue-600 mt-0.5" aria-hidden="true" />
                <div class="flex-1">
                  <h4 class="text-sm font-semibold text-blue-900 mb-1">Meeting Link</h4>
                  <a
                    :href="event.meeting_link"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm text-blue-600 hover:text-blue-700 underline break-all"
                  >
                    {{ event.meeting_link }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Cards -->
        <div class="space-y-4">
          <!-- Already Attended -->
          <div v-if="hasAttended" class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center gap-3">
              <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              <div>
                <h3 class="text-lg font-semibold text-green-900">Event Attended!</h3>
                <p class="text-sm text-green-700 mt-1">
                  You've checked in to this event and earned your LGR credit.
                </p>
              </div>
            </div>
          </div>

          <!-- Check-in Available -->
          <div v-else-if="isRegistered && canCheckIn" class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Ready to check in?</h3>
                <p class="text-sm text-gray-600 mt-1">
                  Check in now to earn your LGR activity credit
                </p>
              </div>
              <button
                @click="checkIn"
                :disabled="processing"
                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing">Checking in...</span>
                <span v-else>Check In</span>
              </button>
            </div>
          </div>

          <!-- Registration -->
          <div v-else-if="!isRegistered" class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Register for this event</h3>
                <p class="text-sm text-gray-600 mt-1">
                  Secure your spot and get notified before the event starts
                </p>
              </div>
              <button
                @click="register"
                :disabled="processing"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing">Registering...</span>
                <span v-else>Register Now</span>
              </button>
            </div>
          </div>

          <!-- Registered but event not started -->
          <div v-else class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center gap-3">
              <CheckCircleIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              <div>
                <h3 class="text-lg font-semibold text-blue-900">You're Registered!</h3>
                <p class="text-sm text-blue-700 mt-1">
                  Check-in will be available when the event starts
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
  ArrowLeftIcon, 
  CalendarIcon, 
  ClockIcon, 
  UsersIcon, 
  UserIcon,
  VideoIcon,
  CheckCircleIcon 
} from 'lucide-vue-next';

interface Event {
  id: number;
  title: string;
  slug: string;
  description: string | null;
  event_type: string;
  scheduled_at: string;
  duration_minutes: number;
  max_attendees: number | null;
  host_name: string | null;
  meeting_link: string | null;
}

interface Statistics {
  registrations: number;
  attendances: number;
  attendance_rate: number;
}

interface Props {
  event: Event;
  isRegistered: boolean;
  hasAttended: boolean;
  statistics: Statistics;
}

const props = defineProps<Props>();

const processing = ref(false);

const canCheckIn = computed(() => {
  const now = new Date();
  const eventStart = new Date(props.event.scheduled_at);
  const eventEnd = new Date(eventStart.getTime() + props.event.duration_minutes * 60000);
  
  return now >= eventStart && now <= eventEnd;
});

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
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

const register = () => {
  processing.value = true;
  
  router.post(route('events.register', props.event.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false;
    },
  });
};

const checkIn = () => {
  processing.value = true;
  
  router.post(route('events.check-in', props.event.id), {
    method: 'manual',
  }, {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false;
    },
  });
};
</script>
