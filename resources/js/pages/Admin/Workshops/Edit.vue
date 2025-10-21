<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeftIcon } from 'lucide-vue-next'

interface Workshop {
  id: number
  title: string
  description: string
  category: string
  delivery_format: string
  price: number
  start_date: string
  end_date: string
  location: string | null
  meeting_link: string | null
  max_participants: number | null
  requirements: string | null
  learning_outcomes: string | null
  instructor_name: string | null
  instructor_bio: string | null
  lp_reward: number
  bp_reward: number
  status: string
}

const props = defineProps<{
  workshop: Workshop
}>()

const form = useForm({
  title: props.workshop.title,
  description: props.workshop.description,
  category: props.workshop.category,
  delivery_format: props.workshop.delivery_format,
  price: props.workshop.price,
  start_date: props.workshop.start_date,
  end_date: props.workshop.end_date,
  location: props.workshop.location || '',
  meeting_link: props.workshop.meeting_link || '',
  max_participants: props.workshop.max_participants,
  requirements: props.workshop.requirements || '',
  learning_outcomes: props.workshop.learning_outcomes || '',
  instructor_name: props.workshop.instructor_name || '',
  instructor_bio: props.workshop.instructor_bio || '',
  lp_reward: props.workshop.lp_reward,
  bp_reward: props.workshop.bp_reward,
  status: props.workshop.status,
})

const submit = () => {
  if (!props.workshop?.id) {
    alert('Workshop ID is missing!')
    return
  }
  
  // Use transform to add _method field
  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(`/admin/workshops/${props.workshop.id}`)
}
</script>

<template>
  <Head title="Edit Workshop" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex items-center space-x-4 mb-6">
        <Link :href="route('admin.workshops.index')" class="text-gray-600 hover:text-gray-900">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <h1 class="text-2xl font-semibold text-gray-900">Edit Workshop</h1>
      </div>

      <form @submit.prevent="submit" class="max-w-4xl">
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
              <input v-model="form.title" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
              <textarea v-model="form.description" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
              <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
              <select v-model="form.category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="financial_literacy">Financial Literacy</option>
                <option value="business_skills">Business Skills</option>
                <option value="leadership">Leadership</option>
                <option value="marketing">Marketing</option>
                <option value="technology">Technology</option>
                <option value="personal_development">Personal Development</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Format *</label>
              <select v-model="form.delivery_format" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="online">Online</option>
                <option value="physical">Physical</option>
                <option value="hybrid">Hybrid</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Price (K) *</label>
              <input v-model.number="form.price" type="number" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Max Participants</label>
              <input v-model.number="form.max_participants" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
              <input v-model="form.start_date" type="datetime-local" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
              <input v-model="form.end_date" type="datetime-local" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
              <input v-model="form.location" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Meeting Link</label>
              <input v-model="form.meeting_link" type="url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">LP Reward *</label>
              <input v-model.number="form.lp_reward" type="number" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">BP Reward *</label>
              <input v-model.number="form.bp_reward" type="number" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
              <textarea v-model="form.requirements" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Learning Outcomes</label>
              <textarea v-model="form.learning_outcomes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Instructor Name</label>
              <input v-model="form.instructor_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
              <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Instructor Bio</label>
              <textarea v-model="form.instructor_bio" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t">
            <Link :href="route('admin.workshops.index')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </Link>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
              {{ form.processing ? 'Updating...' : 'Update Workshop' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
