<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
              <form @submit.prevent="saveAsset">
                <div>
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                    <BuildingOffice2Icon class="h-6 w-6 text-blue-600" />
                  </div>
                  <div class="mt-3 text-center sm:mt-5">
                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                      {{ asset ? 'Edit Asset' : 'Create New Asset' }}
                    </DialogTitle>
                  </div>
                </div>

                <div class="mt-6 space-y-4">
                  <!-- Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Asset name"
                    />
                    <div v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</div>
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                      v-model="form.description"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Asset description"
                    />
                    <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
                  </div>

                  <!-- Category -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select
                      v-model="form.category"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                      <option value="">Select category</option>
                      <option value="property">Property</option>
                      <option value="vehicle">Vehicle</option>
                      <option value="equipment">Equipment</option>
                      <option value="investment">Investment</option>
                      <option value="other">Other</option>
                    </select>
                    <div v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category }}</div>
                  </div>

                  <!-- Value -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Value (Kwacha)</label>
                    <input
                      v-model.number="form.value"
                      type="number"
                      min="0"
                      step="0.01"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="0.00"
                    />
                    <div v-if="errors.value" class="mt-1 text-sm text-red-600">{{ errors.value }}</div>
                  </div>

                  <!-- Location -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input
                      v-model="form.location"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Asset location"
                    />
                    <div v-if="errors.location" class="mt-1 text-sm text-red-600">{{ errors.location }}</div>
                  </div>

                  <!-- Tier Requirements -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Minimum Tier Requirement</label>
                    <select
                      v-model="form.minimum_tier"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                      <option value="">No requirement</option>
                      <option value="bronze">Bronze</option>
                      <option value="silver">Silver</option>
                      <option value="gold">Gold</option>
                      <option value="diamond">Diamond</option>
                      <option value="elite">Elite</option>
                    </select>
                    <div v-if="errors.minimum_tier" class="mt-1 text-sm text-red-600">{{ errors.minimum_tier }}</div>
                  </div>

                  <!-- Team Volume Requirement -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Team Volume Requirement (Kwacha)</label>
                    <input
                      v-model.number="form.team_volume_requirement"
                      type="number"
                      min="0"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="0"
                    />
                    <div v-if="errors.team_volume_requirement" class="mt-1 text-sm text-red-600">{{ errors.team_volume_requirement }}</div>
                  </div>

                  <!-- Maintenance Period -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Maintenance Period (months)</label>
                    <input
                      v-model.number="form.maintenance_period_months"
                      type="number"
                      min="1"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="12"
                    />
                    <div v-if="errors.maintenance_period_months" class="mt-1 text-sm text-red-600">{{ errors.maintenance_period_months }}</div>
                  </div>
                </div>

                <div class="mt-6 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                  <button
                    type="submit"
                    :disabled="processing"
                    class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50 sm:col-start-2"
                  >
                    <span v-if="processing">Saving...</span>
                    <span v-else>{{ asset ? 'Update' : 'Create' }}</span>
                  </button>
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { BuildingOffice2Icon } from '@heroicons/vue/24/outline'

interface Props {
  show: boolean
  asset?: any
}

const props = defineProps<Props>()
const emit = defineEmits(['close', 'saved'])

const processing = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  description: '',
  category: '',
  value: 0,
  location: '',
  minimum_tier: '',
  team_volume_requirement: 0,
  maintenance_period_months: 12
})

watch(() => props.asset, (newAsset) => {
  if (newAsset) {
    Object.assign(form, {
      name: newAsset.name || '',
      description: newAsset.description || '',
      category: newAsset.category || '',
      value: newAsset.value || 0,
      location: newAsset.location || '',
      minimum_tier: newAsset.minimum_tier || '',
      team_volume_requirement: newAsset.team_volume_requirement || 0,
      maintenance_period_months: newAsset.maintenance_period_months || 12
    })
  } else {
    // Reset form for new asset
    Object.assign(form, {
      name: '',
      description: '',
      category: '',
      value: 0,
      location: '',
      minimum_tier: '',
      team_volume_requirement: 0,
      maintenance_period_months: 12
    })
  }
}, { immediate: true })

const saveAsset = async () => {
  processing.value = true
  errors.value = {}

  try {
    const url = props.asset 
      ? route('admin.assets.update-asset', props.asset.id)
      : route('admin.assets.create-asset')
    
    const method = props.asset ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(form)
    })

    const data = await response.json()

    if (data.success) {
      emit('saved')
    } else {
      if (data.errors) {
        errors.value = data.errors
      } else {
        alert('Failed to save asset: ' + data.message)
      }
    }
  } catch (error) {
    console.error('Error saving asset:', error)
    alert('An error occurred while saving the asset')
  } finally {
    processing.value = false
  }
}
</script>