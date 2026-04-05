<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
    <link href="https://fonts.googleapis.com/css2?family=Gilda+Display&family=Raleway:wght@300;400;500&family=Great+Vibes&display=swap" rel="stylesheet" />
  </Head>

  <div class="t4-root">
    <!-- Ambient background texture -->
    <div class="t4-bg-texture"></div>

    <!-- Page tabs as full-width sections -->
    <!-- Sticky minimal nav -->
    <nav class="t4-nav">
      <button v-for="tab in navTabs" :key="tab.id"
        @click="activeTab = tab.id"
        :class="['t4-navdot', activeTab === tab.id ? 't4-navdot--active' : '']"
        :title="tab.label">
        <span class="t4-navdot-label">{{ tab.label }}</span>
      </button>
    </nav>

    <!-- Mobile nav -->
    <nav class="t4-mobile-nav">
      <button v-for="tab in navTabs" :key="tab.id"
        @click="activeTab = tab.id"
        :class="['t4-mobile-navbtn', activeTab === tab.id ? 'active' : '']">
        {{ tab.label }}
      </button>
    </nav>

    <!-- ════ HOME ════ -->
    <div v-show="activeTab === 'home'" class="t4-page t4-home">
      <!-- Full viewport title section -->
      <div class="t4-opening">
        <p class="t4-invite-line">Together with their families</p>

        <div class="t4-names-block">
          <h1 class="t4-name-script">{{ weddingEvent.groom_name }}</h1>
          <div class="t4-floral-divider">
            <div class="t4-floral-line"></div>
            <span class="t4-floral-motif">✿</span>
            <div class="t4-floral-line"></div>
          </div>
          <h1 class="t4-name-script">{{ weddingEvent.bride_name }}</h1>
        </div>

        <p class="t4-invite-request">request the pleasure of your company<br/>at their wedding</p>

        <!-- Circular date badge -->
        <div class="t4-date-circle">
          <div class="t4-date-circle-inner">
            <span class="t4-circle-day">{{ dayOfWeek }}</span>
            <span class="t4-circle-date">{{ dayNum }}</span>
            <span class="t4-circle-month">{{ monthYear }}</span>
          </div>
        </div>

        <button @click="scrollToDetails" class="t4-scroll-hint">↓ Details below</button>
      </div>

      <!-- Hero image, full bleed -->
      <div class="t4-hero-full">
        <img
          :src="weddingEvent.hero_image"
          :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
          class="t4-hero-img"
        />
      </div>

      <!-- Details section with ornamental layout -->
      <div class="t4-details-section" ref="detailsRef">
        <div class="t4-ornament-top">❧</div>
        
        <div class="t4-details-grid">
          <div class="t4-detail-col">
            <p class="t4-detail-heading">Ceremony</p>
            <p class="t4-detail-time">{{ weddingEvent.ceremony_time }}</p>
            <div class="t4-detail-ornament">—</div>
            <p class="t4-detail-venue">{{ weddingEvent.venue_name }}</p>
            <p class="t4-detail-address">{{ weddingEvent.venue_address }}</p>
          </div>

          <div class="t4-details-vr"></div>

          <div class="t4-detail-col">
            <p class="t4-detail-heading">Reception</p>
            <p class="t4-detail-time">{{ weddingEvent.reception_time }}</p>
            <div class="t4-detail-ornament">—</div>
            <p class="t4-detail-venue">{{ weddingEvent.reception_venue }}</p>
          </div>
        </div>

        <!-- Countdown -->
        <div v-if="countdown" class="t4-countdown">
          <p class="t4-countdown-tag">Counting down</p>
          <div class="t4-countdown-row">
            <div v-for="unit in countdownUnits" :key="unit.key" class="t4-count">
              <span class="t4-count-n">{{ countdown[unit.key] }}</span>
              <span class="t4-count-u">{{ unit.label }}</span>
            </div>
          </div>
        </div>

        <div class="t4-rsvp-row">
          <div class="t4-rsvp-ornament">✦</div>
          <button @click="showRSVPModal = true" class="t4-rsvp-btn">
            Kindly Reply by {{ rsvpDeadline }}
          </button>
          <div class="t4-rsvp-ornament">✦</div>
        </div>

        <div class="t4-ornament-bottom">❧</div>
      </div>
    </div>

    <!-- ════ STORY ════ -->
    <div v-show="activeTab === 'story'" class="t4-page">
      <div class="t4-page-masthead">
        <p class="t4-page-tag">Chapter One</p>
        <h2 class="t4-page-title-serif">Our Love Story</h2>
        <div class="t4-page-rule"></div>
      </div>

      <div class="t4-story-body">
        <!-- First meeting — full width with image -->
        <div class="t4-story-lead">
          <div class="t4-story-lead-image">
            <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" />
          </div>
          <div class="t4-story-lead-text">
            <p class="t4-story-chapter">How We Met</p>
            <p class="t4-story-prose">{{ weddingEvent.how_we_met }}</p>
          </div>
        </div>

        <!-- Decorative break -->
        <div class="t4-story-break">
          <div class="t4-break-line"></div>
          <span class="t4-break-text">❧ ❧ ❧</span>
          <div class="t4-break-line"></div>
        </div>

        <!-- Proposal - text left, image right -->
        <div class="t4-story-proposal">
          <div class="t4-story-proposal-text">
            <p class="t4-story-chapter">The Proposal</p>
            <p class="t4-story-prose">{{ weddingEvent.proposal_story }}</p>
          </div>
          <div class="t4-story-proposal-image">
            <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" />
            <div class="t4-image-frame-corner t4-corner-tl"></div>
            <div class="t4-image-frame-corner t4-corner-tr"></div>
            <div class="t4-image-frame-corner t4-corner-bl"></div>
            <div class="t4-image-frame-corner t4-corner-br"></div>
          </div>
        </div>

        <!-- Gallery -->
        <div v-if="galleryImages.length > 1" class="t4-story-gallery">
          <p class="t4-gallery-heading">Moments Together</p>
          <div class="t4-gallery-rows">
            <div class="t4-gallery-row">
              <div class="t4-gallery-cell t4-gallery-cell--tall" v-if="galleryImages[1]">
                <img :src="galleryImages[1].url" alt="Gallery 1" />
              </div>
              <div class="t4-gallery-col">
                <div class="t4-gallery-cell" v-if="galleryImages[2]">
                  <img :src="galleryImages[2].url" alt="Gallery 2" />
                </div>
                <div class="t4-gallery-cell" v-if="galleryImages[3]">
                  <img :src="galleryImages[3].url" alt="Gallery 3" />
                </div>
              </div>
              <div class="t4-gallery-cell t4-gallery-cell--tall" v-if="galleryImages[4]">
                <img :src="galleryImages[4].url" alt="Gallery 4" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ════ PROGRAM ════ -->
    <div v-show="activeTab === 'program'" class="t4-page">
      <div class="t4-page-masthead">
        <p class="t4-page-tag">The Wedding Day</p>
        <h2 class="t4-page-title-serif">Order of Celebration</h2>
        <div class="t4-page-rule"></div>
        <p class="t4-page-date-full">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
      </div>

      <div class="t4-program-scroll">
        <div class="t4-prog-event">
          <div class="t4-prog-time-block">
            <span class="t4-prog-time">{{ weddingEvent.ceremony_time }}</span>
          </div>
          <div class="t4-prog-flower">✿</div>
          <div class="t4-prog-info">
            <h3 class="t4-prog-name">Wedding Ceremony</h3>
            <p class="t4-prog-venue">{{ weddingEvent.venue_name }}</p>
            <p class="t4-prog-addr">{{ weddingEvent.venue_address }}</p>
          </div>
        </div>

        <div class="t4-prog-connector">
          <div class="t4-prog-line"></div>
        </div>

        <div class="t4-prog-event">
          <div class="t4-prog-time-block">
            <span class="t4-prog-time">{{ weddingEvent.reception_time }}</span>
          </div>
          <div class="t4-prog-flower">✿</div>
          <div class="t4-prog-info">
            <h3 class="t4-prog-name">Reception & Celebration</h3>
            <p class="t4-prog-venue">{{ weddingEvent.reception_venue }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ════ LOCATION ════ -->
    <div v-show="activeTab === 'location'" class="t4-page">
      <div class="t4-page-masthead">
        <p class="t4-page-tag">You'll find us here</p>
        <h2 class="t4-page-title-serif">The Venue</h2>
        <div class="t4-page-rule"></div>
      </div>

      <div class="t4-location-body">
        <div class="t4-venue-card">
          <div class="t4-vc-top">
            <h3 class="t4-vc-name">{{ weddingEvent.venue_name }}</h3>
            <p class="t4-vc-address">{{ weddingEvent.venue_address }}</p>
          </div>
          <div class="t4-vc-map" @click="openGoogleMaps">
            <div class="t4-vc-map-inner">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="t4-map-pin">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
              </svg>
              <span>View on map</span>
            </div>
          </div>
          <a :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
             target="_blank" class="t4-vc-directions">Get directions →</a>
        </div>
      </div>
    </div>

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
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
const detailsRef = ref(null)

const navTabs = [
  { id: 'home', label: 'Home' },
  { id: 'story', label: 'Story' },
  { id: 'program', label: 'Program' },
  { id: 'location', label: 'Venue' },
]
const countdownUnits = [
  { key: 'days', label: 'Days' },
  { key: 'hours', label: 'Hours' },
  { key: 'minutes', label: 'Mins' },
  { key: 'seconds', label: 'Secs' },
]

const wDate = computed(() => props.weddingEvent?.wedding_date ? new Date(props.weddingEvent.wedding_date) : new Date())

const dayOfWeek = computed(() => wDate.value.toLocaleDateString('en-US', { weekday: 'long' }))
const dayNum    = computed(() => wDate.value.getDate())
const monthYear = computed(() => wDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }))

// RSVP deadline = 2 months before wedding
const rsvpDeadline = computed(() => {
  const d = new Date(wDate.value)
  d.setMonth(d.getMonth() - 2)
  return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })
})

const formatDateFull = d => new Date(d).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })

const updateCountdown = () => {
  const diff = wDate.value.getTime() - Date.now()
  if (diff > 0) countdown.value = {
    days:    Math.floor(diff / 86400000),
    hours:   Math.floor((diff % 86400000) / 3600000),
    minutes: Math.floor((diff % 3600000)  / 60000),
    seconds: Math.floor((diff % 60000)    / 1000),
  }
}

const scrollToDetails = () => {
  detailsRef.value?.scrollIntoView({ behavior: 'smooth' })
}

const openGoogleMaps = () => {
  window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.weddingEvent.venue_address)}`, '_blank')
}

let timer
onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000) })
onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
.t4-root {
  --blush:    #f7f0e8;
  --petal:    #f2e8de;
  --stem:     #7a6a5a;
  --ink:      #2c2520;
  --leaf:     #8a9e7a;
  --dusty:    #c4a898;
  --rose:     #b87060;
  --soft:     #a09085;

  font-family: 'Raleway', sans-serif;
  font-weight: 300;
  background: var(--blush);
  color: var(--ink);
  min-height: 100vh;
  position: relative;
}

.t4-bg-texture {
  position: fixed; inset: 0; z-index: 0;
  pointer-events: none;
  background-image: radial-gradient(ellipse at 20% 30%, rgba(180,150,130,0.08) 0%, transparent 60%),
                    radial-gradient(ellipse at 80% 70%, rgba(122,106,90,0.06) 0%, transparent 50%);
}

/* ── Sidebar dot nav ──────────────────────────── */
.t4-nav {
  position: fixed;
  right: 2rem; top: 50%; transform: translateY(-50%);
  z-index: 200;
  display: none;
  flex-direction: column; gap: 1rem;
}
@media (min-width: 900px) { .t4-nav { display: flex; } }

.t4-navdot {
  position: relative;
  background: none; border: none; cursor: pointer;
  display: flex; align-items: center; gap: 0.75rem;
  flex-direction: row-reverse;
}
.t4-navdot::after {
  content: '';
  width: 8px; height: 8px;
  border-radius: 50%;
  border: 1px solid var(--dusty);
  display: block;
  transition: all 0.2s;
}
.t4-navdot--active::after {
  background: var(--rose);
  border-color: var(--rose);
}
.t4-navdot-label {
  font-size: 0.6rem; letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--soft);
  opacity: 0; transform: translateX(5px);
  transition: all 0.2s;
  white-space: nowrap;
}
.t4-navdot:hover .t4-navdot-label { opacity: 1; transform: translateX(0); }

/* Mobile tab nav */
.t4-mobile-nav {
  position: sticky; top: 0; z-index: 100;
  background: var(--blush);
  border-bottom: 1px solid rgba(196,168,152,0.3);
  display: flex; justify-content: center;
  padding: 0.75rem 1rem;
  gap: 0.25rem;
}
@media (min-width: 900px) { .t4-mobile-nav { display: none; } }

.t4-mobile-navbtn {
  background: none; border: none; cursor: pointer;
  font-family: 'Raleway', sans-serif;
  font-size: 0.65rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--soft);
  padding: 0.4rem 0.8rem;
  border-radius: 100px;
  transition: all 0.2s;
}
.t4-mobile-navbtn.active { color: var(--rose); background: rgba(184,112,96,0.08); }

/* ── Pages ────────────────────────────────────── */
.t4-page { position: relative; z-index: 10; }

/* ── Opening / invitation ─────────────────────── */
.t4-opening {
  min-height: 100vh;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  gap: 2rem;
}

.t4-invite-line {
  font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--soft); margin: 0;
}

.t4-names-block {
  display: flex; flex-direction: column; align-items: center; gap: 1rem;
}

.t4-name-script {
  font-family: 'Great Vibes', cursive;
  font-size: clamp(3rem, 8vw, 6rem);
  font-weight: 400;
  color: var(--ink);
  margin: 0;
  line-height: 1.1;
}

.t4-floral-divider {
  display: flex; align-items: center; gap: 1rem; width: 100%; max-width: 300px;
}
.t4-floral-line { flex: 1; height: 1px; background: var(--dusty); opacity: 0.5; }
.t4-floral-motif { color: var(--rose); font-size: 1rem; }

.t4-invite-request {
  font-size: 0.8rem; letter-spacing: 0.1em;
  color: var(--soft); line-height: 1.8; margin: 0;
}

/* Circular date badge */
.t4-date-circle {
  width: 140px; height: 140px;
  border-radius: 50%;
  border: 1px solid var(--dusty);
  display: flex; align-items: center; justify-content: center;
  position: relative;
}
.t4-date-circle::before {
  content: '';
  position: absolute; inset: 5px;
  border-radius: 50%;
  border: 1px solid rgba(196,168,152,0.4);
}
.t4-date-circle-inner {
  display: flex; flex-direction: column; align-items: center; gap: 0.1rem;
}
.t4-circle-day {
  font-size: 0.55rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--soft);
}
.t4-circle-date {
  font-family: 'Gilda Display', serif;
  font-size: 2.8rem; line-height: 1;
  color: var(--ink);
}
.t4-circle-month {
  font-size: 0.6rem; letter-spacing: 0.15em;
  color: var(--stem);
}

.t4-scroll-hint {
  background: none; border: none; cursor: pointer;
  font-size: 0.65rem; letter-spacing: 0.2em;
  color: var(--soft); margin-top: 1rem;
  animation: float 2s ease-in-out infinite;
}
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(6px); }
}

/* Full bleed hero */
.t4-hero-full {
  width: 100%; height: 70vh; min-height: 400px;
  overflow: hidden;
}
.t4-hero-img { width: 100%; height: 100%; object-fit: cover; object-position: center 30%; }

/* Details section */
.t4-details-section {
  padding: 5rem 2rem;
  text-align: center;
  max-width: 800px; margin: 0 auto;
}

.t4-ornament-top, .t4-ornament-bottom {
  font-size: 2rem; color: var(--dusty);
  margin: 0 0 3rem;
  opacity: 0.6;
}
.t4-ornament-bottom { margin: 3rem 0 0; }

.t4-details-grid {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  align-items: center;
  margin-bottom: 3.5rem;
}
@media (min-width: 600px) {
  .t4-details-grid { flex-direction: row; align-items: stretch; justify-content: center; }
}

.t4-detail-col { flex: 1; max-width: 260px; }
.t4-detail-heading {
  font-size: 0.6rem; letter-spacing: 0.3em; text-transform: uppercase;
  color: var(--rose); margin: 0 0 0.75rem;
}
.t4-detail-time {
  font-family: 'Gilda Display', serif;
  font-size: 1.5rem; color: var(--ink); margin: 0;
}
.t4-detail-ornament { color: var(--dusty); margin: 0.5rem 0; font-size: 0.8rem; }
.t4-detail-venue {
  font-family: 'Gilda Display', serif;
  font-size: 1rem; color: var(--ink); margin: 0 0 0.25rem;
}
.t4-detail-address { font-size: 0.78rem; color: var(--soft); margin: 0; }

.t4-details-vr {
  width: 1px; background: rgba(196,168,152,0.4);
  align-self: stretch; display: none;
}
@media (min-width: 600px) { .t4-details-vr { display: block; } }

/* Countdown */
.t4-countdown { margin-bottom: 3rem; }
.t4-countdown-tag {
  font-size: 0.6rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--soft); margin-bottom: 1.5rem;
}
.t4-countdown-row {
  display: flex; justify-content: center; gap: 2rem;
}
.t4-count { display: flex; flex-direction: column; align-items: center; }
.t4-count-n {
  font-family: 'Gilda Display', serif;
  font-size: 2.5rem; color: var(--ink); line-height: 1;
}
.t4-count-u {
  font-size: 0.55rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--soft); margin-top: 0.3rem;
}

/* RSVP row */
.t4-rsvp-row {
  display: flex; align-items: center; justify-content: center;
  gap: 1.5rem;
  margin-top: 1rem;
}
.t4-rsvp-ornament { color: var(--dusty); font-size: 0.75rem; }
.t4-rsvp-btn {
  background: none;
  border: 1px solid var(--stem);
  color: var(--stem);
  font-family: 'Raleway', sans-serif;
  font-size: 0.65rem; letter-spacing: 0.2em; text-transform: uppercase;
  padding: 0.85rem 2.5rem; cursor: pointer;
  transition: all 0.25s;
}
.t4-rsvp-btn:hover {
  background: var(--stem); color: var(--blush);
}

/* ── Page masthead ────────────────────────────── */
.t4-page-masthead {
  padding: 5rem 2rem 3rem;
  text-align: center;
}
.t4-page-tag {
  font-size: 0.6rem; letter-spacing: 0.3em; text-transform: uppercase;
  color: var(--rose); margin: 0 0 1rem;
}
.t4-page-title-serif {
  font-family: 'Gilda Display', serif;
  font-size: clamp(2.5rem, 6vw, 4rem);
  font-weight: 400; color: var(--ink); margin: 0;
}
.t4-page-rule {
  width: 60px; height: 1px;
  background: var(--dusty); margin: 1.5rem auto 0;
}
.t4-page-date-full { margin-top: 1rem; font-size: 0.8rem; color: var(--soft); }

/* ── Story ────────────────────────────────────── */
.t4-story-body { padding: 0 2rem 5rem; max-width: 1100px; margin: 0 auto; }

.t4-story-lead {
  display: grid; grid-template-columns: 1fr;
  gap: 3rem; align-items: center; margin-bottom: 4rem;
}
@media (min-width: 768px) { .t4-story-lead { grid-template-columns: 1.2fr 1fr; } }

.t4-story-lead-image img {
  width: 100%; aspect-ratio: 3/4; object-fit: cover; display: block;
}

.t4-story-chapter {
  font-size: 0.62rem; letter-spacing: 0.3em; text-transform: uppercase;
  color: var(--rose); margin: 0 0 1rem;
}
.t4-story-prose { color: var(--stem); line-height: 1.9; font-size: 0.95rem; }

.t4-story-break {
  display: flex; align-items: center; gap: 1.5rem;
  margin: 3rem 0;
}
.t4-break-line { flex: 1; height: 1px; background: rgba(196,168,152,0.4); }
.t4-break-text { font-size: 1rem; color: var(--dusty); letter-spacing: 1em; }

.t4-story-proposal {
  display: grid; grid-template-columns: 1fr;
  gap: 3rem; align-items: center; margin-bottom: 4rem;
}
@media (min-width: 768px) {
  .t4-story-proposal { grid-template-columns: 1fr 1fr; }
}

.t4-story-proposal-image { position: relative; }
.t4-story-proposal-image img {
  width: 100%; aspect-ratio: 3/4; object-fit: cover; display: block;
}
/* Corner frame ornaments */
.t4-image-frame-corner {
  position: absolute; width: 20px; height: 20px;
  border-color: var(--dusty); border-style: solid;
}
.t4-corner-tl { top: -6px; left: -6px; border-width: 1px 0 0 1px; }
.t4-corner-tr { top: -6px; right: -6px; border-width: 1px 1px 0 0; }
.t4-corner-bl { bottom: -6px; left: -6px; border-width: 0 0 1px 1px; }
.t4-corner-br { bottom: -6px; right: -6px; border-width: 0 1px 1px 0; }

/* Gallery */
.t4-story-gallery { margin-top: 4rem; }
.t4-gallery-heading {
  font-family: 'Gilda Display', serif;
  font-size: 1.5rem; color: var(--ink);
  text-align: center; margin-bottom: 2rem;
}
.t4-gallery-rows { }
.t4-gallery-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 0.75rem; align-items: center;
}
.t4-gallery-cell img { width: 100%; height: 180px; object-fit: cover; display: block; }
.t4-gallery-cell--tall img { height: 375px; }
.t4-gallery-col { display: flex; flex-direction: column; gap: 0.75rem; }

/* ── Program ──────────────────────────────────── */
.t4-program-scroll {
  max-width: 600px; margin: 0 auto;
  padding: 0 2rem 5rem;
  display: flex; flex-direction: column;
}

.t4-prog-event {
  display: grid;
  grid-template-columns: 80px 2rem 1fr;
  gap: 0 1.5rem;
  align-items: center;
}

.t4-prog-time-block { text-align: right; }
.t4-prog-time {
  font-family: 'Gilda Display', serif;
  font-size: 1.1rem; color: var(--stem);
}

.t4-prog-flower { color: var(--rose); font-size: 1.2rem; text-align: center; }

.t4-prog-name {
  font-family: 'Gilda Display', serif;
  font-size: 1.4rem; color: var(--ink); margin: 0 0 0.3rem;
}
.t4-prog-venue { font-size: 0.85rem; color: var(--stem); margin: 0; }
.t4-prog-addr  { font-size: 0.75rem; color: var(--soft); margin: 0.2rem 0 0; }

.t4-prog-connector {
  display: flex; justify-content: center;
  padding: 0 0 0 calc(80px + 1.5rem + 1rem);
  height: 3rem;
}
.t4-prog-line {
  width: 1px; height: 100%;
  background: rgba(196,168,152,0.5);
}

/* ── Location ─────────────────────────────────── */
.t4-location-body {
  padding: 0 2rem 5rem;
  max-width: 600px; margin: 0 auto;
}
.t4-venue-card {
  border: 1px solid rgba(196,168,152,0.4);
  background: rgba(255,255,255,0.4);
  display: flex; flex-direction: column;
  overflow: hidden;
}
.t4-vc-top { padding: 2.5rem 2.5rem 2rem; }
.t4-vc-name {
  font-family: 'Gilda Display', serif;
  font-size: 1.5rem; color: var(--ink); margin: 0 0 0.5rem;
}
.t4-vc-address { font-size: 0.85rem; color: var(--soft); margin: 0; }

.t4-vc-map {
  background: var(--petal);
  height: 250px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background 0.2s;
}
.t4-vc-map:hover { background: #e8ddd0; }
.t4-vc-map-inner { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.t4-map-pin { width: 2.5rem; height: 2.5rem; color: var(--rose); }
.t4-vc-map-inner span {
  font-size: 0.62rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--soft);
}
.t4-vc-directions {
  display: block; text-align: center;
  padding: 1.25rem;
  font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--stem); text-decoration: none;
  border-top: 1px solid rgba(196,168,152,0.3);
  transition: color 0.2s;
}
.t4-vc-directions:hover { color: var(--rose); }
</style>