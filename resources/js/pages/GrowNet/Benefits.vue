<template>
  <Head title="My Benefits" />

  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-6">
      <div class="flex items-center gap-3 mb-4">
        <Link
          :href="route('grownet.dashboard')"
          class="p-2 hover:bg-white/10 rounded-lg"
          aria-label="Back to dashboard"
        >
          <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <div>
          <h1 class="text-xl font-bold">Good afternoon, {{ userName }}</h1>
          <p class="text-sm text-blue-100">Welcome back</p>
        </div>
      </div>

      <!-- Tier Info -->
      <div v-if="hasStarterKit" class="bg-white/10 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-blue-100">{{ tierName }}</p>
            <p class="text-xs text-blue-200 mt-0.5">{{ tierDescription }}</p>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold">K{{ tierPrice }}</p>
            <p class="text-xs text-blue-200">One-time investment</p>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4">
          <div class="text-center">
            <p class="text-xl font-bold">{{ benefitsCount }}</p>
            <p class="text-xs text-blue-200">Benefits Included</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-bold">{{ storageAllocation }}GB</p>
            <p class="text-xs text-blue-200">Cloud Storage</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-bold">{{ earningPotential }}%</p>
            <p class="text-xs text-blue-200">Earning Potential</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Benefits Grid -->
    <div class="p-4 pb-24">
      <div class="grid grid-cols-2 gap-3">
        <div
          v-for="benefit in allBenefits"
          :key="benefit.id"
          class="bg-white rounded-lg p-3 border border-gray-200"
        >
          <div class="flex flex-col h-full">
            <div class="flex items-start gap-2 mb-2">
              <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                <component
                  :is="getIconComponent(benefit.icon)"
                  class="h-5 w-5 text-blue-600"
                  aria-hidden="true"
                />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-sm text-gray-900 leading-tight">{{ benefit.name }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ getCategoryLabel(benefit.category) }}</p>
              </div>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed flex-1">{{ benefit.description }}</p>
            <div v-if="benefit.is_coming_soon" class="mt-2">
              <span class="inline-block text-xs font-medium text-orange-700 bg-orange-50 px-2 py-1 rounded">
                Coming Soon
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Upgrade CTA -->
      <div v-if="!hasStarterKit" class="mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white text-center">
        <h3 class="text-lg font-bold mb-2">Ready to Unlock These Benefits?</h3>
        <p class="text-sm text-blue-100 mb-4">Upgrade to a starter kit and get access to all these amazing features</p>
        <button
          @click="upgradeStarterKit"
          class="px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50"
        >
          Upgrade Now
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  ChevronLeftIcon,
  SparklesIcon,
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
  GiftIcon,
  StarIcon,
  ArrowUpCircleIcon,
  DevicePhoneMobileIcon,
  ShoppingBagIcon,
  VideoCameraIcon,
  PlayCircleIcon,
} from '@heroicons/vue/24/outline'

interface Benefit {
  id: number
  name: string
  slug: string
  category: string
  benefit_type: string
  description: string
  icon: string
  unit?: string
  allocation?: number | boolean
  is_coming_soon: boolean
  status: string
}

const page = usePage()
const userName = computed(() => page.props.auth?.user?.name?.split(' ')[0] || 'Member')
const allBenefits = ref<Benefit[]>([])
const hasStarterKit = ref(false)
const userTier = ref<string | null>(null)

const tierInfo: Record<string, any> = {
  lite: { name: 'Lite', description: 'Entry level - Learning & Foundation Building', price: 300, storage: 5, earning: 5 },
  basic: { name: 'Basic', description: 'Skilled Member - Application & Consistency', price: 500, storage: 10, earning: 10 },
  growth_plus: { name: 'Growth Plus', description: 'Experienced Member - Team Building', price: 1000, storage: 25, earning: 15 },
  pro: { name: 'Pro', description: 'Top Performer - Excellence & Innovation', price: 2000, storage: 50, earning: 20 },
}

const tierName = computed(() => tierInfo[userTier.value || 'lite']?.name || 'Lite')
const tierDescription = computed(() => tierInfo[userTier.value || 'lite']?.description || '')
const tierPrice = computed(() => tierInfo[userTier.value || 'lite']?.price || 300)
const storageAllocation = computed(() => tierInfo[userTier.value || 'lite']?.storage || 5)
const earningPotential = computed(() => tierInfo[userTier.value || 'lite']?.earning || 5)
const benefitsCount = computed(() => allBenefits.value.filter(b => b.benefit_type === 'starter_kit' && b.status === 'active').length)

const categoryLabels: Record<string, string> = {
  apps: 'Digital Tools & Apps',
  cloud: 'Cloud & Data',
  learning: 'Learning & Training',
  media: 'Content & Media',
  resources: 'Digital Resources',
}

const iconMap: Record<string, any> = {
  UserGroupIcon, BuildingOffice2Icon, CurrencyDollarIcon, ShoppingCartIcon,
  IdentificationIcon, DocumentTextIcon, BriefcaseIcon, CloudIcon,
  AcademicCapIcon, BookOpenIcon, MusicalNoteIcon, FilmIcon,
  BookmarkIcon, GiftIcon, StarIcon, ArrowUpCircleIcon,
  DevicePhoneMobileIcon, ShoppingBagIcon, VideoCameraIcon,
  PlayCircleIcon, SparklesIcon,
}

const getIconComponent = (iconName: string) => iconMap[iconName] || SparklesIcon

const getCategoryLabel = (category: string) => categoryLabels[category] || category

const upgradeStarterKit = () => {
  window.location.href = route('grownet.starter-kit')
}

const fetchBenefits = async () => {
  try {
    const response = await fetch('/api/my-benefits')
    const data = await response.json()
    
    const allStarterKitBenefits: Benefit[] = []
    Object.values(data.starter_kit_benefits).forEach((categoryBenefits: any) => {
      allStarterKitBenefits.push(...categoryBenefits)
    })
    
    const allMonthlyBenefits: Benefit[] = []
    Object.values(data.monthly_benefits).forEach((categoryBenefits: any) => {
      allMonthlyBenefits.push(...categoryBenefits)
    })
    
    // Combine all benefits for display
    allBenefits.value = [...allStarterKitBenefits, ...allMonthlyBenefits]
    hasStarterKit.value = data.has_starter_kit
    userTier.value = data.user_tier
  } catch (error) {
    console.error('Failed to fetch benefits:', error)
  }
}

onMounted(() => {
  fetchBenefits()
})
</script>
