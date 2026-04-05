<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
  </Head>

  <div class="t2-root">

    <!-- Floating pill nav -->
    <nav class="t2-floatnav" :class="{ 't2-floatnav--scrolled': scrolled }">
      <div class="t2-floatnav-inner">
        <div class="t2-floatnav-brand">
          <span>{{ initials }}</span>
        </div>
        <div class="t2-floatnav-links">
          <button v-for="tab in navTabs" :key="tab.id"
            @click="activeTab = tab.id"
            :class="['t2-navlink', activeTab === tab.id ? 't2-navlink--active' : '']">
            {{ tab.label }}
          </button>
        </div>
        <button @click="showRSVPModal = true" class="t2-navlink-rsvp">RSVP</button>
      </div>
    </nav>

    <!-- HOME -->
    <section v-show="activeTab === 'home'">
      <!-- Full bleed hero with overlapping nameplate -->
      <div class="t2-hero">
        <div class="t2-hero-bg">
          <img :src="weddingEvent.hero_image"
               :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
               class="t2-hero-img" />
          <div class="t2-hero-vignette"></div>
        </div>

        <!-- Nameplate overlapping bottom -->
        <div class="t2-nameplate">
          <div class="t2-nameplate-inner">
            <div class="t2-nameplate-left">
              <p class="t2-tag">Marriage Invitation</p>
              <h1 class="t2-headline">
                {{ weddingEvent.groom_name }}<br/>
                <span class="t2-headline-amp">&amp;</span><br/>
                {{ weddingEvent.bride_name }}
              </h1>
            </div>
            <div class="t2-nameplate-right">
              <div class="t2-meta-block">
                <p class="t2-meta-label">Date</p>
                <p class="t2-meta-value">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
              </div>
              <div class="t2-meta-block">
                <p class="t2-meta-label">Venue</p>
                <p class="t2-meta-value">{{ weddingEvent.venue_name }}</p>
                <p class="t2-meta-sub">{{ weddingEvent.venue_address }}</p>
              </div>
              <div class="t2-meta-block">
                <p class="t2-meta-label">Ceremony</p>
                <p class="t2-meta-value">{{ weddingEvent.ceremony_time }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Countdown row -->
      <div v-if="countdown" class="t2-countdown-bar">
        <p class="t2-countdown-intro">Counting down to forever</p>
        <div class="t2-countdown-units">
          <div v-for="unit in countdownUnits" :key="unit.key" class="t2-cu">
            <span class="t2-cu-num">{{ countdown[unit.key] }}</span>
            <span class="t2-cu-label">{{ unit.label }}</span>
          </div>
        </div>
        <button @click="showRSVPModal = true" class="t2-cta-bar">Confirm attendance</button>
      </div>

      <!-- Story teaser -->
      <div class="t2-teaser">
        <div class="t2-teaser-left">
          <span class="t2-section-num">01</span>
          <h2 class="t2-teaser-heading">The Story<br/><em>So Far</em></h2>
          <p class="t2-teaser-body">{{ truncate(weddingEvent.how_we_met, 220) }}</p>
          <button @click="activeTab = 'story'" class="t2-text-link">Read more →</button>
        </div>
        <div class="t2-teaser-right">
          <div class="t2-mosaic">
            <div class="t2-mosaic-large">
              <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Story" />
            </div>
            <div class="t2-mosaic-small-col">
              <div class="t2-mosaic-small" v-for="(img, i) in galleryImages.slice(0,2)" :key="i">
                <img :src="img.url" :alt="`Gallery ${i}`" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- STORY -->
    <section v-show="activeTab === 'story'" class="t2-page">
      <div class="t2-page-header">
        <span class="t2-section-num">02</span>
        <h2 class="t2-page-title">Our Story</h2>
        <p class="t2-page-sub">The chapters that brought us here</p>
      </div>

      <div class="t2-story-body">
        <div class="t2-pull-quote">&ldquo;{{ pullQuote }}&rdquo;</div>

        <div class="t2-two-col">
          <div>
            <h3 class="t2-chapter-label">Chapter I — How We Met</h3>
            <p class="t2-body-text">{{ weddingEvent.how_we_met }}</p>
          </div>
          <div class="t2-chapter-image">
            <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="How we met" />
          </div>
        </div>

        <div class="t2-chapter-divider">
          <span>✦</span>
        </div>

        <div class="t2-two-col t2-two-col--reversed">
          <div class="t2-chapter-image">
            <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" />
          </div>
          <div>
            <h3 class="t2-chapter-label">Chapter II — The Proposal</h3>
            <p class="t2-body-text">{{ weddingEvent.proposal_story }}</p>
          </div>
        </div>

        <div v-if="galleryImages.length > 1" class="t2-gallery-strip">
          <p class="t2-gallery-heading">A Few of Our Favourite Moments</p>
          <div class="t2-strip-grid">
            <div v-for="(img, i) in galleryImages.slice(1)" :key="i" class="t2-strip-cell">
              <img :src="img.url" :alt="`Memory ${i+2}`" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- PROGRAM -->
    <section v-show="activeTab === 'program'" class="t2-page">
      <div class="t2-page-header">
        <span class="t2-section-num">03</span>
        <h2 class="t2-page-title">The Programme</h2>
        <p class="t2-page-sub">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
      </div>

      <div class="t2-programme">
        <div class="t2-prog-row">
          <div class="t2-prog-time-col">
            <span class="t2-prog-time">{{ weddingEvent.ceremony_time }}</span>
          </div>
          <div class="t2-prog-content">
            <h3>Wedding Ceremony</h3>
            <p>{{ weddingEvent.venue_name }}</p>
            <p class="t2-prog-detail">{{ weddingEvent.venue_address }}</p>
          </div>
          <div class="t2-prog-num">I</div>
        </div>
        <div class="t2-prog-row">
          <div class="t2-prog-time-col">
            <span class="t2-prog-time">{{ weddingEvent.reception_time }}</span>
          </div>
          <div class="t2-prog-content">
            <h3>Reception &amp; Celebration</h3>
            <p>{{ weddingEvent.reception_venue }}</p>
          </div>
          <div class="t2-prog-num">II</div>
        </div>
      </div>
    </section>

    <!-- LOCATION -->
    <section v-show="activeTab === 'location'" class="t2-page">
      <div class="t2-page-header">
        <span class="t2-section-num">04</span>
        <h2 class="t2-page-title">The Venue</h2>
        <p class="t2-page-sub">{{ weddingEvent.venue_name }}</p>
      </div>
      <p class="t2-location-address">{{ weddingEvent.venue_address }}</p>
      <div class="t2-map-block" @click="openGoogleMaps">
        <div class="t2-map-content">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="t2-map-pin">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
          </svg>
          <p>View on Google Maps</p>
        </div>
      </div>
      <div class="t2-dir-link-row">
        <a :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
           target="_blank" class="t2-dir-link">Get directions →</a>
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
const scrolled = ref(false)
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
  { key: 'minutes', label: 'Minutes' },
  { key: 'seconds', label: 'Seconds' },
]

const initials = computed(() => {
  const g = props.weddingEvent.groom_name?.[0] || ''
  const b = props.weddingEvent.bride_name?.[0] || ''
  return `${g}&${b}`
})

const pullQuote = computed(() => {
  const text = props.weddingEvent.how_we_met || ''
  return text.length > 120 ? text.slice(0, 120).trim() + '…' : text
})

const truncate = (text, len) => {
  if (!text) return ''
  return text.length > len ? text.slice(0, len).trim() + '…' : text
}

const formatDateFull = d => new Date(d).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })

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

const onScroll = () => { scrolled.value = window.scrollY > 80 }

let timer
onMounted(() => {
  updateCountdown()
  timer = setInterval(updateCountdown, 1000)
  window.addEventListener('scroll', onScroll)
})
onUnmounted(() => { clearInterval(timer); window.removeEventListener('scroll', onScroll) })
</script>

<style scoped>
.t2-root {
  --ink: #111;
  --mid: #555;
  --soft: #999;
  --paper: #faf9f7;
  --warm: #f3ede4;
  --rule: #e5dfd7;
  --accent: #c4783a;

  font-family: 'DM Sans', sans-serif;
  font-weight: 300;
  color: var(--ink);
  background: var(--paper);
}

/* ── Floating nav ─────────────────────────────── */
.t2-floatnav {
  position: fixed; top: 1.5rem; left: 50%; transform: translateX(-50%);
  z-index: 200;
  transition: all 0.3s;
  width: min(900px, 92vw);
}
.t2-floatnav-inner {
  display: flex; align-items: center; gap: 1rem;
  background: rgba(255,255,255,0.92);
  backdrop-filter: blur(12px);
  border: 1px solid var(--rule);
  border-radius: 100px;
  padding: 0.6rem 0.6rem 0.6rem 1.5rem;
  box-shadow: 0 4px 30px rgba(0,0,0,0.08);
}
.t2-floatnav--scrolled .t2-floatnav-inner {
  box-shadow: 0 4px 40px rgba(0,0,0,0.14);
}

.t2-floatnav-brand {
  font-family: 'Libre Baskerville', serif;
  font-style: italic;
  font-size: 0.9rem;
  color: var(--ink);
  margin-right: auto;
}
.t2-floatnav-links { display: flex; gap: 0.25rem; }
.t2-navlink {
  background: none; border: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.72rem;
  letter-spacing: 0.05em;
  color: var(--mid);
  padding: 0.4rem 0.9rem;
  border-radius: 100px;
  transition: all 0.2s;
}
.t2-navlink:hover { color: var(--ink); background: var(--warm); }
.t2-navlink--active { color: var(--ink); background: var(--warm); font-weight: 500; }
.t2-navlink-rsvp {
  background: var(--ink); color: var(--paper); border: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.72rem; letter-spacing: 0.1em;
  text-transform: uppercase;
  padding: 0.55rem 1.5rem; border-radius: 100px;
  transition: background 0.2s;
}
.t2-navlink-rsvp:hover { background: var(--accent); }

/* ── Hero ─────────────────────────────────────── */
.t2-hero { position: relative; height: 90vh; min-height: 600px; }
.t2-hero-bg { position: absolute; inset: 0; }
.t2-hero-img { width: 100%; height: 100%; object-fit: cover; object-position: center 25%; }
.t2-hero-vignette {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.0) 40%, rgba(0,0,0,0.55) 100%);
}

.t2-nameplate {
  position: absolute; bottom: -3rem; left: 0; right: 0;
  padding: 0 2rem;
  z-index: 10;
}
@media (min-width: 768px) { .t2-nameplate { padding: 0 4rem; } }

.t2-nameplate-inner {
  background: var(--paper);
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  padding: 2.5rem 2.5rem;
  border-top: 3px solid var(--accent);
  box-shadow: 0 20px 60px rgba(0,0,0,0.12);
}
@media (min-width: 768px) {
  .t2-nameplate-inner { grid-template-columns: 1fr 1fr; padding: 3rem 4rem; }
}

.t2-tag {
  font-size: 0.62rem;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: var(--accent);
  margin: 0 0 1rem;
}
.t2-headline {
  font-family: 'Libre Baskerville', serif;
  font-weight: 400;
  font-size: clamp(2rem, 4vw, 3.5rem);
  line-height: 1.15;
  margin: 0;
  color: var(--ink);
}
.t2-headline-amp {
  font-style: italic;
  color: var(--accent);
  font-size: 0.7em;
}

.t2-nameplate-right {
  display: flex; flex-direction: column; gap: 1.5rem;
  justify-content: center;
  border-left: 1px solid var(--rule);
  padding-left: 2.5rem;
}
.t2-meta-label {
  font-size: 0.58rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--soft); margin: 0 0 0.3rem;
}
.t2-meta-value { font-size: 0.9rem; color: var(--ink); margin: 0; font-weight: 400; }
.t2-meta-sub   { font-size: 0.78rem; color: var(--mid); margin: 0.2rem 0 0; }

/* ── Countdown bar ────────────────────────────── */
.t2-countdown-bar {
  margin-top: 6rem;
  padding: 4rem 3rem;
  background: var(--ink);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}
@media (min-width: 768px) {
  .t2-countdown-bar {
    flex-direction: row;
    justify-content: space-between;
    padding: 3rem 6rem;
    margin-top: 5rem;
  }
}

.t2-countdown-intro {
  font-family: 'Libre Baskerville', serif;
  font-style: italic;
  color: rgba(255,255,255,0.5);
  font-size: 0.9rem;
  margin: 0;
}
.t2-countdown-units { display: flex; gap: 2.5rem; }
.t2-cu { display: flex; flex-direction: column; align-items: center; }
.t2-cu-num {
  font-family: 'Libre Baskerville', serif;
  font-size: 2.5rem;
  color: white; line-height: 1;
}
.t2-cu-label {
  font-size: 0.58rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: rgba(255,255,255,0.4); margin-top: 0.3rem;
}

.t2-cta-bar {
  background: none;
  border: 1px solid rgba(255,255,255,0.3);
  color: white;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.68rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  padding: 0.8rem 2rem;
  cursor: pointer;
  transition: all 0.2s;
  border-radius: 2px;
}
.t2-cta-bar:hover { background: var(--accent); border-color: var(--accent); }

/* ── Story teaser ─────────────────────────────── */
.t2-teaser {
  display: grid;
  grid-template-columns: 1fr;
  gap: 3rem;
  padding: 5rem 2rem;
}
@media (min-width: 900px) {
  .t2-teaser { grid-template-columns: 1fr 1fr; padding: 7rem 5rem; gap: 6rem; align-items: center; }
}

.t2-section-num {
  font-family: 'Libre Baskerville', serif;
  font-size: 4rem;
  font-weight: 700;
  color: var(--rule);
  line-height: 1;
  display: block;
  margin-bottom: 1rem;
}
.t2-teaser-heading {
  font-family: 'Libre Baskerville', serif;
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 400;
  line-height: 1.2;
  margin: 0 0 1.5rem;
}
.t2-teaser-heading em { color: var(--accent); }
.t2-teaser-body { color: var(--mid); line-height: 1.8; font-size: 0.95rem; margin-bottom: 1.5rem; }
.t2-text-link {
  background: none; border: none; cursor: pointer;
  font-size: 0.78rem; letter-spacing: 0.1em;
  color: var(--accent); text-decoration: none;
  border-bottom: 1px solid var(--accent);
  padding-bottom: 0.1em;
}

.t2-mosaic { display: grid; grid-template-columns: 2fr 1fr; gap: 0.75rem; height: 420px; }
.t2-mosaic-large img, .t2-mosaic-small img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.t2-mosaic-small-col { display: flex; flex-direction: column; gap: 0.75rem; }
.t2-mosaic-small { flex: 1; overflow: hidden; }

/* ── Pages ────────────────────────────────────── */
.t2-page { padding: 6rem 2rem 5rem; }
@media (min-width: 768px) { .t2-page { padding: 8rem 5rem 6rem; } }

.t2-page-header { margin-bottom: 4rem; }
.t2-page-title {
  font-family: 'Libre Baskerville', serif;
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 400;
  margin: 0.5rem 0;
}
.t2-page-sub { color: var(--mid); font-size: 0.85rem; letter-spacing: 0.05em; }

/* Story body */
.t2-story-body { display: flex; flex-direction: column; gap: 4rem; }
.t2-pull-quote {
  font-family: 'Libre Baskerville', serif;
  font-style: italic;
  font-size: clamp(1.3rem, 3vw, 2rem);
  color: var(--ink);
  line-height: 1.5;
  border-left: 4px solid var(--accent);
  padding-left: 2rem;
  max-width: 700px;
}

.t2-two-col {
  display: grid; grid-template-columns: 1fr;
  gap: 3rem; align-items: center;
}
@media (min-width: 768px) {
  .t2-two-col { grid-template-columns: 1fr 1fr; }
  .t2-two-col--reversed .t2-chapter-image { order: -1; }
}

.t2-chapter-label {
  font-size: 0.65rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--accent); margin: 0 0 1rem;
}
.t2-body-text { color: var(--mid); line-height: 1.85; font-size: 0.95rem; }
.t2-chapter-image img { width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block; }

.t2-chapter-divider {
  text-align: center; font-size: 1.2rem; color: var(--rule); letter-spacing: 1em;
}

.t2-gallery-strip { }
.t2-gallery-heading {
  font-family: 'Libre Baskerville', serif;
  font-size: 1.4rem; font-weight: 400;
  margin-bottom: 1.5rem;
}
.t2-strip-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}
.t2-strip-cell img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; }

/* Programme */
.t2-programme { border-top: 1px solid var(--rule); }
.t2-prog-row {
  display: grid;
  grid-template-columns: 80px 1fr 40px;
  gap: 2rem;
  padding: 2.5rem 0;
  border-bottom: 1px solid var(--rule);
  align-items: start;
}
.t2-prog-time {
  font-family: 'Libre Baskerville', serif;
  font-style: italic;
  font-size: 0.85rem;
  color: var(--accent);
  padding-top: 0.2rem;
}
.t2-prog-content h3 {
  font-family: 'Libre Baskerville', serif;
  font-size: 1.6rem; font-weight: 400;
  margin: 0 0 0.4rem; color: var(--ink);
}
.t2-prog-content p { margin: 0; color: var(--mid); font-size: 0.9rem; }
.t2-prog-detail { font-size: 0.78rem; color: var(--soft); }
.t2-prog-num {
  font-family: 'Libre Baskerville', serif;
  font-style: italic; font-size: 2rem;
  color: var(--rule); text-align: right; line-height: 1;
}

/* Location */
.t2-location-address { color: var(--mid); font-size: 0.95rem; margin-bottom: 3rem; }
.t2-map-block {
  background: var(--warm); height: 350px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background 0.2s;
}
.t2-map-block:hover { background: #ece4d8; }
.t2-map-content { display: flex; flex-direction: column; align-items: center; gap: 1rem; color: var(--mid); }
.t2-map-pin { width: 3rem; height: 3rem; }
.t2-map-content p { font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; }
.t2-dir-link-row { margin-top: 2rem; }
.t2-dir-link {
  font-size: 0.78rem; letter-spacing: 0.1em; color: var(--accent);
  text-decoration: none; border-bottom: 1px solid var(--accent);
}
</style>