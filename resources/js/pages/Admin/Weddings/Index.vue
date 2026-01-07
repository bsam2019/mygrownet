<template>
  <Head title="Wedding Cards Management" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Wedding Cards</h1>
            <p class="mt-1 text-sm text-gray-500">Manage electronic wedding invitations</p>
          </div>
          <Link
            :href="route('admin.weddings.create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
          >
            <PlusIcon class="h-5 w-5" aria-hidden="true" />
            Create Wedding Card
          </Link>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Cards</div>
            <div class="text-2xl font-bold text-gray-900">{{ weddings.length }}</div>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Published</div>
            <div class="text-2xl font-bold text-green-600">{{ publishedCount }}</div>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Drafts</div>
            <div class="text-2xl font-bold text-amber-600">{{ draftCount }}</div>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Upcoming</div>
            <div class="text-2xl font-bold text-purple-600">{{ upcomingCount }}</div>
          </div>
        </div>

        <!-- Wedding Cards Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Couple</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="wedding in weddings" :key="wedding.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                      <HeartIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ wedding.groom_name }} & {{ wedding.bride_name }}
                      </div>
                      <div class="text-sm text-gray-500">{{ wedding.venue_name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(wedding.wedding_date) }}</div>
                  <div class="text-xs text-gray-500">{{ daysUntil(wedding.wedding_date) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="wedding.template" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    {{ wedding.template.name }}
                  </span>
                  <span v-else class="text-sm text-gray-400">No template</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="wedding.is_published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ wedding.is_published ? 'Published' : 'Draft' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <a
                      v-if="wedding.is_published"
                      :href="`/wedding/${wedding.slug}`"
                      target="_blank"
                      class="text-gray-400 hover:text-gray-600"
                      title="View Live"
                    >
                      <EyeIcon class="h-5 w-5" aria-hidden="true" />
                    </a>
                    <Link
                      :href="route('admin.weddings.edit', wedding.id)"
                      class="text-purple-600 hover:text-purple-900"
                      title="Edit"
                    >
                      <PencilIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <button
                      @click="confirmDelete(wedding)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete"
                    >
                      <TrashIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="weddings.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <HeartIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                  <h3 class="mt-2 text-sm font-medium text-gray-900">No wedding cards</h3>
                  <p class="mt-1 text-sm text-gray-500">Get started by creating a new wedding card.</p>
                  <div class="mt-6">
                    <Link
                      :href="route('admin.weddings.create')"
                      class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700"
                    >
                      <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                      Create Wedding Card
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <TransitionRoot as="template" :show="showDeleteModal">
      <Dialog as="div" class="relative z-50" @close="showDeleteModal = false">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">Delete Wedding Card</DialogTitle>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete the wedding card for {{ deletingWedding?.groom_name }} & {{ deletingWedding?.bride_name }}? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
              <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                  @click="deleteWedding"
                >
                  Delete
                </button>
                <button
                  type="button"
                  class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                  @click="showDeleteModal = false"
                >
                  Cancel
                </button>
              </div>
            </DialogPanel>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot } from '@headlessui/vue'
import { PlusIcon, HeartIcon, PencilIcon, TrashIcon, EyeIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import AdminLayout from '@/layouts/AdminLayout.vue'

interface Wedding {
  id: number
  groom_name: string
  bride_name: string
  slug: string
  wedding_date: string
  venue_name: string
  template: { id: number; name: string } | null
  is_published: boolean
  guest_count: number
  status: string
  created_at: string
}

const props = defineProps<{
  weddings: Wedding[]
}>()

const showDeleteModal = ref(false)
const deletingWedding = ref<Wedding | null>(null)

const publishedCount = computed(() => props.weddings.filter(w => w.is_published).length)
const draftCount = computed(() => props.weddings.filter(w => !w.is_published).length)
const upcomingCount = computed(() => {
  const today = new Date()
  return props.weddings.filter(w => new Date(w.wedding_date) > today).length
})

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const daysUntil = (dateStr: string) => {
  const today = new Date()
  const weddingDate = new Date(dateStr)
  const diffTime = weddingDate.getTime() - today.getTime()
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'Past'
  if (diffDays === 0) return 'Today!'
  if (diffDays === 1) return 'Tomorrow'
  return `${diffDays} days away`
}

const confirmDelete = (wedding: Wedding) => {
  deletingWedding.value = wedding
  showDeleteModal.value = true
}

const deleteWedding = () => {
  if (!deletingWedding.value) return
  
  router.delete(route('admin.weddings.destroy', deletingWedding.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      deletingWedding.value = null
    }
  })
}
</script>
