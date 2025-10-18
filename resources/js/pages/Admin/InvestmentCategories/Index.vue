<script setup>
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Swal from 'sweetalert2'
import { formatKwacha } from '@/utils/format'

const page = usePage()

const props = defineProps({
  categories: Object
})

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: flash.success,
      timer: 2000,
      showConfirmButton: false
    })
  }
  if (flash?.error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: flash.error
    })
  }
}, { deep: true })

const form = ref({
  id: null,
  name: '',
  description: '',
  min_investment: 0,
  max_investment: null,
  interest_rate: 0,
  expected_roi: 0,
  lock_in_period: 0,
  is_active: false // Changed default to false
})

const showModal = ref(false)
const isEditing = ref(false)

const showAddModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const editCategory = (category) => {
  form.value = {
    id: category.id,
    name: category.name,
    description: category.description || '',
    min_investment: parseFloat(category.min_investment),
    max_investment: category.max_investment ? parseFloat(category.max_investment) : null,
    interest_rate: parseFloat(category.interest_rate),
    expected_roi: parseFloat(category.expected_roi),
    lock_in_period: parseInt(category.lock_in_period),
    is_active: Boolean(category.is_active)
  }
  isEditing.value = true
  showModal.value = true
}

const saveCategory = () => {
  if (isEditing.value) {
    router.put(route('admin.categories.update', form.value.id), form.value, {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Category updated successfully',
          timer: 2000,
          showConfirmButton: false
        })
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: Object.values(errors).flat().join('\n')
        })
      }
    })
  } else {
    router.post(route('admin.categories.store'), form.value, {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Category created successfully',
          timer: 2000,
          showConfirmButton: false
        })
      },
      onError: (errors) => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: Object.values(errors).flat().join('\n')
        })
      }
    })
  }
}

const deleteCategory = (id) => {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('admin.categories.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire(
            'Deleted!',
            'Category has been deleted.',
            'success'
          )
        },
        onError: () => {}
      })
    }
  })
}

const resetForm = () => {
  form.value = {
    id: null,
    name: '',
    description: '',
    min_investment: 0,
    max_investment: null,
    interest_rate: 0,
    expected_roi: 0,
    lock_in_period: 0,
    is_active: false // Changed default to false
  }
}
</script>

<template>
  <AdminLayout :breadcrumbs="[
    { name: 'Dashboard', href: route('admin.dashboard') },
    { name: 'Investment Categories', href: route('admin.categories.index') }
  ]">
    <div class="p-4">
      <div class="bg-white rounded-lg shadow">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-semibold">Investment Categories</h3>
          <button @click="showAddModal" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">
            Add New Category
          </button>
        </div>
        <div class="p-4">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min Investment</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Investment</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Interest Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expected ROI</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lock Period</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="category in categories.data" :key="category.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatKwacha(category.min_investment) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ category.max_investment ? formatKwacha(category.max_investment) : '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ category.interest_rate }}%</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ category.expected_roi }}%</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ category.lock_in_period }} months</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-2 py-1 text-xs rounded-full',
                      category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    ]">
                      {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap space-x-2">
                    <button @click="editCategory(category)" class="px-3 py-1 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
                      Edit
                    </button>
                    <button @click="deleteCategory(category.id)" class="px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end sm:items-center justify-center min-h-screen p-4 text-center sm:p-0">
          <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal"></div>

          <div class="relative bg-white rounded-t-lg sm:rounded-lg w-full max-w-lg transform transition-all">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-4 border-b">
              <h3 class="text-lg font-semibold">{{ isEditing ? 'Edit' : 'Add' }} Category</h3>
              <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                <span class="text-2xl">&times;</span>
              </button>
            </div>

            <!-- Modal Form -->
            <div class="p-4 max-h-[calc(100vh-12rem)] overflow-y-auto">
              <form @submit.prevent="saveCategory" class="space-y-4">
                <!-- Basic Info Section -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" v-model="form.name" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="form.description" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                  </div>
                </div>

                <!-- Investment Details Section -->
                <div class="space-y-4">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Min Investment</label>
                      <input type="number" v-model="form.min_investment" required min="0" step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Max Investment</label>
                      <input type="number" v-model="form.max_investment" min="0" step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%)</label>
                      <input type="number" v-model="form.interest_rate" required step="0.01" min="0" max="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Expected ROI (%)</label>
                      <input type="number" v-model="form.expected_roi" required step="0.01" min="0" max="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lock-in Period (months)</label>
                    <input type="number" v-model="form.lock_in_period" required min="0"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                  </div>

                  <div class="flex items-center">
                    <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-blue-600">
                    <label class="ml-2 text-sm text-gray-700">Active</label>
                  </div>
                </div>
              </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
              <button type="button" @click="closeModal"
                class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" @click="saveCategory"
                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">
                Save
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
