<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg mr-3">
            <BriefcaseIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Position Management</h3>
            <p class="text-sm text-gray-500">Manage job positions and organizational roles</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button
            v-if="canCreate"
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Position
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b border-gray-100 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search positions..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm"
              @input="debouncedFilter"
            />
          </div>
        </div>

        <!-- Department Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select
            v-model="filters.department"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Positions Grid -->
    <div class="p-6">
      <div v-if="filteredPositions.length === 0" class="text-center py-12">
        <BriefcaseIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No positions found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hasFilters ? 'Try adjusting your search criteria.' : 'Get started by creating your first position.' }}
        </p>
        <div v-if="!hasFilters && canCreate" class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Position
          </button>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="position in filteredPositions"
          :key="position.id"
          class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
        >
          <!-- Position Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h4 class="text-lg font-medium text-gray-900 mb-1">{{ position.title }}</h4>
              <p class="text-sm text-gray-600">{{ position.department?.name || 'No Department' }}</p>
            </div>
            <div class="flex items-center space-x-2 ml-4">
              <button
                v-if="canEdit"
                @click="editPosition(position)"
                class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                title="Edit Position"
              >
                <PencilIcon class="h-4 w-4" />
              </button>
              <button
                v-if="canDelete && position.employee_count === 0"
                @click="confirmDelete(position)"
                class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                title="Delete Position"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
              <div
                v-else-if="canDelete"
                class="p-1 text-gray-300 cursor-not-allowed"
                title="Cannot delete position with employees"
              >
                <TrashIcon class="h-4 w-4" />
              </div>
            </div>
          </div>

          <!-- Position Details -->
          <div class="space-y-3">
            <!-- Status -->
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-500">Status</span>
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="position.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ position.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <!-- Employee Count -->
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-500">Employees</span>
              <span class="text-sm font-medium text-gray-900">{{ position.employee_count }}</span>
            </div>

            <!-- Salary Range -->
            <div v-if="position.min_salary || position.max_salary" class="flex items-center justify-between">
              <span class="text-sm text-gray-500">Salary Range</span>
              <span class="text-sm font-medium text-gray-900">
                {{ formatSalaryRange(position.min_salary, position.max_salary) }}
              </span>
            </div>

            <!-- Level -->
            <div v-if="position.level" class="flex items-center justify-between">
              <span class="text-sm text-gray-500">Level</span>
              <span class="text-sm font-medium text-gray-900">{{ position.level }}</span>
            </div>
          </div>

          <!-- Description -->
          <div v-if="position.description" class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-600 line-clamp-3">{{ position.description }}</p>
          </div>

          <!-- Requirements -->
          <div v-if="position.requirements?.length > 0" class="mt-4">
            <h5 class="text-sm font-medium text-gray-900 mb-2">Key Requirements</h5>
            <ul class="space-y-1">
              <li
                v-for="requirement in position.requirements.slice(0, 3)"
                :key="requirement"
                class="text-xs text-gray-600 flex items-center"
              >
                <div class="w-1 h-1 bg-gray-400 rounded-full mr-2"></div>
                {{ requirement }}
              </li>
              <li v-if="position.requirements.length > 3" class="text-xs text-gray-500 italic">
                +{{ position.requirements.length - 3 }} more requirements
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Position Modal -->
    <Modal :show="showCreateModal || showEditModal" @close="closeModal">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ editingPosition ? 'Edit Position' : 'Create Position' }}
        </h3>
        
        <form @submit.prevent="submitPosition">
          <div class="space-y-4">
            <!-- Position Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Position Title <span class="text-red-500">*</span>
              </label>
              <input
                v-model="positionForm.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                :class="{ 'border-red-500': errors.title }"
                placeholder="Enter position title"
              />
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
            </div>

            <!-- Department -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Department <span class="text-red-500">*</span>
              </label>
              <select
                v-model="positionForm.department_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                :class="{ 'border-red-500': errors.department_id }"
              >
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
              <p v-if="errors.department_id" class="mt-1 text-sm text-red-600">{{ errors.department_id }}</p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                v-model="positionForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                :class="{ 'border-red-500': errors.description }"
                placeholder="Enter position description"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>

            <!-- Salary Range -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Min Salary
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">K</span>
                  <input
                    v-model.number="positionForm.min_salary"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    :class="{ 'border-red-500': errors.min_salary }"
                    placeholder="0.00"
                  />
                </div>
                <p v-if="errors.min_salary" class="mt-1 text-sm text-red-600">{{ errors.min_salary }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Max Salary
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">K</span>
                  <input
                    v-model.number="positionForm.max_salary"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    :class="{ 'border-red-500': errors.max_salary }"
                    placeholder="0.00"
                  />
                </div>
                <p v-if="errors.max_salary" class="mt-1 text-sm text-red-600">{{ errors.max_salary }}</p>
              </div>
            </div>

            <!-- Level -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Level
              </label>
              <select
                v-model="positionForm.level"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                :class="{ 'border-red-500': errors.level }"
              >
                <option value="">Select Level</option>
                <option value="Entry">Entry Level</option>
                <option value="Junior">Junior</option>
                <option value="Mid">Mid Level</option>
                <option value="Senior">Senior</option>
                <option value="Lead">Lead</option>
                <option value="Manager">Manager</option>
                <option value="Director">Director</option>
                <option value="Executive">Executive</option>
              </select>
              <p v-if="errors.level" class="mt-1 text-sm text-red-600">{{ errors.level }}</p>
            </div>

            <!-- Requirements -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Requirements
              </label>
              <div class="space-y-2">
                <div
                  v-for="(requirement, index) in positionForm.requirements"
                  :key="index"
                  class="flex items-center space-x-2"
                >
                  <input
                    v-model="positionForm.requirements[index]"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    placeholder="Enter requirement"
                  />
                  <button
                    type="button"
                    @click="removeRequirement(index)"
                    class="p-2 text-red-600 hover:text-red-800 transition-colors"
                  >
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
                <button
                  type="button"
                  @click="addRequirement"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
                >
                  <PlusIcon class="h-4 w-4 mr-2" />
                  Add Requirement
                </button>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select
                v-model="positionForm.is_active"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              >
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <LoadingSpinner v-if="processing" class="w-4 h-4 mr-2" />
              {{ editingPosition ? 'Update Position' : 'Create Position' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">Delete Position</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to delete "{{ positionToDelete?.title }}"? 
                This action cannot be undone.
              </p>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            @click="deletePosition"
          >
            Delete
          </button>
          <button
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:w-auto sm:text-sm"
            @click="showDeleteModal = false"
          >
            Cancel
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import {
  BriefcaseIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import Modal from '@/components/Modal.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

s
interfac
  id: number
  title: string
  description?:
  department_id: number
  min_salary?: number
  max_salary?: number
  level?: string
  requirements?:g[]
  is_active: boolean
  employee_count: nuber
  department?: {
    id: number
    name: string
  }
}

t {
  id: number
  name: stri
}


  positions: Position[]
  departments: Department[]
  canCreate?: boolean
  canEdit?: boolean
  canDelete?: boole
  errors?: Record<str>
}

 {
  canCreate: false,
  canEdit: false,
  canDelete: false,
  errors: () => ({})
})

ata
const filters = 
  search: '',
  department: '',
  status: ''
})

)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingPosition = ref<Positi
const positionToDelete = ref<Position | null>(null)
const processing = ref(false)

{
  title: '',
  descriptio',
  department_id: '',
  min_salary: null all,
  max_salary: null as number | null,
  level: '',
  requiremening[],
  is_active: true
})


const filte> {
  let filtered = [...props.positions]
  
  
    const search = filt()
    filtered = filtered.filter(position =>
      position.title.toLowerCase().includeh) ||
      position.description?.toLowerCase().includes(sea) ||
      position.department?.name.toLowerCase().includes(search)
    )
  }
  
  {
    filtered = filtered.fil
  }
  
  tus) {
    const isActive = fi'
    filtered = filtered.filter(position => positve)
  }
  
  red
})

{
  return !!(filters.search || filteatus)
})

ods
const formg => {
  if (min && max) {
    return `K${min.toLocaleString()} - K${`
  } else if (min) {
    return `K${min.toLocaleString()}+`
  } else if (max) {
    return `Up to K${max}`
 
ed'
}

const d{

}, 300)

c = () => {

}

const editPosition = (position: Posit
  editingPosition.value = position
  positionForm.title = position.title
  positionForm.description = position.description || ''
  positionForm.department_id = position.department_id.t()
  positionForm.min_salary = position.min_sal
  positionForm.max_salary = position.max_salary || null
  positionForm.level = position.level || ''
  positionForm.requirements ])]
 s_active
ue = true
}

const confirmDelete = (positioon) => {
 position

}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = fe
  editingPosition.value = null
  positionForm.title = ''
  positionForm.description = ''
  positionForm.department_id = '
  positionForm.min_salarynull
  positionForm.max_salary = null
  positionForm.level = ''
 = []
true
}

c

}

c> {
dex, 1)
}

co
  processing.val
  
  const data = {
    title: positionForm.title,
    description: positionForm.descriptio| null,
    department_id: positionForm.departme
    min_salary: positionForm.min_salar
    max_salary: positionForm.max_salary,
    level: positionForm.level || null,
   
  
  }
  
  if (editingPosition.vae) {
    router.put(route {
      onSuccess: () => {
        )
        processing.val
      },
      o=> {
      false
      }
    })
  } else {
    router.post(routta, {
      onSuccess: () => {
        
        processing.val= false
      },
      o
      se
      }
   })

}

const deletePosition = () => {
  if (positionToDelete.value) {
    router.delete(route('positions.de {
      onSuccess: () => {
       
      null
      }
  })
  }
}
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clam
 
  overfl</style>den;
}
ow: hid: vertical;x-orient-bobkit -wep: 3;  te.value = itionToDele  posfalsee = odal.valueleteMowDsh alue.id),ToDelete.v', positionstroy  } e = falluocessing.va  pr) => {: (nErrorue ()closeModale'), daorons.ste('positi.value = essing  procnError: () eue = falsloseModal(cid), data,e.ition.valu editingPospdate',s.usition('poluem.is_activonFortive: positiis_ac  !== ''),.trim()  reqeq =>ts.filter(renrm.requiremositionFoments: p requirey,id,nt_n |ue = true {n = () =>tiosubmitPosinst ts.splice(inuiremeneqrm.rositionFo  pmber) =ndex: nu = (iequirementveR remoonst')('pushements.quironForm.re  positi= () => {irement qust addReon = ivem.is_actoritionF  posts uiremenForm.reqposition  = 'als= truealue eleteModal.v  showDalue = e.veletoDonTiti possitin: PoditModal.valshowE   position.i =m.is_activepositionFor ments || [equiren.rsitio= [...(po nulry ||laingtroS {ion) =>allymaticte autoupdall wiperty uted proe compo th sctive,are reas   // FiltertersyFilonst appllters()lyFipp  ace(() => unter = deboncedFilebouspecifiNot   return ' }caleString()Lo.toring()}ocaleStmax.toLmber): strinx?: nunumber, main?: ange = (mSalaryRat// Meths.stnt || filterpartmers.deed(() =>  computsFilters =st haconilteturn fre== isActiive =ion.is_act== 'actives.status =lter(filters.staf iepartment)lters.d== fioString() =ment_id.t.depart => positionr(positiontepartment) ers.deif (filtrch(searcssewerCaLoarch.toers.sesearch) {rs.teif (filted(() = compuions =itedPosr/ Computed/as strs: [] tnu number | sn: 'ctive(m = reanForioconst positull>(null)on | nf(false = reateModal showCreconst({ivereacteactive d// Rrops>(),ps<PfineProlts(deithDefaut props = wconsnging, strian Props {faceinterngrtmenDepaface erintm strinng stri {tionosie P// Type  </div>
                <p v-if="errors.min_salary" class="mt-1 text-sm text-red-600">{{ errors.min_salary }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Max Salary
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">K</span>
                  <input
                    v-model.number="positionForm.max_salary"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    :class="{ 'border-red-500': errors.max_salary }"
                    placeholder="0.00"
                  />
                </div>
                <p v-if="errors.max_salary" class="mt-1 text-sm text-red-600">{{ errors.max_salary }}</p>
              </div>
            </div>

            <!-- Level -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Level
              </label>
              <select
                v-model="positionForm.level"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                :class="{ 'border-red-500': errors.level }"
              >
                <option value="">Select Level</option>
                <option value="Entry">Entry Level</option>
                <option value="Junior">Junior</option>
                <option value="Mid">Mid Level</option>
                <option value="Senior">Senior</option>
                <option value="Lead">Lead</option>
                <option value="Manager">Manager</option>
                <option value="Director">Director</option>
                <option value="Executive">Executive</option>
              </select>
              <p v-if="errors.level" class="mt-1 text-sm text-red-600">{{ errors.level }}</p>
            </div>

            <!-- Requirements -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Requirements
              </label>
              <div class="space-y-2">
                <div
                  v-for="(requirement, index) in positionForm.requirements"
                  :key="index"
                  class="flex items-center space-x-2"
                >
                  <input
                    v-model="positionForm.requirements[index]"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    placeholder="Enter requirement"
                  />
                  <button
                    type="button"
                    @click="removeRequirement(index)"
                    class="p-2 text-red-600 hover:text-red-800 transition-colors"
                  >
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
                <button
                  type="button"
                  @click="addRequirement"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
                >
                  <PlusIcon class="h-4 w-4 mr-2" />
                  Add Requirement
                </button>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select
                v-model="positionForm.is_active"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              >
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <LoadingSpinner v-if="processing" class="w-4 h-4 mr-2" />
              {{ editingPosition ? 'Update Position' : 'Create Position' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">Delete Position</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to delete "{{ positionToDelete?.title }}"? 
                This action cannot be undone.
              </p>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            @click="deletePosition"
          >
            Delete
          </button>
          <button
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:w-auto sm:text-sm"
            @click="showDeleteModal = false"
          >
            Cancel
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import {
  BriefcaseIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import Modal from '@/components/Modal.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { formatCurrency } from '@/utils/formatting'

// Types
interface Position {
  id: number
  title: string
  description?: string
  department_id: number
  min_salary?: number
  max_salary?: number
  level?: string
  requirements?: string[]
  is_active: boolean
  employee_count: number
  department?: {
    id: number
    name: string
  }
}

interface Department {
  id: number
  name: string
}

interface Props {
  positions: Position[]
  departments: Department[]
  canCreate?: boolean
  canEdit?: boolean
  canDelete?: boolean
  errors?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  canCreate: false,
  canEdit: false,
  canDelete: false,
  errors: () => ({})
})

// Reactive data
const filters = reactive({
  search: '',
  department: '',
  status: ''
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingPosition = ref<Position | null>(null)
const positionToDelete = ref<Position | null>(null)
const processing = ref(false)

const positionForm = reactive({
  title: '',
  description: '',
  department_id: '',
  min_salary: null as number | null,
  max_salary: null as number | null,
  level: '',
  requirements: [] as string[],
  is_active: true
})

// Computed
const filteredPositions = computed(() => {
  let filtered = [...props.positions]
  
  if (filters.search) {
    const search = filters.search.toLowerCase()
    filtered = filtered.filter(position =>
      position.title.toLowerCase().includes(search) ||
      position.description?.toLowerCase().includes(search) ||
      position.department?.name.toLowerCase().includes(search)
    )
  }
  
  if (filters.department) {
    filtered = filtered.filter(position => position.department_id.toString() === filters.department)
  }
  
  if (filters.status) {
    const isActive = filters.status === 'active'
    filtered = filtered.filter(position => position.is_active === isActive)
  }
  
  return filtered
})

const hasFilters = computed(() => {
  return !!(filters.search || filters.department || filters.status)
})

// Methods
const formatSalaryRange = (min?: number, max?: number): string => {
  if (!min && !max) return 'Not specified'
  if (min && max) return `${formatCurrency(min)} - ${formatCurrency(max)}`
  if (min) return `From ${formatCurrency(min)}`
  if (max) return `Up to ${formatCurrency(max)}`
  return 'Not specified'
}

const debouncedFilter = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  // Filters are reactive, so the computed property will update automatically
}

const editPosition = (position: Position) => {
  editingPosition.value = position
  positionForm.title = position.title
  positionForm.description = position.description || ''
  positionForm.department_id = position.department_id.toString()
  positionForm.min_salary = position.min_salary || null
  positionForm.max_salary = position.max_salary || null
  positionForm.level = position.level || ''
  positionForm.requirements = [...(position.requirements || [])]
  positionForm.is_active = position.is_active
  showEditModal.value = true
}

const confirmDelete = (position: Position) => {
  positionToDelete.value = position
  showDeleteModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingPosition.value = null
  positionForm.title = ''
  positionForm.description = ''
  positionForm.department_id = ''
  positionForm.min_salary = null
  positionForm.max_salary = null
  positionForm.level = ''
  positionForm.requirements = []
  positionForm.is_active = true
}

const addRequirement = () => {
  positionForm.requirements.push('')
}

const removeRequirement = (index: number) => {
  positionForm.requirements.splice(index, 1)
}

const submitPosition = () => {
  processing.value = true
  
  const data = {
    title: positionForm.title,
    description: positionForm.description || null,
    department_id: positionForm.department_id,
    min_salary: positionForm.min_salary,
    max_salary: positionForm.max_salary,
    level: positionForm.level || null,
    requirements: positionForm.requirements.filter(req => req.trim() !== ''),
    is_active: positionForm.is_active
  }
  
  if (editingPosition.value) {
    router.put(route('positions.update', editingPosition.value.id), data, {
      onSuccess: () => {
        closeModal()
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    })
  } else {
    router.post(route('positions.store'), data, {
      onSuccess: () => {
        closeModal()
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    })
  }
}

const deletePosition = () => {
  if (positionToDelete.value) {
    router.delete(route('positions.destroy', positionToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false
        positionToDelete.value = null
      }
    })
  }
}
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>