<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  branches: Array<{ id: number; name: string }>
  workers: Array<{ id: number; name: string }>
}

const props = defineProps<Props>()

const form = useForm({
  name: '',
  code: '',
  branch_id: null,
  manager_id: null,
  description: '',
})

const submit = () => {
  form.post(route('cms.departments.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-3xl mx-auto">
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.departments.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Departments
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Create Department</h1>
        <p class="mt-1 text-sm text-gray-500">Add a new department to your organization</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200">
        <div class="p-6 space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <FormInput
              v-model="form.name"
              label="Department Name"
              placeholder="e.g., Human Resources"
              required
              :error="form.errors.name"
            />

            <FormInput
              v-model="form.code"
              label="Department Code"
              placeholder="e.g., HR"
              :error="form.errors.code"
              help-text="Short code for identification"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <FormSelect
              v-model="form.branch_id"
              label="Branch"
              :options="branches.map(b => ({ value: b.id, label: b.name }))"
              :error="form.errors.branch_id"
              help-text="Optional - assign to a branch"
            />

            <FormSelect
              v-model="form.manager_id"
              label="Department Manager"
              :options="workers.map(w => ({ value: w.id, label: w.name }))"
              :error="form.errors.manager_id"
              help-text="Optional - assign a manager"
            />
          </div>

          <FormInput
            v-model="form.description"
            label="Description"
            type="textarea"
            :rows="4"
            placeholder="Brief description of the department's role and responsibilities"
            :error="form.errors.description"
          />
        </div>

        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.departments.index'))"
            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
          >
            {{ form.processing ? 'Creating...' : 'Create Department' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
