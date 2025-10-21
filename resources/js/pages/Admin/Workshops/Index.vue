<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { PencilIcon, TrashIcon, EyeIcon } from 'lucide-vue-next'

interface Workshop {
  id: number
  title: string
  slug: string
  category: string
  delivery_format: string
  price: string
  price_raw: number
  status: string
  start_date: string
  end_date: string
  max_participants: number | null
  registrations_count: number
  lp_reward: number
  bp_reward: number
}

const props = defineProps<{
  workshops: Workshop[]
}>()

const getStatusColor = (status: string) => {
  const colors = {
    draft: 'bg-gray-100 text-gray-800',
    published: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    completed: 'bg-blue-100 text-blue-800',
  }
  return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800'
}

const updateStatus = (workshopId: number, status: string) => {
  if (confirm(`Are you sure you want to change the status to ${status}?`)) {
    router.patch(`/admin/workshops/${workshopId}/status`, { status })
  }
}

const deleteWorkshop = (workshopId: number) => {
  alert(`Workshop ID: ${workshopId}, Type: ${typeof workshopId}, URL: /admin/workshops/${workshopId}`)
  if (confirm('Are you sure you want to delete this workshop? This action cannot be undone.')) {
    router.delete(`/admin/workshops/${workshopId}`, {
      onSuccess: () => {
        router.visit('/admin/workshops', { method: 'get' })
      }
    })
  }
}
</script>

<template>
  <Head title="Manage Workshops" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Manage Workshops</h1>
        <div class="flex space-x-3">
          <Link
            :href="route('admin.workshops.registrations')"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            View Registrations
          </Link>
        </div>
      </div>

      <!-- Mobile Card View -->
      <div class="md:hidden space-y-4">
        <div v-for="workshop in workshops" :key="workshop.id" class="bg-white rounded-lg shadow p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h3 class="font-medium text-gray-900">{{ workshop.title }}</h3>
              <p class="text-xs text-gray-500 mt-1">
                <span class="capitalize">{{ workshop.category.replace('_', ' ') }}</span> • 
                <span class="capitalize">{{ workshop.delivery_format }}</span>
              </p>
            </div>
            <select
              :value="workshop.status"
              @change="updateStatus(workshop.id, ($event.target as HTMLSelectElement).value)"
              :class="['px-2 py-1 text-xs rounded-full border-0', getStatusColor(workshop.status)]"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="cancelled">Cancelled</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          
          <div class="grid grid-cols-2 gap-3 text-sm mb-3">
            <div>
              <span class="text-gray-500">Price:</span>
              <span class="font-medium ml-1">K{{ workshop.price }}</span>
            </div>
            <div>
              <span class="text-gray-500">Participants:</span>
              <span class="font-medium ml-1">{{ workshop.registrations_count }}{{ workshop.max_participants ? ` / ${workshop.max_participants}` : '' }}</span>
            </div>
            <div class="col-span-2">
              <span class="text-gray-500">Start:</span>
              <span class="font-medium ml-1">{{ workshop.start_date }}</span>
            </div>
          </div>
          
          <div class="flex space-x-2 pt-3 border-t">
            <Link
              :href="route('mygrownet.workshops.show', workshop.slug)"
              class="flex-1 px-3 py-2 text-center text-sm bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100"
            >
              View
            </Link>
            <Link
              :href="route('admin.workshops.edit', workshop.id)"
              class="flex-1 px-3 py-2 text-center text-sm bg-green-50 text-green-600 rounded-lg hover:bg-green-100"
            >
              Edit
            </Link>
            <button
              @click="deleteWorkshop(workshop.id)"
              class="flex-1 px-3 py-2 text-center text-sm bg-red-50 text-red-600 rounded-lg hover:bg-red-100"
            >
              Delete
            </button>
          </div>
        </div>
        
        <div v-if="workshops.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
          No workshops found
        </div>
      </div>

      <!-- Desktop Table View -->
      <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Workshop</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participants</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="workshop in workshops" :key="workshop.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ workshop.title }}</div>
                <div class="text-xs text-gray-500">
                  <span class="capitalize">{{ workshop.category.replace('_', ' ') }}</span> • 
                  <span class="capitalize">{{ workshop.delivery_format }}</span> • 
                  {{ workshop.lp_reward }} LP / {{ workshop.bp_reward }} BP
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ workshop.start_date }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">K{{ workshop.price }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ workshop.registrations_count }}{{ workshop.max_participants ? ` / ${workshop.max_participants}` : '' }}
              </td>
              <td class="px-6 py-4">
                <select
                  :value="workshop.status"
                  @change="updateStatus(workshop.id, ($event.target as HTMLSelectElement).value)"
                  :class="['px-2 py-1 text-xs rounded-full border-0', getStatusColor(workshop.status)]"
                >
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="cancelled">Cancelled</option>
                  <option value="completed">Completed</option>
                </select>
              </td>
              <td class="px-6 py-4 text-sm">
                <div class="flex space-x-2">
                  <Link
                    :href="route('mygrownet.workshops.show', workshop.slug)"
                    class="text-blue-600 hover:text-blue-800"
                    title="View"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link
                    :href="route('admin.workshops.edit', workshop.id)"
                    class="text-green-600 hover:text-green-800"
                    title="Edit"
                  >
                    <PencilIcon class="w-5 h-5" />
                  </Link>
                  <button
                    @click="deleteWorkshop(workshop.id)"
                    class="text-red-600 hover:text-red-800"
                    title="Delete"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="workshops.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                No workshops found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
