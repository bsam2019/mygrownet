<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const form = useForm({
  name: '',
  supervisor_id: null,
  specialization: '',
  members: [] as Array<{ employee_id: number | null; role: string }>,
});

const addMember = () => {
  form.members.push({ employee_id: null, role: '' });
};

const removeMember = (index: number) => {
  form.members.splice(index, 1);
};

const submit = () => {
  form.post(route('cms.labour.crews.store'));
};
</script>

<template>
  <Head title="Create Crew" />
  
  <CMSLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('cms.labour.crews.index')" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Create Crew</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Crew Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Crew Name <span class="text-red-500">*</span>
              </label>
              <input v-model="form.name" type="text" required class="w-full rounded-lg border-gray-300" placeholder="e.g., Construction Team A" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Specialization</label>
              <input v-model="form.specialization" type="text" class="w-full rounded-lg border-gray-300" placeholder="e.g., Masonry, Carpentry" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Crew Members</h2>
            <button type="button" @click="addMember" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">
              <PlusIcon class="h-4 w-4" aria-hidden="true" />
              Add Member
            </button>
          </div>

          <div v-if="form.members.length === 0" class="text-center py-8 text-gray-500">
            No members added yet
          </div>

          <div v-else class="space-y-3">
            <div v-for="(member, index) in form.members" :key="index" class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
              <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                  <input v-model="member.employee_id" type="number" placeholder="Employee ID" class="w-full rounded-lg border-gray-300" required />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                  <input v-model="member.role" type="text" placeholder="e.g., Foreman, Worker" class="w-full rounded-lg border-gray-300" required />
                </div>
              </div>
              <button type="button" @click="removeMember(index)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg mt-6">
                <TrashIcon class="h-5 w-5" aria-hidden="true" />
              </button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('cms.labour.crews.index')" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create Crew' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>
