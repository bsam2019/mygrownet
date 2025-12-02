<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { 
  UserCircleIcon, 
  ClipboardDocumentCheckIcon, 
  ChartBarIcon,
  ChatBubbleLeftRightIcon,
  ShoppingCartIcon,
  Cog6ToothIcon,
  CubeIcon,
  WalletIcon,
  BanknotesIcon,
  HeartIcon,
  AcademicCapIcon,
  BuildingOfficeIcon,
  HomeIcon,
  UsersIcon,
  CalendarIcon,
  DocumentTextIcon,
  GiftIcon,
  TruckIcon,
  ShieldCheckIcon,
  SparklesIcon
} from '@heroicons/vue/24/solid';

interface Module {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  color: string | null;
  thumbnail: string | null;
  has_access: boolean;
  is_subscribed: boolean;
  subscription_status: string | null;
  requires_subscription: boolean;
  primary_route: string;
  status: string;
}

interface Props {
  module: Module;
}

const props = defineProps<Props>();

const handleClick = () => {
  if (props.module.has_access) {
    router.visit(props.module.primary_route);
  } else {
    alert('Subscription feature coming soon!');
  }
};

// Icon mapping for modules - using solid icons
const iconMap: Record<string, any> = {
  'mlm-dashboard': UsersIcon,
  'mygrownet-core': HomeIcon,
  'dashboard': HomeIcon,
  'task-management': ClipboardDocumentCheckIcon,
  'smart-accounting': ChartBarIcon,
  'sme-accounting': ChartBarIcon,
  'accounting': ChartBarIcon,
  'messaging': ChatBubbleLeftRightIcon,
  'marketplace': ShoppingCartIcon,
  'shop': ShoppingCartIcon,
  'settings': Cog6ToothIcon,
  'mygrow-save': WalletIcon,
  'wallet': WalletIcon,
  'personal-finance': BanknotesIcon,
  'finance': BanknotesIcon,
  'wedding-planner': HeartIcon,
  'wedding': HeartIcon,
  'learning': AcademicCapIcon,
  'education': AcademicCapIcon,
  'enterprise': BuildingOfficeIcon,
  'business': BuildingOfficeIcon,
  'calendar': CalendarIcon,
  'documents': DocumentTextIcon,
  'rewards': GiftIcon,
  'loyalty': GiftIcon,
  'delivery': TruckIcon,
  'logistics': TruckIcon,
  'security': ShieldCheckIcon,
  'premium': SparklesIcon,
};

const getIcon = () => iconMap[props.module.slug] || CubeIcon;
</script>

<template>
  <button
    @click="handleClick"
    class="group relative bg-white rounded-2xl p-5 sm:p-6 flex flex-col items-center justify-center gap-3 sm:gap-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 active:scale-95 shadow-md border border-gray-100"
  >
    <!-- Icon Container with colored background -->
    <div 
      class="w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-110"
      :style="{ backgroundColor: module.color || '#3B82F6' }"
    >
      <component 
        :is="getIcon()" 
        class="w-7 h-7 sm:w-8 sm:h-8 text-white"
      />
    </div>
    
    <!-- Module Name -->
    <span class="text-gray-800 text-center font-semibold text-sm leading-tight">
      {{ module.name }}
    </span>

    <!-- Status Badge -->
    <span 
      v-if="module.status === 'beta'" 
      class="absolute top-2 right-2 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700 rounded-full"
    >
      Beta
    </span>
    
    <span 
      v-if="module.status === 'coming_soon'" 
      class="absolute top-2 right-2 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-600 rounded-full"
    >
      Soon
    </span>

    <!-- Access indicator -->
    <span 
      v-if="!module.has_access && module.requires_subscription" 
      class="absolute bottom-2 right-2 w-2 h-2 bg-orange-400 rounded-full"
      title="Subscription required"
    />
  </button>
</template>
