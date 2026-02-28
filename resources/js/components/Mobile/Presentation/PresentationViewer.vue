<template>
  <div 
    class="fixed inset-0"
    style="z-index: 999999 !important;"
    @touchstart="handleTouchStart"
    @touchend="handleTouchEnd"
    @mousemove="showControls"
    @click="showControls"
  >
    <!-- Slides Container - Full Screen -->
    <div class="absolute inset-0 overflow-hidden">
      <div 
        class="h-full flex transition-transform duration-300 ease-out"
        :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
      >
        <div 
          v-for="(slide, index) in slides" 
          :key="index"
          class="h-full w-full flex-shrink-0 overflow-y-auto overscroll-contain"
        >
          <component 
            :is="slide.component" 
            v-bind="slide.props"
            :referral-link="referralLink"
            :referral-code="referralCode"
          />
        </div>
      </div>
    </div>

    <!-- Top Control Bar - Overlaid with fade transition -->
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 -translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-300"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-4"
    >
      <div 
        v-show="controlsVisible"
        class="absolute top-0 left-0 right-0 flex items-center justify-between px-4 py-3 z-50 safe-area-top"
      >
        <!-- Left: Share & Download Buttons -->
        <div class="flex items-center gap-2">
          <button
            @click="sharePresentation"
            class="p-2.5 bg-black/30 backdrop-blur-sm rounded-full hover:bg-black/50 transition-colors active:scale-95"
            aria-label="Share referral link"
          >
            <ShareIcon class="h-5 w-5 text-white" aria-hidden="true" />
          </button>
          
          <!-- Download Menu -->
          <div class="relative">
            <button
              @click="showDownloadMenu = !showDownloadMenu"
              :disabled="dataLoading"
              class="p-2.5 bg-black/30 backdrop-blur-sm rounded-full hover:bg-black/50 transition-colors active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
              aria-label="Download presentation"
              title="Download presentation"
            >
              <ArrowDownTrayIcon class="h-5 w-5 text-white" aria-hidden="true" />
            </button>
            
            <!-- Download dropdown -->
            <Transition
              enter-active-class="transition-all duration-200"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition-all duration-150"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
            >
              <div 
                v-show="showDownloadMenu"
                class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden z-50"
              >
                <button
                  @click="downloadPowerPoint(); showDownloadMenu = false"
                  :disabled="isDownloading"
                  class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                    <path d="M14 2v6h6"/>
                    <path d="M10 12h4v1h-4v-1zm0 2h4v1h-4v-1zm0 2h4v1h-4v-1z"/>
                  </svg>
                  <span class="font-medium">PowerPoint (.pptx)</span>
                </button>
                <button
                  @click="downloadPDF(); showDownloadMenu = false"
                  class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2 border-t border-gray-100"
                >
                  <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                    <path d="M14 2v6h6"/>
                  </svg>
                  <span class="font-medium">PDF</span>
                </button>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Center: Slide Counter -->
        <div class="text-white text-sm font-medium px-3 py-1 bg-black/30 backdrop-blur-sm rounded-full">
          {{ currentSlide + 1 }} / {{ slides.length }}
        </div>

        <!-- Right: Controls -->
        <div class="flex items-center gap-2">
          <button
            @click="toggleFullscreen"
            class="p-2.5 bg-black/30 backdrop-blur-sm rounded-full hover:bg-black/50 transition-colors active:scale-95"
            aria-label="Toggle fullscreen"
          >
            <ArrowsPointingOutIcon v-if="!isFullscreen" class="h-5 w-5 text-white" aria-hidden="true" />
            <ArrowsPointingInIcon v-else class="h-5 w-5 text-white" aria-hidden="true" />
          </button>
          <button
            @click="$emit('close')"
            class="p-2.5 bg-black/30 backdrop-blur-sm rounded-full hover:bg-black/50 transition-colors active:scale-95"
            aria-label="Close presentation"
          >
            <XMarkIcon class="h-5 w-5 text-white" aria-hidden="true" />
          </button>
        </div>
      </div>
    </Transition>

    <!-- Bottom Navigation Bar - Overlaid with fade transition -->
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-300"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-4"
    >
      <div 
        v-show="controlsVisible"
        class="absolute bottom-0 left-0 right-0 px-4 py-3 z-50 safe-area-bottom"
      >
        <div class="flex items-center justify-between gap-2">
          <!-- Previous Button -->
          <button
            @click="prevSlide"
            :disabled="currentSlide === 0"
            class="flex items-center gap-1.5 px-4 py-2.5 rounded-full transition-all min-w-[80px] justify-center"
            :class="currentSlide === 0 
              ? 'bg-black/20 text-white/30 cursor-not-allowed' 
              : 'bg-black/30 backdrop-blur-sm text-white hover:bg-black/50 active:scale-95'"
            aria-label="Previous slide"
          >
            <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
            <span class="text-sm font-medium">Prev</span>
          </button>

          <!-- Navigation Dots (scrollable) -->
          <div class="flex-1 flex justify-center overflow-x-auto px-2 scrollbar-hide">
            <div class="flex gap-1.5 bg-black/30 backdrop-blur-sm rounded-full px-3 py-2">
              <button
                v-for="(_, index) in slides"
                :key="index"
                @click="goToSlide(index)"
                class="flex-shrink-0 h-2 rounded-full transition-all duration-200"
                :class="currentSlide === index ? 'bg-white w-5' : 'bg-white/40 hover:bg-white/60 w-2'"
                :aria-label="`Go to slide ${index + 1}`"
              />
            </div>
          </div>

          <!-- Next Button -->
          <button
            @click="nextSlide"
            :disabled="currentSlide === slides.length - 1"
            class="flex items-center gap-1.5 px-4 py-2.5 rounded-full transition-all min-w-[80px] justify-center"
            :class="currentSlide === slides.length - 1 
              ? 'bg-black/20 text-white/30 cursor-not-allowed' 
              : 'bg-black/30 backdrop-blur-sm text-white hover:bg-black/50 active:scale-95'"
            aria-label="Next slide"
          >
            <span class="text-sm font-medium">Next</span>
            <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, shallowRef, computed } from 'vue';
import { 
  XMarkIcon, 
  ChevronLeftIcon, 
  ChevronRightIcon,
  ArrowsPointingOutIcon,
  ArrowsPointingInIcon,
  ShareIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';
import { usePresentationData } from '@/composables/usePresentationData';
import { usePresentationExport } from '@/composables/usePresentationExport';
import { useAlert } from '@/composables/useAlert';

// Import slide components
import WelcomeSlide from './slides/WelcomeSlide.vue';
import AboutUsSlide from './slides/AboutUsSlide.vue';
import HowItWorksSlide from './slides/HowItWorksSlide.vue';
import StarterKitsSlide from './slides/StarterKitsSlide.vue';
import EarningOverviewSlide from './slides/EarningOverviewSlide.vue';
import ReferralBonusSlide from './slides/ReferralBonusSlide.vue';
import MatrixSystemSlide from './slides/MatrixSystemSlide.vue';
import LevelCommissionsSlide from './slides/LevelCommissionsSlide.vue';
import PerformanceBonusSlide from './slides/PerformanceBonusSlide.vue';
import PositionBonusSlide from './slides/PositionBonusSlide.vue';
import LgrSlide from './slides/LgrSlide.vue';
import CommunityRewardsSlide from './slides/CommunityRewardsSlide.vue';
import VentureBuilderSlide from './slides/VentureBuilderSlide.vue';
import ProductsServicesSlide from './slides/ProductsServicesSlide.vue';
import SuccessPathSlide from './slides/SuccessPathSlide.vue';
import JoinNowSlide from './slides/JoinNowSlide.vue';

const props = defineProps<{
  referralLink: string;
  referralCode: string;
  userName?: string;
}>();

const emit = defineEmits<{
  close: [];
  share: [];
}>();

const currentSlide = ref(0);
const isFullscreen = ref(false);
const touchStartX = ref(0);
const touchEndX = ref(0);
const controlsVisible = ref(true);
const hideControlsTimeout = ref<number | null>(null);
const showDownloadMenu = ref(false);

// Fetch presentation data
const { data: presentationData, loading: dataLoading } = usePresentationData();

// Export functionality (Updated: 2026-02-28)
const { generatePowerPoint, generatePDF } = usePresentationExport();
const { success, error: showError, loading, close } = useAlert();
const isDownloading = ref(false);

// Define slides with dynamic data
const slides = computed(() => {
  const slideData = presentationData.value || {
    tiers: [],
    commission_rates: [],
    performance_tiers: [],
    matrix: { width: 3, depth: 7, total_capacity: 3279 },
    referral_bonus: { rate: 15, commission_base: 50 }
  };

  return [
    { component: WelcomeSlide, props: {} },
    { component: AboutUsSlide, props: {} },
    { component: HowItWorksSlide, props: {} },
    { component: StarterKitsSlide, props: { tiers: slideData.tiers } },
    { component: EarningOverviewSlide, props: {} },
    { component: ReferralBonusSlide, props: { 
      referralBonus: slideData.referral_bonus,
      tiers: slideData.tiers 
    } },
    { component: MatrixSystemSlide, props: { 
      matrix: slideData.matrix,
      commissionRates: slideData.commission_rates 
    } },
    { component: LevelCommissionsSlide, props: { 
      commissionRates: slideData.commission_rates,
      matrix: slideData.matrix 
    } },
    { component: PerformanceBonusSlide, props: { 
      commissionRates: slideData.commission_rates 
    } },
    { component: PositionBonusSlide, props: {} },
    { component: LgrSlide, props: { tiers: slideData.tiers } },
    { component: CommunityRewardsSlide, props: {} },
    { component: VentureBuilderSlide, props: {} },
    { component: ProductsServicesSlide, props: {} },
    { component: SuccessPathSlide, props: {} },
    { component: JoinNowSlide, props: { userName: props.userName } },
  ];
});

const goToSlide = (index: number) => {
  currentSlide.value = index;
  showControls();
};

const nextSlide = () => {
  if (currentSlide.value < slides.value.length - 1) {
    currentSlide.value++;
    showControls();
  }
};

const prevSlide = () => {
  if (currentSlide.value > 0) {
    currentSlide.value--;
    showControls();
  }
};

// Show controls and set auto-hide timer
const showControls = () => {
  controlsVisible.value = true;
  
  // Clear existing timeout
  if (hideControlsTimeout.value) {
    clearTimeout(hideControlsTimeout.value);
  }
  
  // Set new timeout to hide controls after 3 seconds
  hideControlsTimeout.value = window.setTimeout(() => {
    controlsVisible.value = false;
    showDownloadMenu.value = false;
  }, 3000);
};

// Touch handling for swipe
const handleTouchStart = (e: TouchEvent) => {
  touchStartX.value = e.touches[0].clientX;
  showControls();
};

const handleTouchEnd = (e: TouchEvent) => {
  touchEndX.value = e.changedTouches[0].clientX;
  const diff = touchStartX.value - touchEndX.value;
  
  if (Math.abs(diff) > 50) {
    if (diff > 0) {
      nextSlide();
    } else {
      prevSlide();
    }
  }
};

// Keyboard navigation
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'ArrowRight' || e.key === ' ') {
    nextSlide();
  } else if (e.key === 'ArrowLeft') {
    prevSlide();
  } else if (e.key === 'Escape') {
    emit('close');
  }
  showControls();
};

const toggleFullscreen = async () => {
  try {
    if (!document.fullscreenElement) {
      await document.documentElement.requestFullscreen();
      isFullscreen.value = true;
    } else {
      await document.exitFullscreen();
      isFullscreen.value = false;
    }
  } catch (err) {
    console.log('Fullscreen not available');
  }
  showControls();
};

const sharePresentation = async () => {
  if (navigator.share) {
    try {
      await navigator.share({
        title: 'Join MyGrowNet',
        text: 'Discover how to Learn, Earn, and Grow with MyGrowNet!',
        url: props.referralLink,
      });
    } catch (err) {
      // User cancelled or error
    }
  } else {
    await navigator.clipboard.writeText(props.referralLink);
    emit('share');
  }
  showControls();
};

const downloadPowerPoint = async () => {
  if (isDownloading.value) return;
  
  try {
    isDownloading.value = true;
    
    // Show initial loading
    loading('Preparing PowerPoint...');
    
    // Track progress without closing/reopening the modal
    let currentProgress = 0;
    const updateProgress = (current: number, total: number) => {
      currentProgress = current;
      // Update the text content directly without closing the modal
      const swalText = document.querySelector('.swal2-html-container');
      if (swalText) {
        swalText.textContent = `Generating PowerPoint... (${current}/${total} slides)`;
      }
    };
    
    await generatePowerPoint(props.referralCode, updateProgress);
    
    close();
    success('PowerPoint downloaded successfully!');
  } catch (error) {
    console.error('Error generating PowerPoint:', error);
    close();
    showError('Failed to generate PowerPoint. Please try again.');
  } finally {
    isDownloading.value = false;
    showControls();
  }
};

const downloadPDF = async () => {
  if (isDownloading.value) return;
  
  try {
    isDownloading.value = true;
    
    // Show initial loading
    loading('Preparing PDF...');
    
    // Track progress without closing/reopening the modal
    let currentProgress = 0;
    const updateProgress = (current: number, total: number) => {
      currentProgress = current;
      // Update the text content directly without closing the modal
      const swalText = document.querySelector('.swal2-html-container');
      if (swalText) {
        swalText.textContent = `Generating PDF... (${current}/${total} slides)`;
      }
    };
    
    await generatePDF(updateProgress);
    
    close();
    success('PDF downloaded successfully!');
  } catch (error) {
    console.error('Error generating PDF:', error);
    close();
    showError('Failed to generate PDF. Please try again.');
  } finally {
    isDownloading.value = false;
    showControls();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
  // Prevent body scroll
  document.body.style.overflow = 'hidden';
  document.body.style.position = 'fixed';
  document.body.style.width = '100%';
  document.body.style.height = '100%';
  document.body.style.top = '0';
  document.body.style.left = '0';
  
  // Show controls initially, then auto-hide
  showControls();
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
  // Clear timeout
  if (hideControlsTimeout.value) {
    clearTimeout(hideControlsTimeout.value);
  }
  // Restore body styles
  document.body.style.overflow = '';
  document.body.style.position = '';
  document.body.style.width = '';
  document.body.style.height = '';
  document.body.style.top = '';
  document.body.style.left = '';
});
</script>

<style scoped>
/* Safe area padding for notched devices */
.safe-area-top {
  padding-top: max(0.75rem, env(safe-area-inset-top));
}

.safe-area-bottom {
  padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
}

/* Hide scrollbar but allow scrolling */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
