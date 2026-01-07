<template>
  <Head :title="`Edit - ${wedding.groom_name} & ${wedding.bride_name}`" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <Link :href="route('admin.weddings.index')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
            <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
            Back to Wedding Cards
          </Link>
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">{{ wedding.groom_name }} & {{ wedding.bride_name }}</h1>
              <p class="mt-1 text-sm text-gray-500">Edit wedding card details</p>
            </div>
            <div class="flex items-center gap-3">
              <a
                :href="`/wedding/${wedding.slug}`"
                target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                <EyeIcon class="h-4 w-4" aria-hidden="true" />
                Preview
              </a>
              <button
                @click="togglePublish"
                :class="[
                  'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                  wedding.is_published 
                    ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' 
                    : 'bg-green-600 text-white hover:bg-green-700'
                ]"
              >
                {{ wedding.is_published ? 'Unpublish' : 'Publish' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Status Banner -->
        <div 
          :class="[
            'mb-6 p-4 rounded-lg flex items-center justify-between',
            wedding.is_published ? 'bg-green-50 border border-green-200' : 'bg-amber-50 border border-amber-200'
          ]"
        >
          <div class="flex items-center gap-3">
            <div :class="['w-3 h-3 rounded-full', wedding.is_published ? 'bg-green-500' : 'bg-amber-500']"></div>
            <div>
              <div class="font-medium" :class="wedding.is_published ? 'text-green-800' : 'text-amber-800'">
                {{ wedding.is_published ? 'Published' : 'Draft' }}
              </div>
              <div class="text-sm" :class="wedding.is_published ? 'text-green-600' : 'text-amber-600'">
                {{ wedding.is_published ? `Live at /wedding/${wedding.slug}` : 'Not visible to public' }}
              </div>
            </div>
          </div>
          <div class="text-sm text-gray-500">
            Access Code: <span class="font-mono font-bold">{{ wedding.access_code }}</span>
            <button @click="regenerateCode" class="ml-2 text-purple-600 hover:text-purple-700 text-xs">
              Regenerate
            </button>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
          <!-- Template Selection -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Template</h2>
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
                    class="h-16 rounded-md mb-2 flex items-center justify-center"
                    :style="{ backgroundColor: template.settings?.colors?.primary || '#9333ea' }"
                  >
                    <HeartIcon class="h-6 w-6 text-white" aria-hidden="true" />
                  </div>
                  <div class="font-medium text-gray-900 text-sm">{{ template.name }}</div>
                  <CheckCircleIcon 
                    v-if="form.template_id === template.id"
                    class="absolute top-2 left-2 h-5 w-5 text-purple-600"
                    aria-hidden="true"
                  />
                </button>
                <!-- Preview Button -->
                <button
                  type="button"
                  @click.stop="openTemplatePreview(template)"
                  class="absolute bottom-12 right-2 p-1 bg-white/90 rounded-full shadow hover:bg-white transition-colors"
                  title="Preview template"
                >
                  <MagnifyingGlassIcon class="h-3.5 w-3.5 text-gray-600" aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>

          <!-- Couple Details -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Couple Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Groom's Name *</label>
                <input v-model="form.groom_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Bride's Name *</label>
                <input v-model="form.bride_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
            </div>
            <div class="mt-4 p-3 bg-gray-50 rounded-md">
              <div class="text-sm text-gray-500">Wedding URL:</div>
              <div class="text-sm font-mono text-purple-600">/wedding/{{ wedding.slug }}</div>
            </div>
          </div>

          <!-- Wedding Details -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Wedding Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Wedding Date *</label>
                <input v-model="form.wedding_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">RSVP Deadline</label>
                <input v-model="form.rsvp_deadline" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Venue Name *</label>
                <input v-model="form.venue_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Venue Address *</label>
                <input v-model="form.venue_location" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Ceremony Time</label>
                <input v-model="form.ceremony_time" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Reception Time</label>
                <input v-model="form.reception_time" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Dress Code</label>
                <input v-model="form.dress_code" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Expected Guests</label>
                <input v-model="form.guest_count" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" />
              </div>
            </div>
          </div>

          <!-- Story -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Our Story</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">How We Met</label>
                <textarea v-model="form.how_we_met" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">The Proposal</label>
                <textarea v-model="form.proposal_story" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
              </div>
            </div>
          </div>

          <!-- Images -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Images</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Hero Image -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                <div 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition-colors"
                  @click="openMediaLibrary('hero')"
                >
                  <div v-if="heroPreview || wedding.hero_image" class="mb-3">
                    <img 
                      :src="heroPreview || wedding.hero_image" 
                      class="max-h-40 mx-auto rounded-lg object-cover" 
                      alt="Hero preview" 
                    />
                  </div>
                  <div v-else class="py-6">
                    <PhotoIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                  </div>
                  <button
                    type="button"
                    class="text-purple-600 hover:text-purple-700 text-sm font-medium"
                  >
                    {{ heroPreview || wedding.hero_image ? 'Change Image' : 'Select Hero Image' }}
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
                  <div v-if="storyPreview || wedding.story_image" class="mb-3">
                    <img 
                      :src="storyPreview || wedding.story_image" 
                      class="max-h-40 mx-auto rounded-lg object-cover" 
                      alt="Story preview" 
                    />
                  </div>
                  <div v-else class="py-6">
                    <PhotoIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                  </div>
                  <button
                    type="button"
                    class="text-purple-600 hover:text-purple-700 text-sm font-medium"
                  >
                    {{ storyPreview || wedding.story_image ? 'Change Image' : 'Select Story Image' }}
                  </button>
                  <p class="text-xs text-gray-500 mt-1">Secondary photo (max 5MB)</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="flex items-center justify-end gap-4">
            <Link :href="route('admin.weddings.index')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Cancel</Link>
            <button type="submit" :disabled="processing" class="inline-flex items-center px-6 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors">
              <span v-if="processing">Saving...</span>
              <span v-else>Save Changes</span>
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
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon, HeartIcon, CheckCircleIcon, EyeIcon, PhotoIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import AdminLayout from '@/layouts/AdminLayout.vue'
import WeddingMediaLibraryModal from '@/components/Wedding/WeddingMediaLibraryModal.vue'
import WeddingTemplatePreviewModal from '@/components/Wedding/WeddingTemplatePreviewModal.vue'
import axios from 'axios'

interface Template {
  id: number
  name: string
  slug: string
  settings: { colors?: { primary?: string } }
}

interface MediaItem {
  id: number | string
  url: string
  thumbnailUrl?: string
  originalName: string
}

interface Wedding {
  id: number
  template_id: number | null
  groom_name: string
  bride_name: string
  slug: string
  wedding_date: string
  venue_name: string
  venue_location: string
  ceremony_time: string
  reception_time: string
  dress_code: string
  rsvp_deadline: string
  guest_count: number
  how_we_met: string
  proposal_story: string
  hero_image: string | null
  story_image: string | null
  access_code: string
  is_published: boolean
}

const props = defineProps<{ wedding: Wedding; templates: Template[] }>()

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

const form = useForm({
  template_id: props.wedding.template_id,
  groom_name: props.wedding.groom_name,
  bride_name: props.wedding.bride_name,
  wedding_date: props.wedding.wedding_date,
  venue_name: props.wedding.venue_name,
  venue_location: props.wedding.venue_location,
  ceremony_time: props.wedding.ceremony_time,
  reception_time: props.wedding.reception_time,
  dress_code: props.wedding.dress_code,
  rsvp_deadline: props.wedding.rsvp_deadline,
  guest_count: props.wedding.guest_count,
  how_we_met: props.wedding.how_we_met,
  proposal_story: props.wedding.proposal_story,
  hero_image: null as File | null,
  story_image: null as File | null,
  hero_image_url: '',
  story_image_url: '',
})

const processing = computed(() => form.processing)

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
  form.post(route('admin.weddings.update', props.wedding.id), {
    forceFormData: true,
    _method: 'PUT',
  })
}

const togglePublish = () => {
  router.post(route('admin.weddings.toggle-publish', props.wedding.id))
}

const regenerateCode = () => {
  router.post(route('admin.weddings.regenerate-code', props.wedding.id))
}
</script>
