<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
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
              <form @submit.prevent="submit">
                <div>
                  <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 mb-4">
                    Add Material to Job
                  </DialogTitle>

                  <div class="space-y-4">
                    <!-- Quick Filters -->
                    <div class="bg-gray-50 rounded-lg p-3 space-y-3">
                      <div class="flex items-center gap-2 mb-2">
                        <FunnelIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                        <span class="text-sm font-medium text-gray-700">Quick Filters</span>
                        <button
                          v-if="searchQuery || selectedCategory"
                          type="button"
                          @click="clearFilters"
                          class="ml-auto text-xs text-blue-600 hover:text-blue-700"
                        >
                          Clear
                        </button>
                      </div>
                      
                      <!-- Search -->
                      <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" aria-hidden="true" />
                        <input
                          v-model="searchQuery"
                          type="text"
                          placeholder="Search by name or code..."
                          class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        />
                      </div>

                      <!-- Category Filter -->
                      <div class="flex items-center gap-2">
                        <select
                          v-model="selectedCategory"
                          class="flex-1 text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                          <option value="">All Categories</option>
                          <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                          </option>
                        </select>
                        <span class="text-xs text-gray-500 whitespace-nowrap">
                          {{ filteredMaterials.length }} of {{ materials.length }}
                        </span>
                      </div>
                    </div>

                    <!-- Material Selection -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Material <span class="text-red-500">*</span>
                      </label>
                      <select
                        v-model="form.material_id"
                        required
                        @change="onMaterialChange"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.material_id }"
                      >
                        <option value="">Select Material</option>
                        <optgroup v-if="filteredMaterials.length === 0" label="No materials found">
                          <option disabled>Try adjusting your filters</option>
                        </optgroup>
                        <option v-for="material in filteredMaterials" :key="material.id" :value="material.id">
                          {{ material.name }} ({{ material.code }}) - K{{ material.current_price }}/{{ material.unit }}
                        </option>
                      </select>
                      <p v-if="form.errors.material_id" class="mt-1 text-sm text-red-600">{{ form.errors.material_id }}</p>
                    </div>

                    <!-- Selected Material Info -->
                    <div v-if="selectedMaterial" class="bg-blue-50 rounded-lg p-3">
                      <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Current Price:</span>
                        <span class="font-medium text-gray-900">K{{ selectedMaterial.current_price }}/{{ selectedMaterial.unit }}</span>
                      </div>
                      <div v-if="selectedMaterial.supplier" class="flex items-center justify-between text-sm mt-1">
                        <span class="text-gray-600">Supplier:</span>
                        <span class="text-gray-900">{{ selectedMaterial.supplier }}</span>
                      </div>
                    </div>

                    <!-- Planned Quantity -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Planned Quantity <span class="text-red-500">*</span>
                      </label>
                      <div class="flex items-center space-x-2">
                        <input
                          v-model="form.planned_quantity"
                          type="number"
                          step="0.01"
                          min="0.01"
                          required
                          placeholder="0.00"
                          class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                          :class="{ 'border-red-500': form.errors.planned_quantity }"
                        />
                        <span v-if="selectedMaterial" class="text-sm text-gray-500">{{ selectedMaterial.unit }}</span>
                      </div>
                      <p v-if="form.errors.planned_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.planned_quantity }}</p>
                    </div>

                    <!-- Unit Price -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Unit Price (K)
                      </label>
                      <input
                        v-model="form.unit_price"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      />
                      <p class="mt-1 text-xs text-gray-500">Leave blank to use current price</p>
                    </div>

                    <!-- Wastage Percentage -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Wastage Percentage
                      </label>
                      <div class="flex items-center space-x-2">
                        <input
                          v-model="form.wastage_percentage"
                          type="number"
                          step="0.1"
                          min="0"
                          max="100"
                          placeholder="0"
                          class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-500">%</span>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Typical: 5-10% for profiles, 3-5% for glass</p>
                    </div>

                    <!-- Notes -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                      <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Optional notes about this material"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      ></textarea>
                    </div>

                    <!-- Estimated Cost -->
                    <div v-if="estimatedCost > 0" class="bg-green-50 rounded-lg p-3">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Estimated Cost:</span>
                        <span class="text-lg font-bold text-green-700">K{{ estimatedCost.toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                  >
                    <span v-if="form.processing">Adding...</span>
                    <span v-else>Add Material</span>
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

<script setup>
import { computed, watch, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: Boolean,
  job: Object,
  materials: Array,
})

const emit = defineEmits(['close', 'added'])

// Filter states
const searchQuery = ref('')
const selectedCategory = ref('')

// Get unique categories from materials
const categories = computed(() => {
  const cats = new Set()
  props.materials.forEach(m => {
    if (m.category?.name) {
      cats.add(JSON.stringify({ id: m.category.id, name: m.category.name }))
    }
  })
  return Array.from(cats).map(c => JSON.parse(c)).sort((a, b) => a.name.localeCompare(b.name))
})

// Filtered materials based on search and category
const filteredMaterials = computed(() => {
  let filtered = props.materials

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(m => 
      m.name.toLowerCase().includes(query) || 
      m.code.toLowerCase().includes(query)
    )
  }

  // Filter by category
  if (selectedCategory.value) {
    filtered = filtered.filter(m => m.category?.id === parseInt(selectedCategory.value))
  }

  return filtered
})

const form = useForm({
  material_id: '',
  planned_quantity: '',
  unit_price: '',
  wastage_percentage: 0,
  notes: '',
})

const selectedMaterial = computed(() => {
  if (!form.material_id) return null
  return props.materials.find(m => m.id === parseInt(form.material_id))
})

const estimatedCost = computed(() => {
  if (!form.planned_quantity || !selectedMaterial.value) return 0
  const price = form.unit_price || selectedMaterial.value.current_price
  return parseFloat(form.planned_quantity) * parseFloat(price)
})

const onMaterialChange = () => {
  if (selectedMaterial.value) {
    form.unit_price = selectedMaterial.value.current_price
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
}

const submit = () => {
  form.post(route('cms.jobs.materials.store', props.job.id), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      clearFilters()
      emit('added')
    },
  })
}

watch(() => props.show, (newVal) => {
  if (!newVal) {
    form.reset()
    form.clearErrors()
    clearFilters()
  }
})
</script>
