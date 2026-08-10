<template>
  <div class="min-h-screen bg-[#0d0a08] text-neutral-100 font-sans selection:bg-[#e2571f] selection:text-white">
    <Head title="GrowMusic — Zambian Music & Audio Streaming Portal" />

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 bg-[#16120f]/90 backdrop-blur-xl border-b border-neutral-800">
      <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-[#e2571f] text-3xl">graphic_eq</span>
          <span class="text-2xl font-black tracking-tight text-white">GrowMusic <span class="text-[#e2571f] font-light">Zambia</span></span>
        </div>

        <div class="flex items-center gap-4 text-xs font-semibold">
          <Link :href="route('growmusic.home')" class="text-[#e2571f] font-bold">Music Portal</Link>
          <a href="https://growstream.mygrownet.com" class="text-neutral-400 hover:text-white transition-colors">GrowStream Video</a>
          <a href="https://mygrownet.com/workspace" class="text-neutral-400 hover:text-white transition-colors">MyGrowNet Workspace</a>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-10 space-y-10">
      <!-- Hero Banner -->
      <div class="relative rounded-3xl p-8 md:p-12 overflow-hidden bg-gradient-to-r from-[#e2571f]/20 via-amber-500/10 to-transparent border border-[#e2571f]/30">
        <div class="max-w-2xl space-y-4">
          <span class="px-3.5 py-1 rounded-full bg-[#e2571f]/20 border border-[#e2571f]/40 text-[#e2571f] text-xs font-bold uppercase tracking-widest inline-flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">music_note</span> Zambian Music &amp; Audio Streaming
          </span>
          <h1 class="text-3xl md:text-5xl font-black text-white leading-tight">
            Stream Top Zambian Sounds &amp; Support Artists
          </h1>
          <p class="text-sm text-neutral-300">
            Listen to official singles, audiobooks, podcasts, and acoustic sessions while supporting local creators directly via Mobile Money.
          </p>
        </div>
      </div>

      <!-- Music Catalog Grid -->
      <div class="space-y-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
          <span class="material-symbols-outlined text-[#e2571f]">local_fire_department</span> Trending Zambian Tracks
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="track in tracks"
            :key="track.id"
            class="group p-5 rounded-2xl bg-[#16120f] border border-neutral-800 hover:border-[#e2571f]/60 transition-all shadow-xl flex flex-col justify-between"
          >
            <div>
              <div class="aspect-square rounded-xl bg-neutral-900 overflow-hidden mb-4 relative">
                <img
                  :src="track.cover_art_url || '/images/default-music.jpg'"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  alt="Track Cover"
                />
                <button
                  @click="playTrack(track)"
                  class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white"
                >
                  <span class="material-symbols-outlined text-5xl text-[#e2571f]">play_circle</span>
                </button>
              </div>
              <h3 class="font-bold text-sm text-white truncate">{{ track.title }}</h3>
              <p class="text-xs text-neutral-400 mt-0.5">{{ track.genre }} • {{ track.album_name || 'Single' }}</p>
            </div>

            <div class="mt-4 pt-3 border-t border-neutral-800/60 flex items-center justify-between text-xs text-neutral-400">
              <span class="font-mono">{{ track.stream_count.toLocaleString() }} streams</span>
              <button @click="playTrack(track)" class="text-[#e2571f] font-bold hover:underline flex items-center gap-1">
                <span>Play Now</span>
                <span class="material-symbols-outlined text-sm">play_arrow</span>
              </button>
            </div>
          </div>

          <div v-if="tracks.length === 0" class="col-span-full py-12 text-center text-neutral-500">
            No music tracks uploaded yet. Artists can upload tracks via Creator Studio.
          </div>
        </div>
      </div>
    </main>

    <!-- Persistent Audio Player Bar -->
    <div v-if="activeTrack" class="fixed bottom-0 left-0 right-0 z-50 bg-[#16120f] border-t border-neutral-800 p-4 shadow-2xl">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-lg bg-neutral-800 overflow-hidden">
            <img :src="activeTrack.cover_art_url || '/images/default-music.jpg'" class="w-full h-full object-cover" />
          </div>
          <div>
            <p class="font-bold text-xs text-white">{{ activeTrack.title }}</p>
            <p class="text-[11px] text-neutral-400">{{ activeTrack.genre }}</p>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button @click="isPlaying = !isPlaying" class="w-10 h-10 rounded-full bg-[#e2571f] text-white flex items-center justify-center">
            <span class="material-symbols-outlined">{{ isPlaying ? 'pause' : 'play_arrow' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

interface Track {
  id: number;
  title: string;
  genre: string;
  album_name: string;
  cover_art_url: string;
  audio_url: string;
  stream_count: number;
}

defineProps<{
  tracks: Track[];
}>();

const activeTrack = ref<Track | null>(null);
const isPlaying = ref(false);

const playTrack = (track: Track) => {
  activeTrack.value = track;
  isPlaying.value = true;
};
</script>
