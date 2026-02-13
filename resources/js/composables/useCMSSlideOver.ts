import { ref, computed } from 'vue'

export type SlideOverType = 'job' | 'customer' | 'invoice' | null

export interface SlideOverConfig {
  title: string
  subtitle?: string
  size?: 'sm' | 'md' | 'lg' | 'xl'
}

const slideOverConfigs: Record<Exclude<SlideOverType, null>, SlideOverConfig> = {
  job: {
    title: 'Create New Job',
    subtitle: 'Add a new job to the system',
    size: 'lg',
  },
  customer: {
    title: 'Add New Customer',
    subtitle: 'Register a new customer',
    size: 'md',
  },
  invoice: {
    title: 'Create New Invoice',
    subtitle: 'Generate an invoice for a customer',
    size: 'xl',
  },
}

export function useCMSSlideOver() {
  const isOpen = ref(false)
  const currentType = ref<SlideOverType>(null)

  const config = computed(() => {
    if (!currentType.value) {
      return { title: '', subtitle: '', size: 'md' as const }
    }
    return slideOverConfigs[currentType.value]
  })

  const open = (type: Exclude<SlideOverType, null>) => {
    currentType.value = type
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
    // Delay resetting type to allow for exit animation
    setTimeout(() => {
      currentType.value = null
    }, 300)
  }

  return {
    isOpen,
    currentType,
    config,
    open,
    close,
  }
}
