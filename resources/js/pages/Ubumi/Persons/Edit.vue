<template>
  <UbumiLayout title="Edit Family Member">
    <div class="max-w-3xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link
          :href="route('ubumi.families.show', { family: person.family_slug })"
          class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 mb-4"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Family Members
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Edit Family Member</h1>
        <p class="mt-1 text-sm text-gray-600">
          Update information for this family member
        </p>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form @submit.prevent="submit">
          <!-- Name -->
          <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
              :class="{ 'border-red-500': form.errors.name }"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Photo Upload -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Photo
            </label>
            
            <!-- Photo Preview or Upload Area -->
            <div v-if="photoPreview || form.photo_url" class="mb-4">
              <div class="relative inline-block">
                <img
                  :src="photoPreview || form.photo_url"
                  alt="Person photo"
                  class="h-32 w-32 rounded-full object-cover border-4 border-emerald-100"
                />
                <button
                  type="button"
                  @click="removePhoto"
                  class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 shadow-lg"
                  aria-label="Remove photo"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Upload Button -->
            <div v-else class="flex items-center justify-center w-full">
              <div class="w-full border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-emerald-400 transition-colors">
                <UserIcon class="mx-auto h-12 w-12 text-gray-400 mb-3" aria-hidden="true" />
                <MediaUploadButton
                  :endpoint="route('ubumi.photos.upload')"
                  variant="dashed"
                  size="md"
                  @success="handlePhotoUpload"
                  @error="handlePhotoError"
                >
                  <template #default="{ uploading }">
                    {{ uploading ? 'Uploading...' : 'Upload Photo' }}
                  </template>
                </MediaUploadButton>
                <p class="mt-2 text-xs text-gray-500">
                  PNG, JPG, GIF up to 5MB
                </p>
              </div>
            </div>

            <p v-if="form.errors.photo_url" class="mt-2 text-sm text-red-600">
              {{ form.errors.photo_url }}
            </p>
          </div>

          <!-- Date of Birth or Approximate Age -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Age Information
            </label>
            <div class="space-y-4">
              <div>
                <label for="date_of_birth" class="block text-sm text-gray-600 mb-1">
                  Date of Birth
                </label>
                <input
                  id="date_of_birth"
                  v-model="form.date_of_birth"
                  type="date"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                  :class="{ 'border-red-500': form.errors.date_of_birth }"
                />
                <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">
                  {{ form.errors.date_of_birth }}
                </p>
              </div>

              <div class="text-center text-sm text-gray-500">OR</div>

              <div>
                <label for="approximate_age" class="block text-sm text-gray-600 mb-1">
                  Approximate Age (years)
                </label>
                <input
                  id="approximate_age"
                  v-model.number="form.approximate_age"
                  type="number"
                  min="0"
                  max="150"
                  placeholder="e.g., 45"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                  :class="{ 'border-red-500': form.errors.approximate_age }"
                />
                <p v-if="form.errors.approximate_age" class="mt-1 text-sm text-red-600">
                  {{ form.errors.approximate_age }}
                </p>
              </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
              Provide either exact date of birth or approximate age
            </p>
          </div>

          <!-- Gender -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Gender
            </label>
            <div class="flex gap-4">
              <label class="flex items-center">
                <input
                  v-model="form.gender"
                  type="radio"
                  value="male"
                  class="h-4 w-4 text-emerald-600 focus:ring-emerald-500"
                />
                <span class="ml-2 text-sm text-gray-700">Male</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.gender"
                  type="radio"
                  value="female"
                  class="h-4 w-4 text-emerald-600 focus:ring-emerald-500"
                />
                <span class="ml-2 text-sm text-gray-700">Female</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.gender"
                  type="radio"
                  :value="null"
                  class="h-4 w-4 text-emerald-600 focus:ring-emerald-500"
                />
                <span class="ml-2 text-sm text-gray-700">Prefer not to say</span>
              </label>
            </div>
            <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">
              {{ form.errors.gender }}
            </p>
          </div>

          <!-- Is Deceased -->
          <div class="mb-6">
            <label class="flex items-center">
              <input
                v-model="form.is_deceased"
                type="checkbox"
                class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 rounded"
              />
              <span class="ml-2 text-sm text-gray-700">This person is deceased</span>
            </label>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <Link
              :href="route('ubumi.families.show', { family: person.family_slug })"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </UbumiLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import UbumiLayout from '@/layouts/UbumiLayout.vue';
import MediaUploadButton from '@/components/MediaUploadButton.vue';
import { ArrowLeftIcon, UserIcon } from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';

interface Person {
  id: string;
  slug: string;
  family_id: number;
  family_slug: string;
  name: string;
  photo_url: string | null;
  date_of_birth: string | null;
  approximate_age: number | null;
  gender: 'male' | 'female' | null;
  is_deceased: boolean;
}

interface Props {
  person: Person;
}

const props = defineProps<Props>();

const form = useForm({
  name: props.person.name,
  photo_url: props.person.photo_url || '',
  date_of_birth: props.person.date_of_birth || '',
  approximate_age: props.person.approximate_age || null,
  gender: props.person.gender,
  is_deceased: props.person.is_deceased,
});

const photoPreview = ref<string | null>(props.person.photo_url);

const handlePhotoUpload = (url: string) => {
  form.photo_url = url;
  photoPreview.value = url;
  toast.success('Photo uploaded', 'Profile photo updated successfully');
};

const handlePhotoError = (error: string) => {
  console.error('Photo upload error:', error);
  toast.error('Upload failed', error);
};

const removePhoto = () => {
  form.photo_url = '';
  photoPreview.value = null;
  toast.info('Photo removed', 'Remember to save changes');
};

const submit = () => {
  form.put(route('ubumi.families.persons.update', { family: props.person.family_slug, person: props.person.slug }), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Changes saved', 'Family member updated successfully');
    },
    onError: () => {
      toast.error('Save failed', 'Please check the form and try again');
    },
  });
};
</script>
