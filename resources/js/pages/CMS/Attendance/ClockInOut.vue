<template>
  <CMSLayout title="Clock In/Out">
    <div class="max-w-2xl mx-auto">
      <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Attendance</h1>

        <!-- Current Status -->
        <div v-if="todayRecord" class="mb-6 p-4 bg-blue-50 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-blue-900">Currently Clocked In</p>
              <p class="text-2xl font-bold text-blue-600 mt-1">{{ todayRecord.clock_in_time }}</p>
              <p v-if="todayRecord.is_late" class="text-sm text-orange-600 mt-1">
                Late by {{ todayRecord.late_by_minutes }} minutes
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-600">Shift</p>
              <p class="font-medium text-gray-900">{{ todayRecord.shift?.shift_name || 'No shift' }}</p>
            </div>
          </div>
        </div>

        <!-- Clock In/Out Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Worker Selection (for managers) -->
          <div v-if="workers && workers.length > 0">
            <label class="block text-sm font-medium text-gray-700 mb-1">Worker</label>
            <select
              v-model="form.worker_id"
              required
              class="w-full rounded-lg border-gray-300"
            >
              <option value="">Select worker</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                {{ worker.name }} ({{ worker.worker_number }})
              </option>
            </select>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full rounded-lg border-gray-300"
              placeholder="Any additional notes..."
            ></textarea>
          </div>

          <!-- Location Capture -->
          <div v-if="locationEnabled" class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm text-gray-700">Location captured</span>
              </div>
              <button
                type="button"
                @click="captureLocation"
                class="text-sm text-blue-600 hover:text-blue-700"
              >
                Refresh
              </button>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3">
            <button
              v-if="!todayRecord || !todayRecord.clock_in_time"
              type="submit"
              :disabled="form.processing"
              class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 font-medium"
            >
              {{ form.processing ? 'Clocking In...' : 'Clock In' }}
            </button>
            <button
              v-else-if="todayRecord && !todayRecord.clock_out_time"
              type="submit"
              :disabled="form.processing"
              class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 font-medium"
            >
              {{ form.processing ? 'Clocking Out...' : 'Clock Out' }}
            </button>
            <div v-else class="flex-1 p-4 bg-gray-50 rounded-lg text-center">
              <p class="text-sm text-gray-600">Already clocked out for today</p>
              <p class="text-xs text-gray-500 mt-1">Clock out time: {{ todayRecord.clock_out_time }}</p>
            </div>
          </div>
        </form>

        <!-- Today's Summary -->
        <div v-if="todayRecord && todayRecord.clock_out_time" class="mt-6 pt-6 border-t">
          <h3 class="text-sm font-medium text-gray-900 mb-3">Today's Summary</h3>
          <div class="grid grid-cols-3 gap-4 text-center">
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ formatMinutes(todayRecord.total_minutes) }}</p>
              <p class="text-xs text-gray-500 mt-1">Total Hours</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ formatMinutes(todayRecord.regular_minutes) }}</p>
              <p class="text-xs text-gray-500 mt-1">Regular Hours</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-orange-600">{{ formatMinutes(todayRecord.overtime_minutes) }}</p>
              <p class="text-xs text-gray-500 mt-1">Overtime</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  todayRecord?: any;
  workers?: any[];
}

const props = defineProps<Props>();

const locationEnabled = ref(false);

const form = useForm({
  worker_id: props.workers && props.workers.length > 0 ? '' : null,
  notes: '',
  location: null as string | null,
  device: 'web',
});

const submit = () => {
  if (!props.todayRecord || !props.todayRecord.clock_in_time) {
    form.post(route('cms.attendance.clock-in'));
  } else {
    form.post(route('cms.attendance.clock-out', props.todayRecord.id));
  }
};

const captureLocation = () => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        form.location = `${position.coords.latitude},${position.coords.longitude}`;
        locationEnabled.value = true;
      },
      (error) => {
        console.error('Location error:', error);
      }
    );
  }
};

const formatMinutes = (minutes: number | null): string => {
  if (!minutes) return '0h';
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
};

onMounted(() => {
  captureLocation();
});
</script>
