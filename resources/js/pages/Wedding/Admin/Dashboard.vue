<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-4">
            <h1 class="text-lg font-medium text-gray-800">{{ weddingName }}</h1>
          </div>
          <div class="flex items-center gap-4">
            <a
              :href="`/wedding-admin/${slug}/export-guests`"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              <ArrowDownTrayIcon class="w-4 h-4" aria-hidden="true" />
              Export CSV
            </a>
            <button
              @click="logout"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors"
            >
              <ArrowRightOnRectangleIcon class="w-4 h-4" aria-hidden="true" />
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-500 mb-1">Invited</p>
          <p class="text-3xl font-light text-gray-800">{{ stats.total_invited || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-500 mb-1">Pending</p>
          <p class="text-3xl font-light text-amber-600">{{ stats.pending || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-500 mb-1">Attending</p>
          <p class="text-3xl font-light text-green-600">{{ stats.attending || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-500 mb-1">Declined</p>
          <p class="text-3xl font-light text-red-500">{{ stats.declined || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-500 mb-1">Confirmed Guests</p>
          <p class="text-3xl font-light text-purple-600">{{ stats.total_confirmed_guests || 0 }}</p>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-8">
          <button
            @click="filterStatus = 'all'"
            :class="['py-4 px-1 border-b-2 font-medium text-sm transition-colors', filterStatus === 'all' ? 'border-gray-800 text-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700']"
          >
            All ({{ guests.length }})
          </button>
          <button
            @click="filterStatus = 'pending'"
            :class="['py-4 px-1 border-b-2 font-medium text-sm transition-colors', filterStatus === 'pending' ? 'border-amber-600 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
          >
            Pending ({{ pendingGuests.length }})
          </button>
          <button
            @click="filterStatus = 'attending'"
            :class="['py-4 px-1 border-b-2 font-medium text-sm transition-colors', filterStatus === 'attending' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
          >
            Attending ({{ attendingGuests.length }})
          </button>
          <button
            @click="filterStatus = 'declined'"
            :class="['py-4 px-1 border-b-2 font-medium text-sm transition-colors', filterStatus === 'declined' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
          >
            Declined ({{ declinedGuests.length }})
          </button>
        </nav>
      </div>

      <!-- Guest List -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-medium text-gray-800">Guest List</h2>
        <button
          @click="openAddGuestModal"
          class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors"
        >
          <PlusIcon class="w-4 h-4" aria-hidden="true" />
          Add Guest
        </button>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Group</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guests</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dietary</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responded</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="guest in filteredGuests" :key="guest.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ guest.full_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ guest.email || '-' }}</div>
                  <div class="text-sm text-gray-500">{{ guest.phone || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ guest.group_name || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(guest.rsvp_status)">
                    {{ getStatusLabel(guest.rsvp_status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span v-if="guest.rsvp_status === 'attending'">{{ guest.confirmed_guests }}</span>
                  <span v-else class="text-gray-400">{{ guest.allowed_guests }} allowed</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ guest.dietary_restrictions || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ guest.rsvp_submitted_at ? formatDate(guest.rsvp_submitted_at) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
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

    <!-- Guest Modal -->
    <div v-if="showGuestModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" @click="closeGuestModal"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ editingGuest ? 'Edit Guest' : 'Add Guest' }}</h3>
          <form @submit.prevent="saveGuest" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input v-model="guestForm.first_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input v-model="guestForm.last_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="guestForm.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input v-model="guestForm.phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
              <input v-model="guestForm.group_name" type="text" placeholder="e.g., Family, Friends, Work" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Allowed Guests (including themselves)</label>
              <input v-model.number="guestForm.allowed_guests" type="number" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="guestForm.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>
            
            <!-- RSVP Status (for editing) -->
            <div v-if="editingGuest">
              <label class="block text-sm font-medium text-gray-700 mb-1">RSVP Status</label>
              <select v-model="guestForm.rsvp_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="pending">Pending</option>
                <option value="attending">Attending</option>
                <option value="declined">Declined</option>
              </select>
            </div>
            
            <div v-if="editingGuest && guestForm.rsvp_status === 'attending'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirmed Guests</label>
              <input v-model.number="guestForm.confirmed_guests" type="number" min="0" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            </div>

            <div class="flex justify-end gap-3 pt-4">
              <button type="button" @click="closeGuestModal" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md">Cancel</button>
              <button type="submit" :disabled="isSaving" class="px-4 py-2 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-md disabled:opacity-50">
                {{ isSaving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Guest</h3>
          <p class="text-sm text-gray-500 mb-6">
            Are you sure you want to delete {{ deletingGuest?.full_name }}? This action cannot be undone.
          </p>
          <div class="flex justify-end gap-3">
            <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md">Cancel</button>
            <button @click="executeDelete" :disabled="isDeleting" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-md disabled:opacity-50">
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
import { PlusIcon, PencilIcon, TrashIcon, ArrowDownTrayIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'

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
    pending: 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800',
    attending: 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    declined: 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || classes.pending
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = { pending: 'Pending', attending: 'Attending', declined: 'Declined' }
  return labels[status] || 'Pending'
}

const logout = () => { router.post(`/wedding-admin/${props.slug}/logout`) }
const formatDate = (dateString: string) => new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
</script>
