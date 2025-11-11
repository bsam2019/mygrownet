<template>
  <Head title="Manage Announcements" />
  
  <AdminLayout>
    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Announcement Management</h1>
        <p class="mt-2 text-gray-600">Create and manage platform announcements</p>
      </div>

      <!-- Create New Announcement -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Create New Announcement</h2>
        
        <form @submit.prevent="createAnnouncement" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input
              v-model="form.title"
              type="text"
              required
              maxlength="255"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter announcement title"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
            <textarea
              v-model="form.message"
              required
              maxlength="1000"
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter announcement message"
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">{{ form.message.length }}/1000 characters</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select
                v-model="form.type"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="info">Info (Blue)</option>
                <option value="warning">Warning (Amber)</option>
                <option value="success">Success (Green)</option>
                <option value="urgent">Urgent (Red)</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience</label>
              <select
                v-model="form.target_audience"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="all">All Users</option>
                <option value="starter_kit_owners">Starter Kit Owners</option>
                <option value="tier:Associate">Associates Only</option>
                <option value="tier:Professional">Professionals Only</option>
                <option value="tier:Senior">Seniors Only</option>
                <option value="tier:Manager">Managers Only</option>
                <option value="tier:Director">Directors Only</option>
                <option value="tier:Executive">Executives Only</option>
                <option value="tier:Ambassador">Ambassadors Only</option>
                <option value="tier:Manager,Director,Executive,Ambassador">Leaders (Manager+)</option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-6">
            <label class="flex items-center gap-2">
              <input
                v-model="form.is_urgent"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="text-sm font-medium text-gray-700">Mark as Urgent</span>
            </label>

            <label class="flex items-center gap-2">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="submitting"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ submitting ? 'Creating...' : 'Create Announcement' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Existing Announcements -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-bold text-gray-900">Existing Announcements</h2>
        </div>

        <div class="divide-y divide-gray-200">
          <div
            v-for="announcement in announcements.data"
            :key="announcement.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span
                    :class="getTypeBadgeClass(announcement.type)"
                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                  >
                    {{ announcement.type.toUpperCase() }}
                  </span>
                  <span
                    v-if="announcement.is_urgent"
                    class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800"
                  >
                    URGENT
                  </span>
                  <span
                    :class="announcement.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                  >
                    {{ announcement.is_active ? 'ACTIVE' : 'INACTIVE' }}
                  </span>
                </div>
                
                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ announcement.title }}</h3>
                <p class="text-gray-600 mb-2">{{ announcement.message }}</p>
                
                <div class="flex items-center gap-4 text-sm text-gray-500">
                  <span>Target: {{ announcement.target_audience }}</span>
                  <span>Created: {{ formatDate(announcement.created_at) }}</span>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="toggleActive(announcement.id)"
                  class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                  {{ announcement.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button
                  @click="deleteAnnouncement(announcement.id)"
                  class="px-3 py-1.5 text-sm font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 transition-colors"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>

          <div v-if="announcements.data.length === 0" class="p-12 text-center text-gray-500">
            No announcements yet. Create your first one above!
          </div>
        </div>
      </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Props {
  announcements: {
    data: any[];
    links: any;
    meta: any;
  };
}

const props = defineProps<Props>();

const form = ref({
  title: '',
  message: '',
  type: 'info',
  target_audience: 'all',
  is_urgent: false,
  is_active: true,
});

const submitting = ref(false);

const createAnnouncement = () => {
  submitting.value = true;
  
  router.post(route('admin.announcements.store'), form.value, {
    onSuccess: () => {
      form.value = {
        title: '',
        message: '',
        type: 'info',
        target_audience: 'all',
        is_urgent: false,
        is_active: true,
      };
      submitting.value = false;
    },
    onError: () => {
      submitting.value = false;
    },
  });
};

const toggleActive = (id: number) => {
  router.post(route('admin.announcements.toggle', id), {}, {
    preserveScroll: true,
  });
};

const deleteAnnouncement = (id: number) => {
  if (confirm('Are you sure you want to delete this announcement?')) {
    router.delete(route('admin.announcements.destroy', id), {
      preserveScroll: true,
    });
  }
};

const getTypeBadgeClass = (type: string) => {
  const classes = {
    info: 'bg-blue-100 text-blue-800',
    warning: 'bg-amber-100 text-amber-800',
    success: 'bg-green-100 text-green-800',
    urgent: 'bg-red-100 text-red-800',
  };
  return classes[type as keyof typeof classes] || classes.info;
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
