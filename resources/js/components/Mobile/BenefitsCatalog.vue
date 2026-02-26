<template>
  <div class="space-y-6">
    <!-- Tab Navigation -->
    <div class="flex gap-2 overflow-x-auto pb-2">
      <button
        v-for="tier in tiers"
        :key="tier.value"
        @click="activeTier = tier.value"
        :class="[
          'px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap transition-all',
          activeTier === tier.value
            ? 'bg-blue-600 text-white shadow-lg'
            : 'bg-white text-gray-700 border border-gray-200 hover:border-gray-300'
        ]"
      >
        {{ tier.label }}
      </button>
    </div>

    <!-- Tier Info Card -->
    <div v-if="activeTierData" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-100">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ activeTierData.name }}</h2>
            <p class="text-gray-600 mt-1">{{ activeTierData.description }}</p>
          </div>
          <div class="text-right flex-shrink-0">
            <div class="text-3xl font-bold text-blue-600">K{{ activeTierData.price }}</div>
            <p class="text-sm text-gray-600 mt-1">One-time investment</p>
          </div>
        </div>
      </div>
      <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ activeTierData.benefits_count }}</div>
          <p class="text-sm text-gray-600 mt-1">Benefits Included</p>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ activeTierData.storage_gb }}GB</div>
          <p class="text-sm text-gray-600 mt-1">Cloud Storage</p>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">{{ activeTierData.earning_potential }}%</div>
          <p class="text-sm text-gray-600 mt-1">Earning Potential</p>
        </div>
      </div>
    </div>

    <!-- Benefits Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="benefit in filteredBenefits"
        :key="benefit.id"
        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden"
      >
        <!-- Card Header with Icon -->
        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
          <div class="flex items-start gap-3">
            <div class="w-12 h-12 rounded-lg bg-white shadow-sm flex items-center justify-center flex-shrink-0">
              <component
                :is="getIconComponent(benefit.icon)"
                class="h-6 w-6 text-blue-600"
                aria-hidden="true"
              />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-900">{{ benefit.name }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">{{ getCategoryLabel(benefit.category) }}</p>
            </div>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-4">
          <p class="text-sm text-gray-600 leading-relaxed">{{ benefit.description }}</p>
          <div v-if="benefit.is_coming_soon" class="mt-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
            Coming Soon
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredBenefits.length === 0" class="text-center py-12">
      <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
        <SparklesIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
      </div>
      <h3 class="text-lg font-semibold text-gray-900">No benefits available</h3>
      <p class="text-gray-600 text-sm mt-1">Check back soon for more information</p>
    </div>

    <!-- Upgrade CTA -->
    <div v-if="!userHasStarterKit" class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-8 text-white text-center">
      <h3 class="text-2xl font-bold mb-2">Ready to Unlock These Benefits?</h3>
      <p class="text-blue-100 mb-6">Upgrade to a starter kit and get access to all these amazing features</p>
      <button
        @click="upgradeStarterKit"
        class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition-colors"
      >
        <span>Upgrade Now</span>
        <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  SparklesIcon,
  ChevronRightIcon,
  UserGroupIcon,
  BuildingOffice2Icon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  IdentificationIcon,
  DocumentTextIcon,
  BriefcaseIcon,
  CloudIcon,
  AcademicCapIcon,
  BookOpenIcon,
  MusicalNoteIcon,
  FilmIcon,
  BookmarkIcon,
} from '@heroicons/vue/24/outline'

interface Benefit {
  id: number
  name: string
  slug: string
  category: string
  description: string
  icon: string
  is_coming_soon: boolean
}

interface TierData {
  name: string
  description: string
  price: number
  benefits_count: number
  storage_gb: number
  earning_potential: number
}

const props = defineProps<{
  userHasStarterKit: boolean
}>();

const activeTier = ref('associate')
const benefits = ref<Benefit[]>([])
const loading = ref(true)

const tiers = [
  { label: 'Associate', value: 'associate' },
  { label: 'Professional', value: 'professional' },
  { label: 'Senior', value: 'senior' },
  { label: 'Manager', value: 'manager' },
  { label: 'Director', value: 'director' },
  { label: 'Executive', value: 'executive' },
  { label: 'Ambassador', value: 'ambassador' },
]

const tierData: Record<string, TierData> = {
  associate: {
    name: 'Associate',
    description: 'Entry level - Learning & Foundation Building',
    price: 300,
    benefits_count: 15,
    storage_gb: 2,
    earning_potential: 5,
  },
  professional: {
    name: 'Professional',
    description: 'Skilled Member - Application & Consistency',
    price: 300,
    benefits_count: 15,
    storage_gb: 2,
    earning_potential: 10,
  },
  senior: {
    name: 'Senior',
    description: 'Experienced Member - Team Building & Mentorship',
    price: 300,
    benefits_count: 15,
    storage_gb: 5,
    earning_potential: 15,
  },
  manager: {
    name: 'Manager',
    description: 'Team Leader - Leadership & Development',
    price: 300,
    benefits_count: 15,
    storage_gb: 10,
    earning_potential: 20,
  },
  director: {
    name: 'Director',
    description: 'Strategic Leader - Strategic Planning & Expansion',
    price: 300,
    benefits_count: 15,
    storage_gb: 15,
    earning_potential: 25,
  },
  executive: {
    name: 'Executive',
    description: 'Top Performer - Excellence & Innovation',
    price: 300,
    benefits_count: 15,
    storage_gb: 20,
    earning_potential: 30,
  },
  ambassador: {
    name: 'Ambassador',
    description: 'Brand Representative - Community Impact & Transformation',
    price: 300,
    benefits_count: 15,
    storage_gb: 25,
    earning_potential: 40,
  },
}

const activeTierData = computed(() => tierData[activeTier.value])

const filteredBenefits = computed(() => {
  return benefits.value
})

const categoryLabels: Record<string, string> = {
  apps: 'Digital Tools & Apps',
  cloud: 'Cloud & Data',
  learning: 'Learning & Training',
  media: 'Content & Media',
  resources: 'Digital Resources',
}

const iconMap: Record<string, any> = {
  UserGroupIcon,
  BuildingOffice2Icon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  IdentificationIcon,
  DocumentTextIcon,
  BriefcaseIcon,
  CloudIcon,
  AcademicCapIcon,
  BookOpenIcon,
  MusicalNoteIcon,
  FilmIcon,
  BookmarkIcon,
}

const getIconComponent = (iconName: string) => {
  return iconMap[iconName] || SparklesIcon
}

const getCategoryLabel = (category: string) => {
  return categoryLabels[category] || category
}

const upgradeStarterKit = () => {
  window.location.href = route('mygrownet.starter-kit.purchase');
}

const fetchBenefits = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/benefits')
    const data = await response.json()
    
    // Flatten grouped benefits
    const allBenefits: Benefit[] = []
    Object.values(data.benefits).forEach((categoryBenefits: any) => {
      allBenefits.push(...categoryBenefits)
    })
    
    benefits.value = allBenefits
  } catch (error) {
    console.error('Failed to fetch benefits:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchBenefits()
})
</script>
