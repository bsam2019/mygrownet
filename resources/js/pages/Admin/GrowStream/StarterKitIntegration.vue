<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center gap-4 mb-4">
            <button
              @click="goBack"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back
            </button>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">GrowStream Starter Kit Integration</h1>
              <p class="mt-1 text-gray-600">Add GrowStream videos to starter kit content</p>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
          <nav class="flex space-x-8">
            <button
              @click="activeTab = 'available'"
              :class="{
                'border-blue-500 text-blue-600': activeTab === 'available',
                'border-transparent text-gray-500 hover:text-gray-700': activeTab !== 'available'
              }"
              class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
            >
              Available Videos ({{ availableVideos.length }})
            </button>
            <button
              @click="activeTab = 'integrated'"
              :class="{
                'border-blue-500 text-blue-600': activeTab === 'integrated',
                'border-transparent text-gray-500 hover:text-gray-700': activeTab !== 'integrated'
              }"
              class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
            >
              Starter Kit Videos ({{ starterKitVideos.length }})
            </button>
          </nav>
        </div>

        <!-- Available Videos Tab -->
        <div v-if="activeTab === 'available'" class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Available Videos</h2>
            <p class="text-sm text-gray-600 mt-1">Published videos that can be added to starter kits</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Video
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Creator
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Duration
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Views
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Current Points
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="video in availableVideos" :key="video.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ video.title }}</p>
                      <p class="text-sm text-gray-500">{{ video.categories }}</p>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ video.creator }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ video.duration }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ video.view_count.toLocaleString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ video.current_points }} pts
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      @click="addToStarterKit(video)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Add to Starter Kit
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Starter Kit Videos Tab -->
        <div v-if="activeTab === 'integrated'" class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Starter Kit Videos</h2>
            <p class="text-sm text-gray-600 mt-1">Videos currently included in starter kit content</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Video
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Creator
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tier
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Unlock Order
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Points Reward
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="video in starterKitVideos" :key="video.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ video.title }}</p>
                      <p class="text-sm text-gray-500">{{ video.view_count }} views</p>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ video.creator }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="{
                        'bg-blue-100 text-blue-800': video.starter_kit_tier === 'Basic Tier',
                        'bg-purple-100 text-purple-800': video.starter_kit_tier === 'Premium Tier',
                        'bg-yellow-100 text-yellow-800': video.starter_kit_tier === 'Elite Tier',
                        'bg-gray-100 text-gray-800': video.starter_kit_tier === 'All Tiers'
                      }"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ video.starter_kit_tier }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ video.unlock_order }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      <div>Starter Kit: {{ video.points_reward }} pts</div>
                      <div class="text-xs text-gray-500">Total: {{ video.total_points }} pts</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      @click="removeFromStarterKit(video)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Add to Starter Kit Modal -->
        <div
          v-if="showAddModal"
          class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
          @click="closeAddModal"
        >
          <div
            class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            @click.stop
          >
            <div class="mt-3">
              <h3 class="text-lg font-medium text-gray-900 mb-4">
                Add to Starter Kit: {{ selectedVideo?.title }}
              </h3>
              
              <form @submit.prevent="submitAddToStarterKit">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Starter Kit Tier
                    </label>
                    <select
                      v-model="addForm.tier"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                      <option value="">Select Tier</option>
                      <option value="basic">Basic Tier</option>
                      <option value="premium">Premium Tier</option>
                      <option value="elite">Elite Tier</option>
                      <option value="all">All Tiers</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Unlock Order (1-100)
                    </label>
                    <input
                      v-model.number="addForm.unlock_order"
                      type="number"
                      min="1"
                      max="100"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Points Reward (0-500)
                    </label>
                    <input
                      v-model.number="addForm.points_reward"
                      type="number"
                      min="0"
                      max="500"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Description (Optional)
                    </label>
                    <textarea
                      v-model="addForm.description"
                      rows="3"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Custom description for starter kit..."
                    />
                  </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                  <button
                    type="button"
                    @click="closeAddModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
                  >
                    {{ processing ? 'Adding...' : 'Add to Starter Kit' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

interface Video {
  id: number
  title: string
  creator: string
  duration?: string
  view_count: number
  categories?: string
  current_points?: number
  starter_kit_tier?: string
  unlock_order?: number
  points_reward?: number
  total_points?: number
}

defineProps<{
  availableVideos: Video[]
  starterKitVideos: Video[]
}>()

const activeTab = ref('available')
const showAddModal = ref(false)
const selectedVideo = ref<Video | null>(null)
const processing = ref(false)

const addForm = reactive({
  tier: '',
  unlock_order: 1,
  points_reward: 0,
  description: ''
})

const goBack = () => {
  window.history.back()
}

const addToStarterKit = (video: Video) => {
  selectedVideo.value = video
  addForm.tier = ''
  addForm.unlock_order = 1
  addForm.points_reward = 0
  addForm.description = ''
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  selectedVideo.value = null
}

const submitAddToStarterKit = () => {
  if (!selectedVideo.value) return
  
  processing.value = true
  
  router.post(
    route('admin.growstream.videos.add-to-starter-kit', selectedVideo.value.id),
    addForm,
    {
      onSuccess: () => {
        closeAddModal()
      },
      onFinish: () => {
        processing.value = false
      }
    }
  )
}

const removeFromStarterKit = (video: Video) => {
  if (confirm('Are you sure you want to remove this video from the starter kit?')) {
    router.delete(route('admin.growstream.videos.remove-from-starter-kit', video.id))
  }
}
</script>