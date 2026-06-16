<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  property: any;
}>();

const isEditing = !!props.property;

const form = ref({
  title: props.property?.title || '',
  description: props.property?.description || '',
  location: props.property?.location || '',
  price_per_night: props.property?.price_per_night || '',
  property_type: props.property?.property_type || 'hotel',
  max_guests: props.property?.max_guests || 1,
  bedrooms: props.property?.bedrooms || 1,
  bathrooms: props.property?.bathrooms || 1,
  is_active: props.property?.is_active ?? true,
  latitude: props.property?.latitude || '',
  longitude: props.property?.longitude || '',
  images: props.property?.images || [] as string[],
  amenities: props.property?.amenities || [] as string[],
});

const newAmenity = ref('');
const uploading = ref(false);
const submitting = ref(false);

const addAmenity = () => {
  const val = newAmenity.value.trim();
  if (val && !form.value.amenities.includes(val)) {
    form.value.amenities.push(val);
  }
  newAmenity.value = '';
};

const removeAmenity = (i: number) => {
  form.value.amenities.splice(i, 1);
};

const uploadImage = async (e: Event) => {
  const input = e.target as HTMLInputElement;
  if (!input.files?.length) return;
  uploading.value = true;
  const fd = new FormData();
  fd.append('image', input.files[0]);
  try {
    const res = await fetch(route('zamstay.host.upload-image'), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content || '' },
      body: fd,
    });
    const data = await res.json();
    if (data.url) form.value.images.push(data.url);
  } finally {
    uploading.value = false;
    input.value = '';
  }
};

const removeImage = (i: number) => {
  form.value.images.splice(i, 1);
};

const submit = () => {
  submitting.value = true;
  const data = {
    ...form.value,
    price_per_night: Number(form.value.price_per_night),
    latitude: form.value.latitude ? Number(form.value.latitude) : undefined,
    longitude: form.value.longitude ? Number(form.value.longitude) : undefined,
    amenities: form.value.amenities.length ? form.value.amenities : undefined,
    images: form.value.images.length ? form.value.images : undefined,
  };

  if (isEditing) {
    router.put(route('zamstay.host.properties.update', props.property.id), data, {
      preserveScroll: true,
      onFinish: () => { submitting.value = false; },
    });
  } else {
    router.post(route('zamstay.host.properties.store'), data, {
      preserveScroll: true,
      onFinish: () => { submitting.value = false; },
    });
  }
};
</script>

<template>
  <Head :title="(isEditing ? 'Edit' : 'Add') + ' Property - Travel Zambia'" />

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('zamstay.host.properties')" class="inline-flex items-center text-sm text-gray-500 hover:text-emerald-600 mb-6">
      &larr; Back to properties
    </Link>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">{{ isEditing ? 'Edit Property' : 'Add New Property' }}</h1>

    <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
        <input v-model="form.title" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
        <textarea v-model="form.description" rows="4" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
          <input v-model="form.location" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Property Type *</label>
          <select v-model="form.property_type" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
            <option value="hotel">Hotel</option>
            <option value="lodge">Lodge</option>
            <option value="guest_house">Guest House</option>
            <option value="home_stay">Home Stay</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
          <input v-model.number="form.latitude" type="number" step="any" placeholder="-15.3875" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
          <input v-model.number="form.longitude" type="number" step="any" placeholder="28.3228" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
        </div>
      </div>
      <p class="text-xs text-gray-400 -mt-3">Coordinates for map display. Use <a href="https://www.latlong.net/" target="_blank" class="text-emerald-600 underline">latlong.net</a> to find them.</p>

      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Price/Night (ZMW) *</label>
          <input v-model.number="form.price_per_night" type="number" min="0" step="0.01" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Max Guests *</label>
          <input v-model.number="form.max_guests" type="number" min="1" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bedrooms</label>
          <input v-model.number="form.bedrooms" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bathrooms</label>
          <input v-model.number="form.bathrooms" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
        </div>
        <div v-if="isEditing" class="flex items-center pt-6">
          <label class="flex items-center gap-2">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
            <span class="text-sm text-gray-700">Property is active</span>
          </label>
        </div>
      </div>

      <!-- Amenities -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Amenities</label>
        <div class="flex gap-2 mb-2">
          <input v-model="newAmenity" type="text" placeholder="e.g. Free WiFi" class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" @keyup.enter="addAmenity" />
          <button type="button" @click="addAmenity" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-xl">Add</button>
        </div>
        <div class="flex flex-wrap gap-2">
          <span v-for="(amenity, i) in form.amenities" :key="i" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-sm">
            {{ amenity }}
            <button type="button" @click="removeAmenity(i)" class="text-emerald-400 hover:text-emerald-600">&times;</button>
          </span>
        </div>
      </div>

      <!-- Images -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Photos</label>
        <div class="flex flex-wrap gap-2 mb-2">
          <div v-for="(img, i) in form.images" :key="i" class="relative w-20 h-20 rounded-xl overflow-hidden border border-gray-200">
            <img :src="img" class="w-full h-full object-cover" />
            <button type="button" @click="removeImage(i)" class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white text-xs flex items-center justify-center rounded-bl-xl">&times;</button>
          </div>
          <label class="w-20 h-20 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-emerald-400 text-gray-400 hover:text-emerald-500 text-sm">
            <span v-if="uploading">...</span>
            <span v-else>+</span>
            <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="uploadImage" />
          </label>
        </div>
      </div>

      <div class="pt-4 border-t border-gray-100">
        <button type="submit" :disabled="submitting" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 text-white font-semibold rounded-xl transition-colors">
          {{ submitting ? 'Saving...' : (isEditing ? 'Update Property' : 'Create Property') }}
        </button>
      </div>
    </form>
  </div>
</template>
