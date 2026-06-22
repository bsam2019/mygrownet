<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  existing: any;
}>();

const form = ref({
  business_name: '',
  license_number: '',
  phone: '',
  bio: '',
});

const submitting = ref(false);

const submit = () => {
  submitting.value = true;
  router.post(route('zamstay.agent.register'), form.value, {
    onFinish: () => { submitting.value = false; },
  });
};
</script>

<template>
  <Head title="Become a Tour Agent - ZamStay" />

  <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div v-if="existing" class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
      <p class="text-amber-800 font-medium">You are already registered as an agent.</p>
      <p class="text-amber-600 text-sm mt-1">Status: {{ existing.is_approved ? 'Approved' : 'Pending Approval' }}</p>
      <Link :href="route('zamstay.agent.dashboard')" class="mt-4 inline-flex px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">
        Go to Dashboard
      </Link>
    </div>

    <div v-else>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Become a Tour Agent</h1>
      <p class="text-gray-500 mb-8">Register as a ZamStay agent and earn commission on bookings.</p>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
          <input v-model="form.business_name" type="text" required
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">License Number (optional)</label>
          <input v-model="form.license_number" type="text"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
          <input v-model="form.phone" type="text" placeholder="+260 XXX XXX XXX"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bio (optional)</label>
          <textarea v-model="form.bio" rows="4"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
        </div>
        <button type="submit" :disabled="submitting"
          class="w-full px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50">
          {{ submitting ? 'Submitting...' : 'Register as Agent' }}
        </button>
      </form>
    </div>
  </div>
</template>
