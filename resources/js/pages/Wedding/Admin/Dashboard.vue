<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header - Mobile Optimized -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-4 py-3">
        <div class="flex items-center justify-between">
          <h1 class="text-base sm:text-lg font-medium text-gray-800 truncate max-w-[180px] sm:max-w-none">{{ weddingName }}</h1>
          <div class="flex items-center gap-2">
            <!-- Mobile: Icon only buttons -->
            <a
              :href="`/wedding-admin/${slug}/export-guests`"
              class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
              aria-label="Export guests to CSV"
            >
              <ArrowDownTrayIcon class="w-5 h-5" aria-hidden="true" />
            </a>
            <button
              @click="logout"
              class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
              aria-label="Logout"
            >
              <ArrowRightOnRectangleIcon class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="px-4 py-4 sm:py-6 max-w-7xl mx-auto">
      <!-- Stats Cards - Mobile Grid -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5 sm:gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-500 mb-1">Invited</p>
          <p class="text-2xl sm:text-3xl font-semibold text-gray-800">{{ stats.total_invited || 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-500 mb-1">Pending</p>
          <p class="text-2xl sm:text-3xl font-semibold text-amber-600">{{ stats.pending || 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-500 mb-1">Attending</p>
          <p class="text-2xl sm:text-3xl font-semibold text-green-600">{{ stats.attending || 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-500 mb-1">Declined</p>
          <p class="text-2xl sm:text-3xl font-semibold text-red-500">{{ stats.declined || 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 col-span-2 sm:col-span-1">
          <p class="text-xs sm:text-sm text-gray-500 mb-1">Confirmed Guests</p>
          <p class="text-2xl sm:text-3xl font-semibold text-purple-600">{{ stats.total_confirmed_guests || 0 }}</p>
        </div>
      </div>

      <!-- Filter Tabs - Horizontal Scroll on Mobile -->
      <div class="mb-4 -mx-4 px-4 overflow-x-auto scrollbar-hide">
        <div class="flex gap-2 min-w-max pb-2">
          <button
            @click="filterStatus = 'all'"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap',
              filterStatus === 'all' 
                ? 'bg-gray-800 text-white' 
                : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'
            ]"
          >
            All ({{ guests.length }})
          </button>
          <button
            @click="filterStatus = 'pending'"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap',
              filterStatus === 'pending' 
                ? 'bg-amber-500 text-white' 
                : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'
            ]"
          >
            Pending ({{ pendingGuests.length }})
          </button>
          <button
            @click="filterStatus = 'attending'"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap',
              filterStatus === 'attending' 
                ? 'bg-green-500 text-white' 
                : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'
            ]"
          >
            Attending ({{ attendingGuests.length }})
          </button>
          <button
            @click="filterStatus = 'declined'"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap',
              filterStatus === 'declined' 
                ? 'bg-red-500 text-white' 
                : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'
            ]"
          >
            Declined ({{ declinedGuests.length }})
          </button>
        </div>
      </div>

      <!-- Guest List Header -->
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base sm:text-lg font-medium text-gray-800">Guest List</h2>
        <button
          @click="openAddGuestModal"
          class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-lg transition-colors"
        >
          <PlusIcon class="w-4 h-4" aria-hidden="true" />
          <span class="hidden sm:inline">Add Guest</span>
          <span class="sm:hidden">Add</span>
        </button>
      </div>

      <!-- Mobile Card View -->
      <div class="space-y-3 md:hidden">
        <div
          v-for="guest in filteredGuests"
          :key="guest.id"
          class="bg-white rounded-xl border border-gray-200 p-4"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0">
              <h3 class="font-medium text-gray-900 truncate">{{ guest.full_name }}</h3>
              <p class="text-sm text-gray-500 truncate">{{ guest.group_name || 'No group' }}</p>
            </div>
            <span :class="getStatusClass(guest.rsvp_status)">
              {{ getStatusLabel(guest.rsvp_status) }}
            </span>
          </div>
          
          <div class="grid grid-cols-2 gap-3 text-sm mb-3">
            <div>
              <p class="text-gray-500 text-xs">Contact</p>
              <p class="text-gray-900 truncate">{{ guest.phone || guest.email || '-' }}</p>
            </div>
            <div>
              <p class="text-gray-500 text-xs">Guests</p>
              <p class="text-gray-900">
                <span v-if="guest.rsvp_status === 'attending'">{{ guest.confirmed_guests }} confirmed</span>
                <span v-else>{{ guest.allowed_guests }} allowed</span>
              </p>
            </div>
            <div v-if="guest.dietary_restrictions" class="col-span-2">
              <p class="text-gray-500 text-xs">Dietary</p>
              <p class="text-gray-900 truncate">{{ guest.dietary_restrictions }}</p>
            </div>
          </div>
          
          <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-400">
              {{ guest.rsvp_submitted_at ? `Responded ${formatDate(guest.rsvp_submitted_at)}` : 'No response yet' }}
            </p>
            <div class="flex items-center gap-1">
              <button 
                @click="openEditGuestModal(guest)" 
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                aria-label="Edit guest"
              >
                <PencilIcon class="w-5 h-5" aria-hidden="true" />
              </button>
              <button 
                @click="confirmDeleteGuest(guest)" 
                class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                aria-label="Delete guest"
              >
                <TrashIcon class="w-5 h-5" aria-hidden="true" />
              </button>
            </div>
          </div>
        </div>
        
        <!-- Empty State Mobile -->
        <div v-if="filteredGuests.length === 0" class="bg-white rounded-xl border border-gray-200 p-8 text-center">
          <UserGroupIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
          <p class="text-gray-500">
            {{ filterStatus === 'all' ? 'No guests added yet' : `No ${filterStatus} guests` }}
          </p>
        </div>
      </div>

      <!-- Desktop Table View -->
      <div class="hidden md:block bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Group</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guests</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dietary</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responded</th>
                <th class="px-4 lg:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="guest in filteredGuests" :key="guest.id" class="hover:bg-gray-50">
                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ guest.full_name }}</td>
                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ guest.email || '-' }}</div>
                  <div class="text-sm text-gray-500">{{ guest.phone || '-' }}</div>
                </td>
                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ guest.group_name || '-' }}</td>
                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(guest.rsvp_status)">
                    {{ getStatusLabel(guest.rsvp_status) }}
                  </span>
                </td>
                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span v-if="guest.rsvp_status === 'attending'">{{ guest.confirmed_guests }}</span>
                  <span v-else class="text-gray-400">{{ guest.allowed_guests }} allowed</span>
                </td>
                <td class="px-4 lg:px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ guest.dietary_restrictions || '-' }}</td>
                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ guest.rsvp_submitted_at ? formatDate(guest.rsvp_submitted_at) : '-' }}
                </td>
                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-right text-sm">
                  <button @click="openEditGuestModal(guest)" class="text-gray-600 hover:text-gray-900 mr-3" aria-label="Edit guest">
                    <PencilIcon class="w-4 h-4" aria-hidden="true" />
                  </button>
                  <button @click="confirmDeleteGuest(guest)" class="text-red-500 hover:text-red-700" aria-label="Delete guest">
                    <TrashIcon class="w-4 h-4" aria-hidden="true" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredGuests.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  {{ filterStatus === 'all' ? 'No guests added yet. Add guests to your invitation list.' : `No ${filterStatus} guests.` }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>


    <!-- Guest Modal - Mobile Optimized -->
    <div v-if="showGuestModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end sm:items-center justify-center min-h-screen">
        <div class="fixed inset-0 bg-black/50" @click="closeGuestModal"></div>
        <div class="relative bg-white w-full sm:max-w-md sm:rounded-xl rounded-t-2xl shadow-xl max-h-[90vh] overflow-y-auto">
          <!-- Modal Header -->
          <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">{{ editingGuest ? 'Edit Guest' : 'Add Guest' }}</h3>
            <button @click="closeGuestModal" class="p-2 -mr-2 text-gray-400 hover:text-gray-600 rounded-lg" aria-label="Close modal">
              <XMarkIcon class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
          
          <form @submit.prevent="saveGuest" class="p-4 space-y-4">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                <input 
                  v-model="guestForm.first_name" 
                  type="text" 
                  required 
                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent" 
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                <input 
                  v-model="guestForm.last_name" 
                  type="text" 
                  required 
                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <input 
                v-model="guestForm.email" 
                type="email" 
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
              <input 
                v-model="guestForm.phone" 
                type="tel" 
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Group</label>
              <input 
                v-model="guestForm.group_name" 
                type="text" 
                placeholder="e.g., Family, Friends, Work" 
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Allowed Guests</label>
              <input 
                v-model.number="guestForm.allowed_guests" 
                type="number" 
                min="1" 
                max="10" 
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent" 
              />
              <p class="text-xs text-gray-500 mt-1">Including themselves</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
              <textarea 
                v-model="guestForm.notes" 
                rows="2" 
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent resize-none"
              ></textarea>
            </div>
            
            <!-- RSVP Status (for editing) -->
            <div v-if="editingGuest">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">RSVP Status</label>
              <select 
                v-model="guestForm.rsvp_status" 
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent bg-white"
              >
                <option value="pending">Pending</option>
                <option value="attending">Attending</option>
                <option value="declined">Declined</option>
              </select>
            </div>
            
            <div v-if="editingGuest && guestForm.rsvp_status === 'attending'">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmed Guests</label>
              <input 
                v-model.number="guestForm.confirmed_guests" 
                type="number" 
                min="0" 
                max="10" 
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-gray-800 focus:border-transparent" 
              />
            </div>

            <!-- Action Buttons - Sticky on Mobile -->
            <div class="sticky bottom-0 bg-white pt-4 pb-2 -mx-4 px-4 border-t border-gray-100 mt-6">
              <div class="flex gap-3">
                <button 
                  type="button" 
                  @click="closeGuestModal" 
                  class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="isSaving" 
                  class="flex-1 px-4 py-3 text-sm font-medium bg-gray-800 hover:bg-gray-900 text-white rounded-lg disabled:opacity-50 transition-colors"
                >
                  {{ isSaving ? 'Saving...' : 'Save Guest' }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal - Mobile Optimized -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end sm:items-center justify-center min-h-screen">
        <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
        <div class="relative bg-white w-full sm:max-w-sm sm:rounded-xl rounded-t-2xl shadow-xl p-6">
          <div class="text-center sm:text-left">
            <div class="mx-auto sm:mx-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
              <TrashIcon class="w-6 h-6 text-red-600" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Guest</h3>
            <p class="text-sm text-gray-500 mb-6">
              Are you sure you want to delete <span class="font-medium text-gray-700">{{ deletingGuest?.full_name }}</span>? This action cannot be undone.
            </p>
          </div>
          <div class="flex flex-col-reverse sm:flex-row gap-3">
            <button 
              @click="showDeleteModal = false" 
              class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Cancel
            </button>
            <button 
              @click="executeDelete" 
              :disabled="isDeleting" 
              class="flex-1 px-4 py-3 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-50 transition-colors"
            >
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  PlusIcon, 
  PencilIcon, 
  TrashIcon, 
  ArrowDownTrayIcon, 
  ArrowRightOnRectangleIcon,
  XMarkIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

interface Guest {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string | null
  phone: string | null
  allowed_guests: number
  group_name: string | null
  notes: string | null
  invitation_sent: boolean
  rsvp_status: 'pending' | 'attending' | 'declined'
  confirmed_guests: number
  dietary_restrictions: string | null
  rsvp_message: string | null
  rsvp_submitted_at: string | null
}

interface Stats {
  total_invited: number
  attending: number
  declined: number
  pending: number
  total_confirmed_guests: number
}

const props = defineProps<{ slug: string; weddingName: string; guests: Guest[]; stats: Stats }>()

const filterStatus = ref<'all' | 'pending' | 'attending' | 'declined'>('all')
const showGuestModal = ref(false)
const showDeleteModal = ref(false)
const editingGuest = ref<Guest | null>(null)
const deletingGuest = ref<Guest | null>(null)
const isSaving = ref(false)
const isDeleting = ref(false)

const guestForm = reactive({
  first_name: '', last_name: '', email: '', phone: '', group_name: '', allowed_guests: 1, notes: '',
  rsvp_status: 'pending' as 'pending' | 'attending' | 'declined', confirmed_guests: 0
})

// Computed filtered lists
const pendingGuests = computed(() => props.guests.filter(g => g.rsvp_status === 'pending'))
const attendingGuests = computed(() => props.guests.filter(g => g.rsvp_status === 'attending'))
const declinedGuests = computed(() => props.guests.filter(g => g.rsvp_status === 'declined'))

const filteredGuests = computed(() => {
  if (filterStatus.value === 'all') return props.guests
  return props.guests.filter(g => g.rsvp_status === filterStatus.value)
})

const resetGuestForm = () => {
  Object.assign(guestForm, { first_name: '', last_name: '', email: '', phone: '', group_name: '', allowed_guests: 1, notes: '', rsvp_status: 'pending', confirmed_guests: 0 })
}

const openAddGuestModal = () => { editingGuest.value = null; resetGuestForm(); showGuestModal.value = true }

const openEditGuestModal = (guest: Guest) => {
  editingGuest.value = guest
  Object.assign(guestForm, {
    first_name: guest.first_name, last_name: guest.last_name, email: guest.email || '', phone: guest.phone || '',
    group_name: guest.group_name || '', allowed_guests: guest.allowed_guests, notes: guest.notes || '',
    rsvp_status: guest.rsvp_status, confirmed_guests: guest.confirmed_guests
  })
  showGuestModal.value = true
}

const closeGuestModal = () => { showGuestModal.value = false; editingGuest.value = null; resetGuestForm() }

const saveGuest = () => {
  isSaving.value = true
  const url = editingGuest.value ? `/wedding-admin/${props.slug}/invited-guests/${editingGuest.value.id}` : `/wedding-admin/${props.slug}/invited-guests`
  
  if (editingGuest.value) {
    router.put(url, { ...guestForm }, {
      preserveScroll: true,
      onSuccess: () => { closeGuestModal() },
      onError: (errors) => { alert(Object.values(errors).join('\n') || 'Failed to save.') },
      onFinish: () => { isSaving.value = false }
    })
  } else {
    router.post(url, { ...guestForm }, {
      preserveScroll: true,
      onSuccess: () => { closeGuestModal() },
      onError: (errors) => { alert(Object.values(errors).join('\n') || 'Failed to save.') },
      onFinish: () => { isSaving.value = false }
    })
  }
}

const confirmDeleteGuest = (guest: Guest) => { deletingGuest.value = guest; showDeleteModal.value = true }

const executeDelete = () => {
  if (!deletingGuest.value) return
  isDeleting.value = true
  
  router.delete(`/wedding-admin/${props.slug}/invited-guests/${deletingGuest.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { showDeleteModal.value = false; deletingGuest.value = null },
    onError: (errors) => { alert(Object.values(errors).join('\n') || 'Failed to delete.') },
    onFinish: () => { isDeleting.value = false }
  })
}

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800',
    attending: 'inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    declined: 'inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || classes.pending
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = { pending: 'Pending', attending: 'Attending', declined: 'Declined' }
  return labels[status] || 'Pending'
}

const logout = () => { router.post(`/wedding-admin/${props.slug}/logout`) }
const formatDate = (dateString: string) => new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
</script>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
