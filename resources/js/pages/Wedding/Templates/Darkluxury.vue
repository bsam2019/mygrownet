<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500&family=Lora:ital,wght@0,400;1,400&family=Jost:wght@300;400&display=swap" rel="stylesheet" />
  </Head>

  <div class="t3-root">

    <!-- Top bar -->
    <header class="t3-topbar">
      <div class="t3-topbar-inner">
        <div class="t3-monogram">{{ monogram }}</div>
        <nav class="t3-nav">
          <button v-for="tab in navTabs" :key="tab.id"
            @click="activeTab = tab.id"
            :class="['t3-navbtn', activeTab === tab.id ? 't3-navbtn--active' : '']">
            {{ tab.label }}
          </button>
        </nav>
        <button @click="showRSVPModal = true" class="t3-rsvp-btn">RSVP</button>
      </div>
    </header>

    <!-- Mobile menu -->
    <div class="t3-mobile-bar">
      <span class="t3-mobile-title">{{ weddingEvent.groom_name }} & {{ weddingEvent.bride_name }}</span>
      <button @click="mobileOpen = !mobileOpen" class="t3-hamburger">
        <span></span><span></span><span></span>
      </button>
    </div>
    <Transition name="t3-menu">
      <div v-if="mobileOpen" class="t3-mobile-drawer">
        <button v-for="tab in navTabs" :key="tab.id"
          @click="activeTab = tab.id; mobileOpen = false"
          class="t3-mobile-item">{{ tab.label }}</button>
        <button @click="showRSVPModal = true; mobileOpen = false" class="t3-mobile-rsvp">RSVP</button>
      </div>
    </Transition>

    <!-- HOME -->
    <section v-show="activeTab === 'home'" class="t3-home">
      <!-- Cinematic hero -->
      <div class="t3-cinema">
        <img :src="weddingEvent.hero_image"
             :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
             class="t3-cinema-img" />
        <div class="t3-cinema-overlay"></div>
        <div class="t3-cinema-content">
          <p class="t3-cinema-eyebrow">You are cordially invited</p>
          <h1 class="t3-cinema-names">
            {{ weddingEvent.groom_name }}
            <em class="t3-and">&amp;</em>
            {{ weddingEvent.bride_name }}
          </h1>
          <div class="t3-gold-rule"></div>
          <p class="t3-cinema-date">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
          <p class="t3-cinema-venue">{{ weddingEvent.venue_name }}</p>
        </div>
      </div>

      <!-- Details strip -->
      <div class="t3-details-strip">
        <div class="t3-detail-item">
          <span class="t3-detail-label">Ceremony</span>
          <span class="t3-detail-value">{{ weddingEvent.ceremony_time }}</span>
        </div>
        <div class="t3-strip-divider"></div>
        <div class="t3-detail-item">
          <span class="t3-detail-label">Reception</span>
          <span class="t3-detail-value">{{ weddingEvent.reception_time }}</span>
        </div>
        <div class="t3-strip-divider"></div>
        <div class="t3-detail-item">
          <span class="t3-detail-label">Location</span>
          <span class="t3-detail-value">{{ weddingEvent.venue_name }}</span>
        </div>
        <div class="t3-strip-divider t3-strip-divider--hide"></div>
        <button @click="showRSVPModal = true" class="t3-strip-rsvp">Confirm attendance</button>
      </div>

      <!-- Countdown -->
      <div v-if="countdown" class="t3-countdown-section">
        <div class="t3-countdown-label">Time remaining</div>
        <div class="t3-countdown-row">
          <div v-for="unit in countdownUnits" :key="unit.key" class="t3-count-cell">
            <div class="t3-count-num">{{ String(countdown[unit.key]).padStart(2, '0') }}</div>
            <div class="t3-count-unit">{{ unit.label }}</div>
          </div>
        </div>
      </div>

      <!-- Story excerpt (bento grid) -->
      <div class="t3-bento">
        <div class="t3-bento-card t3-bento-large" :style="{ backgroundImage: `url(${weddingEvent.story_image || weddingEvent.hero_image})` }">
          <div class="t3-bento-overlay"></div>
          <div class="t3-bento-text">
            <p class="t3-bento-tag">Our Story</p>
            <p class="t3-bento-excerpt">{{ truncate(weddingEvent.how_we_met, 150) }}</p>
            <button @click="activeTab = 'story'" class="t3-bento-link">Read more</button>
          </div>
        </div>
        <div class="t3-bento-small-col">
          <div class="t3-bento-card t3-bento-info">
            <p class="t3-bento-tag">Date</p>
            <p class="t3-bento-info-val">{{ formatShortDate(weddingEvent.wedding_date) }}</p>
          </div>
          <div class="t3-bento-card t3-bento-venue">
            <p class="t3-bento-tag">Venue</p>
            <p class="t3-bento-info-val">{{ weddingEvent.venue_name }}</p>
            <p class="t3-bento-info-sub">{{ weddingEvent.venue_address }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- STORY -->
    <section v-show="activeTab === 'story'" class="t3-page">
      <div class="t3-page-hero" :style="{ backgroundImage: `url(${weddingEvent.hero_image})` }">
        <div class="t3-page-hero-overlay"></div>
        <h2 class="t3-page-hero-title">Our Story</h2>
      </div>
      <div class="t3-page-body">
        <div class="t3-story-grid">
          <div class="t3-story-text">
            <p class="t3-chapter-mark">I</p>
            <h3 class="t3-chapter-title">How We Met</h3>
            <p class="t3-prose">{{ weddingEvent.how_we_met }}</p>
          </div>
          <div class="t3-story-image">
            <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" />
          </div>
        </div>

        <div class="t3-gold-divider">
          <div class="t3-gold-line"></div>
          <span class="t3-gold-diamond">◆</span>
          <div class="t3-gold-line"></div>
        </div>

        <div class="t3-story-grid t3-story-grid--flip">
          <div class="t3-story-image">
            <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" />
          </div>
          <div class="t3-story-text">
            <p class="t3-chapter-mark">II</p>
            <h3 class="t3-chapter-title">The Proposal</h3>
            <p class="t3-prose">{{ weddingEvent.proposal_story }}</p>
          </div>
        </div>

        <div v-if="galleryImages.length > 1" class="t3-gallery-masonry">
          <div v-for="(img, i) in galleryImages.slice(1)" :key="i" class="t3-masonry-item">
            <img :src="img.url" :alt="`Memory ${i+2}`" />
          </div>
        </div>
      </div>
    </section>

    <!-- PROGRAM -->
    <section v-show="activeTab === 'program'" class="t3-page">
      <div class="t3-page-hero t3-page-hero--dark">
        <h2 class="t3-page-hero-title">Programme</h2>
        <p class="t3-page-hero-sub">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
      </div>
      <div class="t3-page-body">
        <div class="t3-timeline">
          <div class="t3-timeline-item">
            <div class="t3-timeline-node">
              <div class="t3-node-dot"></div>
              <div class="t3-node-line"></div>
            </div>
            <div class="t3-timeline-content">
              <div class="t3-timeline-time">{{ weddingEvent.ceremony_time }}</div>
              <h3 class="t3-timeline-title">Wedding Ceremony</h3>
              <p class="t3-timeline-venue">{{ weddingEvent.venue_name }}</p>
              <p class="t3-timeline-address">{{ weddingEvent.venue_address }}</p>
            </div>
          </div>
          <div class="t3-timeline-item">
            <div class="t3-timeline-node">
              <div class="t3-node-dot"></div>
            </div>
            <div class="t3-timeline-content">
              <div class="t3-timeline-time">{{ weddingEvent.reception_time }}</div>
              <h3 class="t3-timeline-title">Reception &amp; Celebration</h3>
              <p class="t3-timeline-venue">{{ weddingEvent.reception_venue }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- LOCATION -->
    <section v-show="activeTab === 'location'" class="t3-page">
      <div class="t3-page-hero t3-page-hero--dark">
        <h2 class="t3-page-hero-title">Venue</h2>
      </div>
      <div class="t3-page-body">
        <div class="t3-venue-info">
          <h3 class="t3-venue-name">{{ weddingEvent.venue_name }}</h3>
          <p class="t3-venue-address">{{ weddingEvent.venue_address }}</p>
        </div>
        <div class="t3-map-card" @click="openGoogleMaps">
          <div class="t3-map-body">
            <div class="t3-map-pin-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
              </svg>
            </div>
            <span>Open Map</span>
          </div>
        </div>
        <div class="t3-dir-row">
          <a :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
             target="_blank" class="t3-dir-btn">Get directions →</a>
        </div>
      </div>
    </section>

    <RSVPModal
      :isOpen="showRSVPModal"
      :weddingEventId="weddingEvent.id"
      @close="showRSVPModal = false"
      @submitted="showRSVPModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import RSVPModal from '@/components/Wedding/RSVPModal.vue'

const props = defineProps({
  weddingEvent: Object,
  template: Object,
  galleryImages: Array,
  ogMeta: Object,
  isPreview: Boolean,
})

const activeTab = ref('home')
const showRSVPModal = ref(false)
const mobileOpen = ref(false)
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })

const navTabs = [
  { id: 'home', label: 'Home' },
  { id: 'story', label: 'Our Story' },
  { id: 'program', label: 'Programme' },
  { id: 'location', label: 'Venue' },
]
const countdownUnits = [
  { key: 'days', label: 'Days' },
  { key: 'hours', label: 'Hours' },
  { key: 'minutes', label: 'Mins' },
  { key: 'seconds', label: 'Secs' },
]

const monogram = computed(() => {
  const g = props.weddingEvent.groom_name?.[0] || ''
  const b = props.weddingEvent.bride_name?.[0] || ''
  return `${g}${b}`
})

const truncate = (text, len) => {
  if (!text) return ''
  return text.length > len ? text.slice(0, len).trim() + '…' : text
}

const formatDateFull  = d => new Date(d).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
const formatShortDate = d => new Date(d).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })

const updateCountdown = () => {
  const diff = new Date(props.weddingEvent.wedding_date).getTime() - Date.now()
  if (diff > 0) countdown.value = {
    days:    Math.floor(diff / 86400000),
    hours:   Math.floor((diff % 86400000) / 3600000),
    minutes: Math.floor((diff % 3600000)  / 60000),
    seconds: Math.floor((diff % 60000)    / 1000),
  }
}

const openGoogleMaps = () => {
  window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.weddingEvent.venue_address)}`, '_blank')
}

let timer
onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000) })
onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
.t3-root {
  --dark:    #0e0c0a;
  --dark-mid:#1e1b17;
  --gold:    #c9a84c;
  --gold-dim:#8a7035;
  --light:   #f5f1ea;
  --mid:     #a09585;
  --soft:    #5a5248;

  font-family: 'Jost', sans-serif;
  font-weight: 300;
  background: var(--dark);
  color: var(--light);
  min-height: 100vh;
}

/* ── Top bar ──────────────────────────────────── */
.t3-topbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  background: rgba(14, 12, 10, 0.92);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(201, 168, 76, 0.2);
  display: none;
}
@media (min-width: 768px) { .t3-topbar { display: block; } }

.t3-topbar-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center;
  padding: 0 3rem;
  gap: 2rem;
}

.t3-monogram {
  font-family: 'Cinzel', serif;
  font-size: 1.1rem;
  color: var(--gold);
  letter-spacing: 0.1em;
  padding: 1.25rem 0;
  margin-right: auto;
}

.t3-nav { display: flex; }
.t3-navbtn {
  background: none; border: none; cursor: pointer;
  font-family: 'Jost', sans-serif;
  font-size: 0.68rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--mid);
  padding: 1.5rem 1.25rem;
  transition: color 0.2s;
  position: relative;
}
.t3-navbtn:hover { color: var(--light); }
.t3-navbtn--active { color: var(--gold); }
.t3-navbtn--active::after {
  content: '';
  position: absolute;
  bottom: 0; left: 1.25rem; right: 1.25rem;
  height: 1px; background: var(--gold);
}

.t3-rsvp-btn {
  background: none;
  border: 1px solid var(--gold);
  color: var(--gold);
  font-family: 'Jost', sans-serif;
  font-size: 0.65rem; letter-spacing: 0.25em; text-transform: uppercase;
  padding: 0.6rem 1.5rem; cursor: pointer;
  transition: all 0.2s;
}
.t3-rsvp-btn:hover { background: var(--gold); color: var(--dark); }

/* Mobile bar */
.t3-mobile-bar {
  display: flex; align-items: center; justify-content: space-between;
  position: fixed; top: 0; left: 0; right: 0; z-index: 200;
  background: var(--dark);
  border-bottom: 1px solid rgba(201,168,76,0.2);
  padding: 1rem 1.5rem;
}
@media (min-width: 768px) { .t3-mobile-bar { display: none; } }

.t3-mobile-title {
  font-family: 'Cinzel', serif;
  font-size: 0.75rem;
  color: var(--gold);
  letter-spacing: 0.1em;
}
.t3-hamburger {
  background: none; border: none; cursor: pointer;
  display: flex; flex-direction: column; gap: 4px; padding: 4px;
}
.t3-hamburger span {
  display: block; width: 22px; height: 1px; background: var(--gold);
}

.t3-mobile-drawer {
  position: fixed; top: 53px; left: 0; right: 0; z-index: 199;
  background: var(--dark-mid);
  border-bottom: 1px solid rgba(201,168,76,0.2);
  padding: 2rem 1.5rem;
  display: flex; flex-direction: column; gap: 1.25rem;
}
.t3-mobile-item {
  background: none; border: none; cursor: pointer;
  font-family: 'Jost', sans-serif;
  font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--mid); text-align: left;
}
.t3-mobile-rsvp {
  background: none; border: 1px solid var(--gold); cursor: pointer;
  font-family: 'Jost', sans-serif;
  font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--gold); padding: 0.75rem 0; margin-top: 0.5rem;
}

.t3-menu-enter-active, .t3-menu-leave-active { transition: all 0.25s; }
.t3-menu-enter-from, .t3-menu-leave-to { opacity: 0; transform: translateY(-10px); }

/* ── Home ─────────────────────────────────────── */
.t3-home { padding-top: 0; }
@media (min-width: 768px) { .t3-home { padding-top: 0; } }

/* Cinema hero */
.t3-cinema {
  position: relative;
  height: 100vh;
  min-height: 600px;
  display: flex; align-items: flex-end;
}
.t3-cinema-img {
  position: absolute; inset: 0;
  width: 100%; height: 100%;
  object-fit: cover; object-position: center 20%;
}
.t3-cinema-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, rgba(14,12,10,0.3) 0%, rgba(14,12,10,0.0) 40%, rgba(14,12,10,0.85) 100%);
}
.t3-cinema-content {
  position: relative; z-index: 10;
  padding: 4rem 2.5rem;
  text-align: center;
  width: 100%;
}
@media (min-width: 768px) { .t3-cinema-content { padding: 5rem 6rem; } }

.t3-cinema-eyebrow {
  font-size: 0.62rem; letter-spacing: 0.35em; text-transform: uppercase;
  color: var(--gold); margin: 0 0 1.5rem;
}
.t3-cinema-names {
  font-family: 'Cinzel', serif;
  font-size: clamp(2.2rem, 5vw, 5rem);
  font-weight: 400;
  line-height: 1.2;
  margin: 0 0 1.5rem;
  color: var(--light);
  letter-spacing: 0.05em;
}
.t3-and {
  font-family: 'Lora', serif;
  font-style: italic;
  color: var(--gold);
  font-size: 0.55em;
  vertical-align: middle;
  margin: 0 0.3em;
}
.t3-gold-rule {
  width: 4rem; height: 1px; background: var(--gold);
  margin: 0 auto 1.5rem;
}
.t3-cinema-date {
  font-size: 0.78rem; letter-spacing: 0.15em;
  color: rgba(245,241,234,0.7); margin: 0 0 0.5rem;
}
.t3-cinema-venue {
  font-family: 'Lora', serif;
  font-style: italic;
  font-size: 1.1rem;
  color: var(--gold);
  margin: 0;
}

/* Details strip */
.t3-details-strip {
  background: var(--dark-mid);
  border-top: 1px solid rgba(201,168,76,0.25);
  border-bottom: 1px solid rgba(201,168,76,0.25);
  display: flex; flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0;
  padding: 1.5rem 2rem;
}
.t3-detail-item { display: flex; flex-direction: column; padding: 0.75rem 2rem; text-align: center; }
.t3-detail-label { font-size: 0.58rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--gold-dim); margin-bottom: 0.3rem; }
.t3-detail-value { font-family: 'Lora', serif; font-style: italic; font-size: 0.9rem; color: var(--light); }
.t3-strip-divider { width: 1px; height: 2.5rem; background: rgba(201,168,76,0.2); }
.t3-strip-divider--hide { display: none; }
@media (min-width: 640px) { .t3-strip-divider--hide { display: block; } }

.t3-strip-rsvp {
  background: none; border: 1px solid var(--gold); cursor: pointer;
  font-family: 'Jost', sans-serif;
  font-size: 0.62rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--gold); padding: 0.6rem 1.5rem; margin-left: 1rem;
  transition: all 0.2s;
}
.t3-strip-rsvp:hover { background: var(--gold); color: var(--dark); }

/* Countdown */
.t3-countdown-section {
  text-align: center;
  padding: 4rem 2rem;
}
.t3-countdown-label {
  font-size: 0.6rem; letter-spacing: 0.3em; text-transform: uppercase;
  color: var(--gold-dim); margin-bottom: 2rem;
}
.t3-countdown-row { display: flex; justify-content: center; gap: 2rem; }
.t3-count-cell { display: flex; flex-direction: column; align-items: center; }
.t3-count-num {
  font-family: 'Cinzel', serif;
  font-size: clamp(2.5rem, 5vw, 4rem);
  color: var(--gold);
  line-height: 1;
  position: relative;
}
.t3-count-num::after {
  content: '';
  position: absolute; bottom: -0.5rem; left: 0; right: 0;
  height: 1px; background: rgba(201,168,76,0.3);
}
.t3-count-unit {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--soft); margin-top: 1rem;
}

/* Bento grid */
.t3-bento {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  padding: 0 1.5rem 3rem;
}
@media (min-width: 768px) {
  .t3-bento { grid-template-columns: 2fr 1fr; padding: 0 3rem 4rem; }
}

.t3-bento-card {
  position: relative;
  overflow: hidden;
}
.t3-bento-large {
  min-height: 400px;
  background-size: cover;
  background-position: center;
  display: flex; align-items: flex-end;
}
.t3-bento-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(14,12,10,0.9) 0%, rgba(14,12,10,0.2) 60%);
}
.t3-bento-text {
  position: relative; z-index: 10;
  padding: 2.5rem;
}
.t3-bento-tag {
  font-size: 0.6rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--gold); margin: 0 0 0.75rem;
}
.t3-bento-excerpt { font-family: 'Lora', serif; font-size: 0.9rem; color: rgba(245,241,234,0.8); line-height: 1.7; margin: 0 0 1rem; }
.t3-bento-link {
  background: none; border: none; cursor: pointer;
  font-family: 'Jost', sans-serif;
  font-size: 0.65rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--gold); padding: 0;
}

.t3-bento-small-col { display: flex; flex-direction: column; gap: 1rem; }

.t3-bento-info, .t3-bento-venue {
  background: var(--dark-mid);
  border: 1px solid rgba(201,168,76,0.15);
  padding: 2rem;
  flex: 1;
}
.t3-bento-info-val {
  font-family: 'Lora', serif;
  font-style: italic;
  font-size: 1.1rem;
  color: var(--light);
  margin: 0.5rem 0 0;
}
.t3-bento-info-sub { font-size: 0.78rem; color: var(--mid); margin-top: 0.5rem; }

/* ── Pages ────────────────────────────────────── */
.t3-page { padding-top: 53px; }
@media (min-width: 768px) { .t3-page { padding-top: 57px; } }

.t3-page-hero {
  height: 40vh; min-height: 280px;
  background-size: cover; background-position: center;
  display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
  padding: 3rem 2rem;
  position: relative;
}
.t3-page-hero-overlay {
  position: absolute; inset: 0;
  background: rgba(14,12,10,0.5);
}
.t3-page-hero--dark {
  background: linear-gradient(135deg, var(--dark-mid) 0%, var(--dark) 100%);
}
.t3-page-hero-title {
  font-family: 'Cinzel', serif;
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 400;
  color: var(--light);
  margin: 0 0 0.5rem;
  position: relative; z-index: 10;
  text-align: center;
}
.t3-page-hero-sub {
  font-family: 'Lora', serif;
  font-style: italic;
  font-size: 0.85rem;
  color: var(--gold);
  position: relative; z-index: 10;
  text-align: center;
}

.t3-page-body { padding: 4rem 2rem; max-width: 1000px; margin: 0 auto; }
@media (min-width: 768px) { .t3-page-body { padding: 5rem 4rem; } }

/* Story */
.t3-story-grid {
  display: grid; grid-template-columns: 1fr;
  gap: 3rem; align-items: center; margin-bottom: 4rem;
}
@media (min-width: 768px) {
  .t3-story-grid { grid-template-columns: 1fr 1fr; }
  .t3-story-grid--flip .t3-story-image { order: -1; }
}
.t3-story-text { }
.t3-chapter-mark {
  font-family: 'Cinzel', serif;
  font-size: 4rem; color: rgba(201,168,76,0.2);
  line-height: 1; margin: 0;
}
.t3-chapter-title {
  font-family: 'Lora', serif;
  font-style: italic;
  font-size: 1.6rem; color: var(--light);
  margin: 0.25rem 0 1.25rem;
}
.t3-prose { color: var(--mid); line-height: 1.85; font-size: 0.95rem; }

.t3-story-image img { width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block; }

.t3-gold-divider {
  display: flex; align-items: center; gap: 1.5rem; margin: 3rem 0;
}
.t3-gold-line { flex: 1; height: 1px; background: rgba(201,168,76,0.3); }
.t3-gold-diamond { color: var(--gold-dim); font-size: 0.8rem; }

.t3-gallery-masonry {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  margin-top: 3rem;
}
@media (min-width: 600px) { .t3-gallery-masonry { grid-template-columns: repeat(3, 1fr); } }
.t3-masonry-item img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; }

/* Timeline */
.t3-timeline { display: flex; flex-direction: column; gap: 0; }
.t3-timeline-item { display: grid; grid-template-columns: 2rem 1fr; gap: 2rem; }
.t3-timeline-node { display: flex; flex-direction: column; align-items: center; padding-top: 0.3rem; }
.t3-node-dot {
  width: 10px; height: 10px;
  border: 1px solid var(--gold);
  border-radius: 50%;
  flex-shrink: 0;
}
.t3-node-line { flex: 1; width: 1px; background: rgba(201,168,76,0.25); min-height: 4rem; }
.t3-timeline-content { padding-bottom: 3rem; }
.t3-timeline-time {
  font-size: 0.65rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 0.5rem;
}
.t3-timeline-title {
  font-family: 'Lora', serif; font-style: italic;
  font-size: 1.5rem; color: var(--light); margin: 0 0 0.5rem;
}
.t3-timeline-venue { font-size: 0.9rem; color: var(--mid); margin: 0; }
.t3-timeline-address { font-size: 0.78rem; color: var(--soft); margin: 0.25rem 0 0; }

/* Venue */
.t3-venue-info { margin-bottom: 2.5rem; }
.t3-venue-name {
  font-family: 'Lora', serif; font-style: italic;
  font-size: 1.8rem; color: var(--light); margin: 0 0 0.5rem;
}
.t3-venue-address { color: var(--mid); font-size: 0.9rem; }

.t3-map-card {
  background: var(--dark-mid);
  border: 1px solid rgba(201,168,76,0.2);
  height: 300px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: border-color 0.2s;
}
.t3-map-card:hover { border-color: var(--gold); }
.t3-map-body { display: flex; flex-direction: column; align-items: center; gap: 1rem; }
.t3-map-pin-icon { width: 2.5rem; height: 2.5rem; color: var(--gold); }
.t3-map-body span {
  font-size: 0.65rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--gold-dim);
}
.t3-dir-row { margin-top: 2rem; }
.t3-dir-btn {
  font-size: 0.72rem; letter-spacing: 0.15em;
  color: var(--gold); text-decoration: none;
  border-bottom: 1px solid rgba(201,168,76,0.4);
  padding-bottom: 0.1em;
  transition: border-color 0.2s;
}
.t3-dir-btn:hover { border-color: var(--gold); }
</style>