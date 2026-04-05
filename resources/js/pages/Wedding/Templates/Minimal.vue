<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=Karla:wght@300;400&display=swap" rel="stylesheet" />
  </Head>

  <div class="t1-root">
    <!-- Vertical sidebar nav (desktop) -->
    <aside class="t1-sidebar">
      <div class="t1-sidebar-inner">
        <div class="t1-date-vertical">
          <span>{{ formatShortDate(weddingEvent.wedding_date) }}</span>
        </div>
        <nav class="t1-nav">
          <button
            v-for="tab in navTabs" :key="tab.id"
            @click="activeTab = tab.id"
            :class="['t1-nav-item', activeTab === tab.id ? 't1-nav-active' : '']"
          >{{ tab.label }}</button>
        </nav>
        <div class="t1-sidebar-bottom">
          <button @click="showRSVPModal = true" class="t1-rsvp-side">RSVP</button>
        </div>
      </div>
    </aside>

    <!-- Mobile top bar -->
    <header class="t1-mobile-header">
      <span class="t1-mobile-names">{{ weddingEvent.groom_name }} & {{ weddingEvent.bride_name }}</span>
      <button @click="mobileNavOpen = !mobileNavOpen" class="t1-mobile-menu-btn">
        <span v-if="!mobileNavOpen">☰</span>
        <span v-else>✕</span>
      </button>
    </header>
    <div v-if="mobileNavOpen" class="t1-mobile-nav">
      <button v-for="tab in navTabs" :key="tab.id"
        @click="activeTab = tab.id; mobileNavOpen = false"
        :class="['t1-mobile-nav-item', activeTab === tab.id ? 'active' : '']">
        {{ tab.label }}
      </button>
      <button @click="showRSVPModal = true; mobileNavOpen = false" class="t1-mobile-rsvp">RSVP</button>
    </div>

    <!-- Main canvas -->
    <main class="t1-main">

      <!-- HOME -->
      <section v-show="activeTab === 'home'" class="t1-section">
        <!-- Split hero -->
        <div class="t1-hero-split">
          <div class="t1-hero-image-pane">
            <img
              :src="weddingEvent.hero_image"
              :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
              class="t1-hero-img"
            />
            <div class="t1-hero-overlay"></div>
          </div>
          <div class="t1-hero-text-pane">
            <p class="t1-eyebrow">We are getting married</p>
            <h1 class="t1-names">
              <em>{{ weddingEvent.groom_name }}</em>
              <span class="t1-ampersand">&</span>
              <em>{{ weddingEvent.bride_name }}</em>
            </h1>
            <div class="t1-ruled-line"></div>
            <p class="t1-full-date">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
            <p class="t1-venue">{{ weddingEvent.venue_name }}</p>
            <p class="t1-venue-address">{{ weddingEvent.venue_address }}</p>

            <!-- Countdown strip -->
            <div v-if="countdown" class="t1-countdown">
              <div v-for="unit in countdownUnits" :key="unit.key" class="t1-countdown-cell">
                <span class="t1-countdown-num">{{ String(countdown[unit.key]).padStart(2,'0') }}</span>
                <span class="t1-countdown-label">{{ unit.label }}</span>
              </div>
            </div>

            <button @click="showRSVPModal = true" class="t1-cta">Kindly Reply</button>
          </div>
        </div>
      </section>

      <!-- STORY -->
      <section v-show="activeTab === 'story'" class="t1-section t1-story-section">
        <div class="t1-story-header">
          <span class="t1-story-label">Our Story</span>
          <h2 class="t1-story-title">How it began</h2>
        </div>

        <!-- Alternating editorial blocks -->
        <div class="t1-editorial-block">
          <div class="t1-editorial-text">
            <h3 class="t1-block-heading">First Encounter</h3>
            <p class="t1-block-body">{{ weddingEvent.how_we_met }}</p>
          </div>
          <div class="t1-editorial-image">
            <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" />
            <div class="t1-image-caption">The beginning</div>
          </div>
        </div>

        <div class="t1-ruled-full"></div>

        <div class="t1-editorial-block t1-editorial-block--reversed">
          <div class="t1-editorial-image">
            <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" />
            <div class="t1-image-caption">The proposal</div>
          </div>
          <div class="t1-editorial-text">
            <h3 class="t1-block-heading">The Proposal</h3>
            <p class="t1-block-body">{{ weddingEvent.proposal_story }}</p>
          </div>
        </div>

        <!-- Gallery grid -->
        <div v-if="galleryImages.length > 1" class="t1-gallery">
          <p class="t1-gallery-label">Memories</p>
          <div class="t1-gallery-grid">
            <div v-for="(img, i) in galleryImages.slice(1)" :key="i" class="t1-gallery-cell">
              <img :src="img.url" :alt="`Memory ${i+1}`" />
            </div>
          </div>
        </div>
      </section>

      <!-- PROGRAM -->
      <section v-show="activeTab === 'program'" class="t1-section t1-program-section">
        <div class="t1-story-header">
          <span class="t1-story-label">The Day</span>
          <h2 class="t1-story-title">Schedule of events</h2>
        </div>
        <div class="t1-program-list">
          <div class="t1-program-item">
            <div class="t1-program-time">{{ weddingEvent.ceremony_time }}</div>
            <div class="t1-program-divider"></div>
            <div class="t1-program-detail">
              <h3>Ceremony</h3>
              <p>{{ weddingEvent.venue_name }}</p>
              <p class="t1-program-sub">{{ weddingEvent.venue_address }}</p>
            </div>
          </div>
          <div class="t1-program-item">
            <div class="t1-program-time">{{ weddingEvent.reception_time }}</div>
            <div class="t1-program-divider"></div>
            <div class="t1-program-detail">
              <h3>Reception</h3>
              <p>{{ weddingEvent.reception_venue }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- LOCATION -->
      <section v-show="activeTab === 'location'" class="t1-section t1-location-section">
        <div class="t1-story-header">
          <span class="t1-story-label">Location</span>
          <h2 class="t1-story-title">{{ weddingEvent.venue_name }}</h2>
        </div>
        <p class="t1-location-address">{{ weddingEvent.venue_address }}</p>
        <div class="t1-map-placeholder" @click="openGoogleMaps">
          <div class="t1-map-inner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="t1-map-icon">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            <span>Open in Maps</span>
          </div>
        </div>
        <div class="t1-directions-row">
          <a :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
             target="_blank" class="t1-directions-btn">Get directions →</a>
        </div>
      </section>

      <!-- CONTACT -->
      <section v-show="activeTab === 'contact'" class="t1-section t1-contact-section">
        <div class="t1-story-header">
          <span class="t1-story-label">Contact</span>
          <h2 class="t1-story-title">Get in touch</h2>
        </div>
        <div class="t1-contact-grid">
          <div class="t1-contact-people">
            <div class="t1-contact-person">
              <h4>{{ weddingEvent.bride_name }}</h4>
              <p v-if="weddingEvent.bride_email">{{ weddingEvent.bride_email }}</p>
              <p v-if="weddingEvent.bride_phone">{{ weddingEvent.bride_phone }}</p>
            </div>
            <div class="t1-contact-person">
              <h4>{{ weddingEvent.groom_name }}</h4>
              <p v-if="weddingEvent.groom_email">{{ weddingEvent.groom_email }}</p>
              <p v-if="weddingEvent.groom_phone">{{ weddingEvent.groom_phone }}</p>
            </div>
            <div v-if="weddingEvent.contact_person" class="t1-contact-person">
              <h4>{{ weddingEvent.contact_person }}<span class="t1-coordinator-tag">Coordinator</span></h4>
              <p v-if="weddingEvent.contact_email">{{ weddingEvent.contact_email }}</p>
              <p v-if="weddingEvent.contact_phone">{{ weddingEvent.contact_phone }}</p>
            </div>
          </div>
          <form @submit.prevent="sendMessage" class="t1-message-form">
            <h3>Send a message</h3>
            <input v-model="contactForm.name" type="text" placeholder="Your name" required />
            <input v-model="contactForm.email" type="email" placeholder="Email address" required />
            <textarea v-model="contactForm.message" rows="4" placeholder="Your message" required></textarea>
            <button type="submit" :disabled="contactForm.sending">
              {{ contactForm.sending ? 'Sending…' : 'Send →' }}
            </button>
            <p v-if="contactForm.success" class="t1-form-success">Message sent!</p>
            <p v-if="contactForm.error" class="t1-form-error">{{ contactForm.error }}</p>
          </form>
        </div>
      </section>

    </main>

    <RSVPModal
      :isOpen="showRSVPModal"
      :weddingEventId="weddingEvent.id"
      @close="showRSVPModal = false"
      @submitted="showRSVPModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
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
const mobileNavOpen = ref(false)
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
const contactForm = ref({ name: '', email: '', message: '', sending: false, success: false, error: null })

const navTabs = [
  { id: 'home',     label: 'Home' },
  { id: 'story',    label: 'Story' },
  { id: 'program',  label: 'Program' },
  { id: 'location', label: 'Location' },
  { id: 'contact',  label: 'Contact' },
]
const countdownUnits = [
  { key: 'days', label: 'days' },
  { key: 'hours', label: 'hrs' },
  { key: 'minutes', label: 'min' },
  { key: 'seconds', label: 'sec' },
]

const formatShortDate = d => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
const formatDateFull  = d => new Date(d).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })

const updateCountdown = () => {
  const diff = new Date(props.weddingEvent.wedding_date).getTime() - Date.now()
  if (diff > 0) {
    countdown.value = {
      days:    Math.floor(diff / 86400000),
      hours:   Math.floor((diff % 86400000) / 3600000),
      minutes: Math.floor((diff % 3600000)  / 60000),
      seconds: Math.floor((diff % 60000)    / 1000),
    }
  }
}

const openGoogleMaps = () => {
  window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.weddingEvent.venue_address)}`, '_blank')
}

const sendMessage = async () => {
  contactForm.value.sending = true
  await new Promise(r => setTimeout(r, 800))
  contactForm.value.success = true
  contactForm.value.sending = false
  contactForm.value.name = contactForm.value.email = contactForm.value.message = ''
}

let timer
onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000) })
onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
/* ── Tokens ───────────────────────────────────── */
.t1-root {
  --ink:      #1a1714;
  --ink-mid:  #6b6560;
  --ink-soft: #b0aaa4;
  --paper:    #f9f7f4;
  --cream:    #ede9e3;
  --accent:   #8b7355;
  --sidebar-w: 200px;

  font-family: 'Karla', sans-serif;
  font-weight: 300;
  color: var(--ink);
  background: var(--paper);
  min-height: 100vh;
  display: flex;
}

/* ── Sidebar ──────────────────────────────────── */
.t1-sidebar {
  width: var(--sidebar-w);
  min-height: 100vh;
  background: var(--ink);
  display: none;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 100;
}
@media (min-width: 768px) { .t1-sidebar { display: flex; } }

.t1-sidebar-inner {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 3rem 2rem;
  gap: 3rem;
}

.t1-date-vertical {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  transform: rotate(180deg);
  font-size: 0.65rem;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  color: var(--ink-soft);
  align-self: center;
}

.t1-nav { display: flex; flex-direction: column; gap: 1.25rem; }
.t1-nav-item {
  background: none; border: none; cursor: pointer;
  text-align: left;
  font-family: 'Karla', sans-serif;
  font-size: 0.7rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ink-soft);
  transition: color 0.2s;
  padding: 0;
}
.t1-nav-item:hover, .t1-nav-active { color: var(--paper); }
.t1-nav-active { position: relative; }
.t1-nav-active::before {
  content: '';
  position: absolute;
  left: -1rem;
  top: 50%; transform: translateY(-50%);
  width: 4px; height: 4px;
  background: var(--accent);
  border-radius: 50%;
}

.t1-sidebar-bottom { margin-top: auto; }
.t1-rsvp-side {
  width: 100%;
  background: var(--accent);
  color: white;
  border: none; cursor: pointer;
  font-family: 'Karla', sans-serif;
  font-size: 0.65rem;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  padding: 0.75rem 0;
}

/* ── Mobile header ────────────────────────────── */
.t1-mobile-header {
  display: flex; align-items: center; justify-content: space-between;
  position: fixed; top: 0; left: 0; right: 0; z-index: 200;
  background: var(--ink);
  padding: 1rem 1.5rem;
}
@media (min-width: 768px) { .t1-mobile-header { display: none; } }

.t1-mobile-names {
  font-family: 'Cormorant Garamond', serif;
  font-style: italic;
  color: var(--paper);
  font-size: 1rem;
}
.t1-mobile-menu-btn {
  background: none; border: none; color: var(--paper);
  font-size: 1.25rem; cursor: pointer;
}

.t1-mobile-nav {
  position: fixed; top: 56px; left: 0; right: 0; z-index: 199;
  background: var(--ink);
  display: flex; flex-direction: column;
  padding: 1.5rem;
  gap: 1rem;
}
@media (min-width: 768px) { .t1-mobile-nav { display: none; } }
.t1-mobile-nav-item {
  background: none; border: none; cursor: pointer;
  font-family: 'Karla', sans-serif;
  font-size: 0.75rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--ink-soft); text-align: left; padding: 0.25rem 0;
}
.t1-mobile-nav-item.active { color: var(--paper); }
.t1-mobile-rsvp {
  margin-top: 0.5rem;
  background: var(--accent); color: white; border: none; cursor: pointer;
  font-family: 'Karla', sans-serif;
  font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase;
  padding: 0.75rem; text-align: center;
}

/* ── Main ─────────────────────────────────────── */
.t1-main {
  flex: 1;
  margin-left: 0;
  padding-top: 56px;
}
@media (min-width: 768px) {
  .t1-main { margin-left: var(--sidebar-w); padding-top: 0; }
}

.t1-section { min-height: 100vh; }

/* ── Home / Hero ──────────────────────────────── */
.t1-hero-split {
  display: grid;
  grid-template-columns: 1fr;
  min-height: 100vh;
}
@media (min-width: 900px) {
  .t1-hero-split { grid-template-columns: 1fr 1fr; }
}

.t1-hero-image-pane {
  position: relative;
  min-height: 50vw;
  max-height: 100vh;
}
@media (min-width: 900px) { .t1-hero-image-pane { min-height: 100vh; } }

.t1-hero-img {
  width: 100%; height: 100%;
  object-fit: cover; object-position: center 30%;
  display: block;
}
.t1-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to right, transparent 70%, var(--paper) 100%);
  display: none;
}
@media (min-width: 900px) { .t1-hero-overlay { display: block; } }

.t1-hero-text-pane {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 4rem 3rem;
  gap: 1.5rem;
}
@media (min-width: 900px) { .t1-hero-text-pane { padding: 6rem 4rem; } }

.t1-eyebrow {
  font-size: 0.65rem;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: var(--accent);
}

.t1-names {
  font-family: 'Cormorant Garamond', serif;
  font-style: italic;
  font-weight: 300;
  font-size: clamp(2.5rem, 5vw, 4.5rem);
  line-height: 1.1;
  color: var(--ink);
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.t1-ampersand {
  font-size: 0.45em;
  color: var(--ink-soft);
  font-style: normal;
  letter-spacing: 0.1em;
  align-self: flex-start;
  margin-left: 0.5em;
}

.t1-ruled-line {
  width: 3rem; height: 1px;
  background: var(--accent);
}

.t1-full-date {
  font-size: 0.8rem;
  letter-spacing: 0.1em;
  color: var(--ink-mid);
}
.t1-venue {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.1rem;
  color: var(--ink);
}
.t1-venue-address {
  font-size: 0.78rem;
  color: var(--ink-soft);
}

.t1-countdown {
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem 0;
  border-top: 1px solid var(--cream);
  border-bottom: 1px solid var(--cream);
}
.t1-countdown-cell {
  display: flex; flex-direction: column; gap: 0.25rem;
}
.t1-countdown-num {
  font-family: 'Cormorant Garamond', serif;
  font-size: 2.25rem;
  font-weight: 300;
  color: var(--ink);
  line-height: 1;
}
.t1-countdown-label {
  font-size: 0.6rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ink-soft);
}

.t1-cta {
  align-self: flex-start;
  background: none;
  border: 1px solid var(--ink);
  color: var(--ink);
  font-family: 'Karla', sans-serif;
  font-size: 0.7rem;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  padding: 0.85rem 2rem;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.t1-cta:hover { background: var(--ink); color: var(--paper); }

/* ── Story ────────────────────────────────────── */
.t1-story-section, .t1-program-section, .t1-location-section, .t1-contact-section {
  padding: 5rem 3rem;
  max-width: 1100px;
}
@media (min-width: 768px) {
  .t1-story-section, .t1-program-section, .t1-location-section, .t1-contact-section {
    padding: 6rem 5rem;
  }
}

.t1-story-header { margin-bottom: 4rem; }
.t1-story-label {
  font-size: 0.62rem;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: var(--accent);
  display: block;
  margin-bottom: 0.75rem;
}
.t1-story-title {
  font-family: 'Cormorant Garamond', serif;
  font-weight: 300;
  font-style: italic;
  font-size: clamp(2rem, 4vw, 3.5rem);
  color: var(--ink);
  margin: 0;
}

.t1-editorial-block {
  display: grid;
  grid-template-columns: 1fr;
  gap: 3rem;
  margin-bottom: 4rem;
}
@media (min-width: 768px) {
  .t1-editorial-block { grid-template-columns: 1fr 1fr; align-items: center; }
  .t1-editorial-block--reversed .t1-editorial-image { order: -1; }
}

.t1-editorial-text { display: flex; flex-direction: column; gap: 1rem; }
.t1-block-heading {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.6rem;
  font-weight: 300;
  color: var(--ink);
  margin: 0;
}
.t1-block-body {
  color: var(--ink-mid);
  line-height: 1.8;
  font-size: 0.95rem;
}

.t1-editorial-image { position: relative; }
.t1-editorial-image img {
  width: 100%; aspect-ratio: 4/3;
  object-fit: cover; display: block;
}
.t1-image-caption {
  font-size: 0.62rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--ink-soft);
  margin-top: 0.5rem;
  text-align: right;
}

.t1-ruled-full {
  width: 100%; height: 1px;
  background: var(--cream);
  margin: 3rem 0;
}

.t1-gallery { margin-top: 4rem; }
.t1-gallery-label {
  font-size: 0.62rem;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 1.5rem;
}
.t1-gallery-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}
@media (min-width: 600px) { .t1-gallery-grid { grid-template-columns: repeat(3, 1fr); } }
.t1-gallery-cell img {
  width: 100%; aspect-ratio: 1;
  object-fit: cover; display: block;
}

/* ── Program ──────────────────────────────────── */
.t1-program-list { display: flex; flex-direction: column; gap: 0; }
.t1-program-item {
  display: grid;
  grid-template-columns: 80px 1px 1fr;
  gap: 0 2rem;
  padding: 2.5rem 0;
  border-bottom: 1px solid var(--cream);
}
.t1-program-time {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1rem;
  color: var(--ink-soft);
  padding-top: 0.15rem;
}
.t1-program-divider {
  background: var(--cream);
  width: 1px;
}
.t1-program-detail h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.5rem;
  font-weight: 400;
  margin: 0 0 0.25rem;
  color: var(--ink);
}
.t1-program-detail p { margin: 0; color: var(--ink-mid); font-size: 0.9rem; }
.t1-program-sub { color: var(--ink-soft); font-size: 0.78rem; }

/* ── Location ─────────────────────────────────── */
.t1-location-address {
  color: var(--ink-mid);
  font-size: 0.95rem;
  margin-bottom: 3rem;
}
.t1-map-placeholder {
  width: 100%;
  aspect-ratio: 16/7;
  background: var(--cream);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
}
.t1-map-placeholder:hover { background: #e0dbd5; }
.t1-map-inner {
  display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
  color: var(--ink-mid);
}
.t1-map-icon { width: 2rem; height: 2rem; }
.t1-map-inner span { font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; }
.t1-directions-row { margin-top: 2rem; }
.t1-directions-btn {
  font-size: 0.7rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--ink);
  text-decoration: none;
  border-bottom: 1px solid var(--accent);
  padding-bottom: 0.1em;
}

/* ── Contact ──────────────────────────────────── */
.t1-contact-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 4rem;
}
@media (min-width: 768px) { .t1-contact-grid { grid-template-columns: 1fr 1fr; } }

.t1-contact-people { display: flex; flex-direction: column; gap: 2.5rem; }
.t1-contact-person h4 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.2rem;
  font-weight: 400;
  margin: 0 0 0.5rem;
  display: flex; align-items: center; gap: 0.75rem;
}
.t1-coordinator-tag {
  font-family: 'Karla', sans-serif;
  font-size: 0.6rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--accent);
  background: #f0ebe4;
  padding: 0.2em 0.6em;
  border-radius: 2px;
}
.t1-contact-person p { margin: 0.2rem 0; font-size: 0.85rem; color: var(--ink-mid); }

.t1-message-form { display: flex; flex-direction: column; gap: 1rem; }
.t1-message-form h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.4rem;
  font-weight: 300;
  margin: 0 0 0.5rem;
}
.t1-message-form input, .t1-message-form textarea {
  background: none;
  border: none;
  border-bottom: 1px solid var(--cream);
  padding: 0.6rem 0;
  font-family: 'Karla', sans-serif;
  font-size: 0.9rem;
  color: var(--ink);
  outline: none;
  transition: border-color 0.2s;
  width: 100%;
}
.t1-message-form input:focus, .t1-message-form textarea:focus {
  border-color: var(--accent);
}
.t1-message-form textarea { resize: vertical; }
.t1-message-form button {
  align-self: flex-start;
  background: var(--ink);
  color: var(--paper);
  border: none; cursor: pointer;
  font-family: 'Karla', sans-serif;
  font-size: 0.7rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  padding: 0.85rem 2rem;
  transition: background 0.2s;
}
.t1-message-form button:hover { background: var(--accent); }
.t1-form-success { font-size: 0.8rem; color: #5a8a5a; }
.t1-form-error   { font-size: 0.8rem; color: #a05a5a; }
</style>