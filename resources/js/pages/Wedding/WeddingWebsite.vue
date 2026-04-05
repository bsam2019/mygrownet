<template>
  <!-- Open Graph Meta Tags for Social Sharing -->
  <Head>
    <title>{{ ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding` }}</title>
    <meta name="description" :content="ogMeta.description || `You are invited to celebrate wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta property="og:type" :content="ogMeta.type || 'website'" />
    <meta property="og:url" :content="ogMeta.url || ''" />
    <meta property="og:title" :content="ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding`" />
    <meta property="og:description" :content="ogMeta.description || `You are invited to celebrate wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta property="og:image" :content="ogMeta.image || ''" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" :content="ogMeta.url || ''" />
    <meta name="twitter:title" :content="ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding`" />
    <meta name="twitter:description" :content="ogMeta.description || `You are invited to celebrate wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta name="twitter:image" :content="ogMeta.image || ''" />
    <meta property="og:site_name" content="Wedding Invitation" />
  </Head>

  <!-- Dynamic Template Loading -->
  <component 
    :is="currentTemplateComponent"
    :weddingEvent="weddingEvent"
    :template="template"
    :galleryImages="galleryImages"
    :ogMeta="ogMeta"
    :isPreview="false"
  />
</template>

<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

// Import all template components
import ModernMinimal from './Templates/ModernMinimal.vue'
import ElegantGold from './Templates/ElegantGold.vue'
import GardenParty from './Templates/GardenParty.vue'
import SunsetRomance from './Templates/SunsetRomance.vue'
import FloraClassic from './Templates/FloraClassic.vue'
import BirthdayBash from './Templates/BirthdayBash.vue'
import AnniversaryElegance from './Templates/AnniversaryElegance.vue'

const props = defineProps({
  weddingEvent: Object,
  template: Object,
  galleryImages: Array,
  ogMeta: {
    type: Object,
    default: () => ({})
  },
  isPreview: Boolean,
})

// Map template slugs to components
const templateComponents = {
  'modern-minimal': ModernMinimal,
  'elegant-gold': ElegantGold,
  'garden-party': GardenParty,
  'sunset-romance': SunsetRomance,
  'flora-classic': FloraClassic,
  'birthday-bash': BirthdayBash,
  'anniversary-elegance': AnniversaryElegance,
}

// Dynamic component loading based on template slug
const currentTemplateComponent = computed(() => {
  const slug = props.template?.slug || 'flora-classic'
  return templateComponents[slug] || FloraClassic
})
</script>
