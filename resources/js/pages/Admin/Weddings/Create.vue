<template>
  <Head title="Create Wedding Card" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <Link :href="route('admin.weddings.index')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
            <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
            Back to Wedding Cards
          </Link>
          <h1 class="text-2xl font-semibold text-gray-900">Create Wedding Card</h1>
          <p class="mt-1 text-sm text-gray-500">Create a new electronic wedding invitation</p>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
          <!-- Step 1: Select Template -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">1. Select Template</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div
                v-for="template in templates"
                :key="template.id"
                class="relative"
              >
                <button
                  type="button"
                  @click="form.template_id = template.id"
                  :class="[
                    'w-full rounded-lg border-2 p-4 text-left transition-all',
                    form.template_id === template.id 
                      ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-500' 
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                >
                  <div 
                    class="h-24 rounded-md mb-3 flex items-center justify-center"
                    :style="{ backgroundColor: template.settings?.colors?.primary || '#9333ea' }"
                  >
                    <HeartIcon class="h-8 w-8 text-white" aria-hidden="true" />
                  </div>
                  <div class="font-medium text-gray-900">{{ template.name }}</div>
                  <div class="text-xs text-gray-500 mt-1 line-clamp-2">{{ template.description }}</div>
                  <span v-if="template.is_premium" class="absolute top-2 right-2 bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full">
                    Premium
                  </span>
                  <CheckCircleIcon 
                    v-if="form.template_id === template.id"
                    class="absolute top-2 left-2 h-6 w-6 text-purple-600"
                    aria-hidden="true"
                  />
                </button>
                <!-- Preview Button -->
                <button
                  type="button"
                  @click.stop="openTemplatePreview(template)"
                  class="absolute bottom-16 right-2 p-1.5 bg-white/90 rounded-full shadow hover:bg-white transition-colors"
                  title="Preview template"
                >
                  <EyeIcon class="h-4 w-4 text-gray-600" aria-hidden="true" />
                </button>
              </div>
            </div>
            <p v-if="errors.template_id" class="mt-2 text-sm text-red-600">{{ errors.template_id }}</p>
          </div>

          <!-- Step 2: Couple Details -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">2. Couple Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Groom's Name *</label>
                <input
                  v-model="form.groom_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="John"
                />
                <p v-if="errors.groom_name" class="mt-1 text-sm text-red-600">{{ errors.groom_name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Bride's Name *</label>
                <input
                  v-model="form.bride_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="Jane"
                />
                <p v-if="errors.bride_name" class="mt-1 text-sm text-red-600">{{ errors.bride_name }}</p>
              </div>
            </div>
            
            <!-- Generated Slug Preview -->
            <div v-if="form.groom_name && form.bride_name && form.wedding_date" class="mt-4 p-3 bg-gray-50 rounded-md">
              <div class="text-sm text-gray-500">Wedding URL will be:</div>
              <div class="text-sm font-mono text-purple-600">/wedding/{{ generatedSlug }}</div>
            </div>
          </div>

          <!-- Step 3: Wedding Details -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">3. Wedding Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Wedding Date *</label>
                <input
                  v-model="form.wedding_date"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                />
                <p v-if="errors.wedding_date" class="mt-1 text-sm text-red-600">{{ errors.wedding_date }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">RSVP Deadline</label>
                <input
                  v-model="form.rsvp_deadline"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Venue Name *</label>
                <input
                  v-model="form.venue_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="3Sixty Convention Centre"
                />
                <p v-if="errors.venue_name" class="mt-1 text-sm text-red-600">{{ errors.venue_name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Venue Address *</label>
                <input
                  v-model="form.venue_location"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="Twin Palm Road, Ibex Hill, Lusaka"
                />
                <p v-if="errors.venue_location" class="mt-1 text-sm text-red-600">{{ errors.venue_location }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Ceremony Time</label>
                <input
                  v-model="form.ceremony_time"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="11:00 AM"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Reception Time</label>
                <input
                  v-model="form.reception_time"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="2:00 PM"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Dress Code</label>
                <input
                  v-model="form.dress_code"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="Formal Attire"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Expected Guests</label>
                <input
                  v-model="form.guest_count"
                  type="number"
                  min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="100"
                />
              </div>
            </div>
          </div>

          <!-- Step 4: Story (Optional) -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">4. Our Story (Optional)</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">How We Met</label>
                <textarea
                  v-model="form.how_we_met"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="Share your love story..."
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">The Proposal</label>
                <textarea
                  v-model="form.proposal_story"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="How did the proposal happen..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Step 5: Images -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">5. Images</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Hero Image -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                <div 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition-colors"
                  @click="openMediaLibrary('hero')"
                >
                  <div v-if="heroPreview" class="mb-3">
                    <img :src="heroPreview" class="max-h-40 mx-auto rounded-lg object-cover" alt="Hero preview" />
                  </div>
                  <div v-else class="py-6">
                    <PhotoIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                  </div>
                  <button
                    type="button"
                    class="text-purple-600 hover:text-purple-700 text-sm font-medium"
                  >
                    {{ heroPreview ? 'Change Image' : 'Select Hero Image' }}
                  </button>
                  <p class="text-xs text-gray-500 mt-1">Main couple photo (max 5MB)</p>
                </div>
              </div>

              <!-- Story Image -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Story Image</label>
                <div 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition-colors"
                  @click="openMediaLibrary('story')"
                >
                  <div v-if="storyPreview" class="mb-3">
                    <img :src="storyPreview" class="max-h-40 mx-auto rounded-lg object-cover" alt="Story preview" />
                  </div>
                  <div v-else class="py-6">
                    <PhotoIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                  </div>
                  <button
                    type="button"
                    class="text-purple-600 hover:text-purple-700 text-sm font-medium"
                  >
                    {{ storyPreview ? 'Change Image' : 'Select Story Image' }}
                  </button>
                  <p class="text-xs text-gray-500 mt-1">Secondary photo (max 5MB)</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="flex items-center justify-end gap-4">
            <Link
              :href="route('admin.weddings.index')"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex items-center px-6 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors"
            >
              <span v-if="processing">Creating...</span>
              <span v-else>Create Wedding Card</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Media Library Modal -->
    <WeddingMediaLibraryModal
      :show="showMediaLibrary"
      :media-library="mediaLibrary"
      :uploading="uploadingMedia"
      :upload-error="mediaUploadError"
      :allow-crop="true"
      :aspect-ratio="mediaTarget === 'hero' ? 4/3 : 1"
      @close="showMediaLibrary = false"
      @upload="handleMediaUpload"
      @select="handleMediaSelect"
      @select-cropped="handleMediaSelectCropped"
      @select-stock-photo="handleStockPhotoSelect"
      @delete="handleMediaDelete"
    />

    <!-- Template Preview Modal -->
    <WeddingTemplatePreviewModal
      :show="showTemplatePreview"
      :template="previewTemplate"
      :groom-name="form.groom_name"
      :bride-name="form.bride_name"
      :wedding-date="form.wedding_date"
      @close="showTemplatePreview = false"
      @select="selectPreviewedTemplate"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon, HeartIcon, CheckCircleIcon, EyeIcon, PhotoIcon } from '@heroicons/vue/24/outline'
import AdminLayout from '@/layouts/AdminLayout.vue'
import WeddingMediaLibraryModal from '@/components/Wedding/WeddingMediaLibraryModal.vue'
import WeddingTemplatePreviewModal from '@/components/Wedding/WeddingTemplatePreviewModal.vue'
import axios from 'axios'

interface Template {
  id: number
  name: string
  slug: string
  description: string
  preview_image: string
  settings: {
    colors?: { primary?: string }
  }
  is_premium: boolean
}

interface MediaItem {
  id: number | string
  url: string
  thumbnailUrl?: string
  originalName: string
}

const props = defineProps<{
  templates: Template[]
}>()

const form = useForm({
  template_id: null as number | null,
  groom_name: '',
  bride_name: '',
  wedding_date: '',
  venue_name: '',
  venue_location: '',
  ceremony_time: '11:00 AM',
  reception_time: '2:00 PM',
  reception_venue: '',
  reception_address: '',
  dress_code: 'Formal Attire',
  rsvp_deadline: '',
  guest_count: 100,
  how_we_met: '',
  proposal_story: '',
  hero_image: null as File | null,
  story_image: null as File | null,
  hero_image_url: '',
  story_image_url: '',
})

const errors = computed(() => form.errors)
const processing = computed(() => form.processing)

// Image previews
const heroPreview = ref<string | null>(null)
const storyPreview = ref<string | null>(null)

// Media library state
const showMediaLibrary = ref(false)
const mediaLibrary = ref<MediaItem[]>([])
const uploadingMedia = ref(false)
const mediaUploadError = ref<string | null>(null)
const mediaTarget = ref<'hero' | 'story'>('hero')

// Template preview state
const showTemplatePreview = ref(false)
const previewTemplate = ref<Template | null>(null)

const generatedSlug = computed(() => {
  if (!form.groom_name || !form.bride_name || !form.wedding_date) return ''
  const date = new Date(form.wedding_date)
  const month = date.toLocaleString('en-US', { month: 'short' }).toLowerCase()
  const year = date.getFullYear()
  return `${form.groom_name.toLowerCase()}-and-${form.bride_name.toLowerCase()}-${month}-${year}`.replace(/\s+/g, '-')
})

// Media Library Functions
const openMediaLibrary = (target: 'hero' | 'story') => {
  mediaTarget.value = target
  showMediaLibrary.value = true
}

const handleMediaUpload = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  uploadingMedia.value = true
  mediaUploadError.value = null

  try {
    const formData = new FormData()
    formData.append('image', file)

    const response = await axios.post('/api/wedding/media/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.media) {
      mediaLibrary.value.unshift(response.data.media)
      handleMediaSelect(response.data.media)
    }
  } catch (error: any) {
    mediaUploadError.value = error.response?.data?.message || 'Upload failed'
  } finally {
    uploadingMedia.value = false
  }
}

const handleMediaSelect = (media: MediaItem) => {
  if (mediaTarget.value === 'hero') {
    heroPreview.value = media.url
    form.hero_image_url = media.url
  } else {
    storyPreview.value = media.url
    form.story_image_url = media.url
  }
  showMediaLibrary.value = false
}

const handleMediaSelectCropped = (dataUrl: string, _originalMedia: MediaItem) => {
  if (mediaTarget.value === 'hero') {
    heroPreview.value = dataUrl
    // Convert data URL to blob for upload
    fetch(dataUrl)
      .then(res => res.blob())
      .then(blob => {
        form.hero_image = new File([blob], 'hero-cropped.jpg', { type: 'image/jpeg' })
      })
  } else {
    storyPreview.value = dataUrl
    fetch(dataUrl)
      .then(res => res.blob())
      .then(blob => {
        form.story_image = new File([blob], 'story-cropped.jpg', { type: 'image/jpeg' })
      })
  }
  showMediaLibrary.value = false
}

const handleStockPhotoSelect = (url: string, _attribution: string) => {
  if (mediaTarget.value === 'hero') {
    heroPreview.value = url
    form.hero_image_url = url
  } else {
    storyPreview.value = url
    form.story_image_url = url
  }
  showMediaLibrary.value = false
}

const handleMediaDelete = async (media: MediaItem) => {
  try {
    await axios.delete(`/api/wedding/media/${media.id}`)
    mediaLibrary.value = mediaLibrary.value.filter(m => m.id !== media.id)
  } catch (error) {
    console.error('Failed to delete media:', error)
  }
}

// Template Preview Functions
const openTemplatePreview = (template: Template) => {
  previewTemplate.value = template
  showTemplatePreview.value = true
}

const selectPreviewedTemplate = () => {
  if (previewTemplate.value) {
    form.template_id = previewTemplate.value.id
  }
  showTemplatePreview.value = false
}

const submit = () => {
  form.post(route('admin.weddings.store'), {
    forceFormData: true,
  })
}
</script>
