<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import MemberLayout from '@/layouts/MemberLayout.vue'
import { Calendar, MapPin, Users, Award, TrendingUp } from 'lucide-vue-next'

interface Workshop {
  id: number
  title: string
  slug: string
  description: string
  category: string
  delivery_format: string
  price: string
  lp_reward: number
  bp_reward: number
  start_date: string
  end_date: string
  location: string | null
  instructor_name: string | null
  featured_image: string | null
  registered_count: number
  available_slots: number | null
  is_full: boolean
}

defineProps<{
  workshops: Workshop[]
  filters: {
    category?: string
    format?: string
  }
}>()

const getCategoryColor = (category: string) => {
  const colors = {
    financial_literacy: 'bg-green-100 text-green-800',
    business_skills: 'bg-blue-100 text-blue-800',
    leadership: 'bg-purple-100 text-purple-800',
    marketing: 'bg-orange-100 text-orange-800',
    technology: 'bg-indigo-100 text-indigo-800',
    personal_development: 'bg-pink-100 text-pink-800',
  }
  return colors[category as keyof typeof colors] || 'bg-gray-100 text-gray-800'
}

const getCategoryLabel = (category: string) => {
  const labels = {
    financial_literacy: 'Financial Literacy',
    business_skills: 'Business Skills',
    leadership: 'Leadership',
    marketing: 'Marketing',
    technology: 'Technology',
    personal_development: 'Personal Development',
  }
  return labels[category as keyof typeof labels] || category
}

const getFormatLabel = (format: string) => {
  const labels = {
    online: 'Online',
    physical: 'Physical',
    hybrid: 'Hybrid',
  }
  return labels[format as keyof typeof labels] || format
}
</script>

<template>
  <Head title="Workshops & Training" />
  <MemberLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Workshops & Training</h1>
          <p class="text-gray-600 mt-1">Learn new skills and earn LP/BP rewards</p>
        </div>
        <Link
          :href="route('mygrownet.workshops.my-workshops')"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          My Workshops
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option value="">All Categories</option>
              <option value="financial_literacy">Financial Literacy</option>
              <option value="business_skills">Business Skills</option>
              <option value="leadership">Leadership</option>
              <option value="marketing">Marketing</option>
              <option value="technology">Technology</option>
              <option value="personal_development">Personal Development</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option value="">All Formats</option>
              <option value="online">Online</option>
              <option value="physical">Physical</option>
              <option value="hybrid">Hybrid</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Workshops Grid -->
      <div v-if="workshops.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">No workshops available at the moment. Check back soon!</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="workshop in workshops"
          :key="workshop.id"
          class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden"
        >
          <div v-if="workshop.featured_image" class="h-48 bg-gray-200">
            <img :src="workshop.featured_image" :alt="workshop.title" class="w-full h-full object-cover" />
          </div>
          <div v-else class="h-48 bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
            <Award class="w-16 h-16 text-white opacity-50" />
          </div>

          <div class="p-6">
            <div class="flex items-center justify-between mb-2">
              <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getCategoryColor(workshop.category)]">
                {{ getCategoryLabel(workshop.category) }}
              </span>
              <span class="text-xs text-gray-500">{{ getFormatLabel(workshop.delivery_format) }}</span>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ workshop.title }}</h3>
            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ workshop.description }}</p>

            <div class="space-y-2 mb-4">
              <div class="flex items-center text-sm text-gray-600">
                <Calendar class="w-4 h-4 mr-2" />
                {{ workshop.start_date }}
              </div>
              <div v-if="workshop.location" class="flex items-center text-sm text-gray-600">
                <MapPin class="w-4 h-4 mr-2" />
                {{ workshop.location }}
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <Users class="w-4 h-4 mr-2" />
                {{ workshop.registered_count }} registered
                <span v-if="workshop.available_slots !== null">
                  ({{ workshop.available_slots }} slots left)
                </span>
              </div>
            </div>

            <div class="flex items-center justify-between mb-4 p-3 bg-blue-50 rounded-lg">
              <div class="text-center">
                <p class="text-xs text-gray-600">LP Reward</p>
                <p class="text-lg font-bold text-blue-600">{{ workshop.lp_reward }}</p>
              </div>
              <div class="text-center">
                <p class="text-xs text-gray-600">BP Reward</p>
                <p class="text-lg font-bold text-green-600">{{ workshop.bp_reward }}</p>
              </div>
              <div class="text-center">
                <p class="text-xs text-gray-600">Price</p>
                <p class="text-lg font-bold text-gray-900">K{{ workshop.price }}</p>
              </div>
            </div>

            <Link
              :href="route('mygrownet.workshops.show', workshop.slug)"
              :class="[
                'block w-full text-center px-4 py-2 rounded-lg font-medium transition-colors',
                workshop.is_full
                  ? 'bg-gray-300 text-gray-600 cursor-not-allowed'
                  : 'bg-blue-600 text-white hover:bg-blue-700'
              ]"
              :disabled="workshop.is_full"
            >
              {{ workshop.is_full ? 'Full' : 'View Details' }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>
