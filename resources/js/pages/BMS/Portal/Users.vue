<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: BMSLayout })

interface PortalUser {
  id: number
  name: string
  email: string
  portal_customers: Array<{
    id: number
    name: string
    email: string
    pivot: { is_active: boolean; last_login_at: string | null }
  }>
}

interface Props {
  users: { data: PortalUser[]; links: any[] }
  allUsers: Array<{ id: number; name: string; email: string }>
  customers: Array<{ id: number; name: string; email: string; phone: string }>
}

const props = defineProps<Props>()

const showLinkForm = ref(false)
const userSearch = ref('')
const customerSearch = ref('')

const form = useForm({ user_id: '', customer_id: '' })

const filteredUsers = ref<Array<{ id: number; name: string; email: string }>>([])
const filteredCustomers = ref<Array<{ id: number; name: string; email: string; phone: string }>>([])

const showUserDropdown = ref(false)
const showCustomerDropdown = ref(false)

const selectedUserName = ref('')
const selectedCustomerName = ref('')

const openLinkForm = () => {
  form.reset()
  userSearch.value = ''
  customerSearch.value = ''
  selectedUserName.value = ''
  selectedCustomerName.value = ''
  showLinkForm.value = true
}

const filterUsers = () => {
  const q = userSearch.value.toLowerCase()
  filteredUsers.value = props.allUsers.filter(u =>
    u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
  ).slice(0, 10)
  showUserDropdown.value = true
}

const selectUser = (user: { id: number; name: string; email: string }) => {
  form.user_id = String(user.id)
  selectedUserName.value = `${user.name} (${user.email})`
  userSearch.value = user.name
  showUserDropdown.value = false
}

const filterCustomers = () => {
  const q = customerSearch.value.toLowerCase()
  filteredCustomers.value = props.customers.filter(c =>
    c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)
  ).slice(0, 10)
  showCustomerDropdown.value = true
}

const selectCustomer = (customer: { id: number; name: string; email: string; phone: string }) => {
  form.customer_id = String(customer.id)
  selectedCustomerName.value = `${customer.name} (${customer.email})`
  customerSearch.value = customer.name
  showCustomerDropdown.value = false
}

const submitLink = () => {
  form.post(route('bms.portal-users.link'), {
    onSuccess: () => {
      showLinkForm.value = false
      form.reset()
    }
  })
}

const unlinkUser = (userId: number, customerId: number) => {
  if (confirm('Remove this customer link?')) {
    router.delete(route('bms.portal-users.unlink', [userId, customerId]))
  }
}

const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Portal Users</h1>
        <p class="text-sm text-gray-500 mt-1">Manage customer portal user access</p>
      </div>
      <button @click="openLinkForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
        Link User to Customer
      </button>
    </div>

    <!-- Link Form Modal -->
    <div v-if="showLinkForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showLinkForm = false">
      <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Link Portal User</h2>
        <form @submit.prevent="submitLink" class="space-y-4">
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
            <input v-model="userSearch" @input="filterUsers" @focus="filterUsers" @blur="setTimeout(() => showUserDropdown = false, 200)" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Search users..." />
            <div v-if="showUserDropdown && filteredUsers.length" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
              <button v-for="u in filteredUsers" :key="u.id" type="button" @mousedown="selectUser(u)" class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50">{{ u.name }} <span class="text-gray-400">{{ u.email }}</span></button>
            </div>
          </div>
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
            <input v-model="customerSearch" @input="filterCustomers" @focus="filterCustomers" @blur="setTimeout(() => showCustomerDropdown = false, 200)" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Search customers..." />
            <div v-if="showCustomerDropdown && filteredCustomers.length" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
              <button v-for="c in filteredCustomers" :key="c.id" type="button" @mousedown="selectCustomer(c)" class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50">{{ c.name }} <span class="text-gray-400">{{ c.email }}</span></button>
            </div>
          </div>
          <div v-if="form.errors.user_id || form.errors.customer_id" class="text-sm text-red-600">{{ form.errors.user_id || form.errors.customer_id }}</div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showLinkForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Cancel</button>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
              {{ form.processing ? 'Linking...' : 'Link User' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Linked Customers</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ u.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ u.email }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">
                <div v-if="u.portal_customers.length === 0" class="text-gray-400 italic">No customers linked</div>
                <div v-for="c in u.portal_customers" :key="c.id" class="flex items-center gap-2 mb-1">
                  <span>{{ c.name }}</span>
                  <span v-if="c.pivot.last_login_at" class="text-xs text-gray-400">(last login: {{ formatDate(c.pivot.last_login_at) }})</span>
                  <button @click="unlinkUser(u.id, c.id)" class="text-red-500 hover:text-red-700 text-xs font-medium ml-1">Remove</button>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <button @click="openLinkForm" class="text-blue-600 hover:text-blue-700 font-medium">Link Another</button>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No portal users found</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="users.links" />
    </div>
  </div>
</template>
