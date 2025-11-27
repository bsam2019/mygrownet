<template>
  <div class="min-h-full flex flex-col px-6 py-8 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 relative overflow-hidden">
    <!-- Background effects -->
    <div class="absolute inset-0">
      <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-80 h-80 bg-purple-500/20 rounded-full -ml-40 -mb-40 blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-blue-400/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Confetti animation -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="confetti confetti-1">🎉</div>
      <div class="confetti confetti-2">✨</div>
      <div class="confetti confetti-3">🌟</div>
      <div class="confetti confetti-4">💫</div>
      <div class="confetti confetti-5">🎊</div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex-1 flex flex-col items-center justify-center text-center">
      <!-- Logo -->
      <div class="mb-6">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl">
          <img 
            src="/logo.png" 
            alt="MyGrowNet" 
            class="w-16 h-auto object-contain"
          />
        </div>
      </div>

      <!-- Main CTA -->
      <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
        Ready to Start?
      </h2>
      <p class="text-xl text-blue-100 mb-8 max-w-md">
        Join thousands of members building their future with MyGrowNet
      </p>

      <!-- Benefits recap -->
      <div class="grid grid-cols-3 gap-4 mb-8 w-full max-w-sm">
        <div class="text-center">
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-2">
            <AcademicCapIcon class="h-6 w-6 text-white" />
          </div>
          <p class="text-white text-xs font-medium">Learn</p>
        </div>
        <div class="text-center">
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-2">
            <CurrencyDollarIcon class="h-6 w-6 text-white" />
          </div>
          <p class="text-white text-xs font-medium">Earn</p>
        </div>
        <div class="text-center">
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-2">
            <ArrowTrendingUpIcon class="h-6 w-6 text-white" />
          </div>
          <p class="text-white text-xs font-medium">Grow</p>
        </div>
      </div>

      <!-- QR Code placeholder -->
      <div v-if="referralCode" class="bg-white rounded-2xl p-4 mb-6 shadow-xl">
        <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center mb-2">
          <!-- QR Code would be generated here -->
          <QrCodeIcon class="h-20 w-20 text-gray-400" />
        </div>
        <p class="text-gray-600 text-xs">Scan to join</p>
      </div>

      <!-- Referral link -->
      <div v-if="referralLink" class="w-full max-w-sm mb-6">
        <p class="text-blue-200 text-sm mb-2">Or use this link:</p>
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20">
          <p class="text-white text-sm font-mono truncate">{{ referralLink }}</p>
        </div>
        <button
          @click="copyLink"
          class="mt-3 w-full py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-gray-100 transition-colors flex items-center justify-center gap-2"
        >
          <ClipboardDocumentIcon class="h-5 w-5" />
          {{ copied ? 'Copied!' : 'Copy Link' }}
        </button>
      </div>

      <!-- Share buttons -->
      <div class="flex items-center gap-3">
        <button
          @click="shareWhatsApp"
          class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center hover:bg-green-600 transition-colors shadow-lg"
          aria-label="Share on WhatsApp"
        >
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
        </button>
        <button
          @click="shareFacebook"
          class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors shadow-lg"
          aria-label="Share on Facebook"
        >
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
          </svg>
        </button>
        <button
          @click="shareTwitter"
          class="w-12 h-12 bg-black rounded-full flex items-center justify-center hover:bg-gray-800 transition-colors shadow-lg"
          aria-label="Share on X"
        >
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
          </svg>
        </button>
      </div>

      <!-- Contact info -->
      <div class="mt-8 text-center">
        <p class="text-blue-200 text-sm">Questions? Contact us:</p>
        <p class="text-white font-medium">support@mygrownet.com</p>
      </div>
    </div>

    <!-- Bottom tagline -->
    <div class="relative z-10 text-center mt-auto">
      <p class="text-white/60 text-sm">
        © {{ new Date().getFullYear() }} MyGrowNet. Learn. Earn. Grow.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  AcademicCapIcon,
  CurrencyDollarIcon,
  ArrowTrendingUpIcon,
  QrCodeIcon,
  ClipboardDocumentIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
  referralLink?: string;
  referralCode?: string;
}>();

const copied = ref(false);

const copyLink = async () => {
  if (props.referralLink) {
    await navigator.clipboard.writeText(props.referralLink);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  }
};

const shareWhatsApp = () => {
  const text = encodeURIComponent(`Join me on MyGrowNet! Learn, Earn, and Grow together. ${props.referralLink || ''}`);
  window.open(`https://wa.me/?text=${text}`, '_blank');
};

const shareFacebook = () => {
  const url = encodeURIComponent(props.referralLink || window.location.origin);
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
};

const shareTwitter = () => {
  const text = encodeURIComponent('Join me on MyGrowNet! Learn, Earn, and Grow together.');
  const url = encodeURIComponent(props.referralLink || window.location.origin);
  window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
};
</script>

<style scoped>
.confetti {
  @apply absolute text-2xl;
  animation: confetti-fall 4s ease-in-out infinite;
}

.confetti-1 { top: -10%; left: 10%; animation-delay: 0s; }
.confetti-2 { top: -10%; left: 30%; animation-delay: 0.5s; }
.confetti-3 { top: -10%; left: 50%; animation-delay: 1s; }
.confetti-4 { top: -10%; left: 70%; animation-delay: 1.5s; }
.confetti-5 { top: -10%; left: 90%; animation-delay: 2s; }

@keyframes confetti-fall {
  0% {
    transform: translateY(0) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: translateY(100vh) rotate(720deg);
    opacity: 0;
  }
}
</style>
