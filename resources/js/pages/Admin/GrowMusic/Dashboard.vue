<template>
  <Head title="GrowMusic Platform Administration" />
  <AdminLayout>
    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">GrowMusic Control Center</h1>
          <p class="text-sm text-gray-600 mt-1">Manage Zambian music catalog, ZAMCO stream log royalties, and artist fan clubs.</p>
        </div>
      </div>

      <!-- KPI Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="p-5 bg-white rounded-xl shadow border-l-4 border-amber-500">
          <p class="text-xs text-gray-500 font-semibold">Total Music Tracks</p>
          <p class="text-2xl font-black text-gray-900 mt-1">{{ totalTracks.toLocaleString() }}</p>
          <p class="text-xs text-amber-600 mt-1">Published Zambian Singles</p>
        </div>

        <div class="p-5 bg-white rounded-xl shadow border-l-4 border-indigo-500">
          <p class="text-xs text-gray-500 font-semibold">Verified Stream Count</p>
          <p class="text-2xl font-black text-indigo-600 mt-1">{{ totalStreams.toLocaleString() }}</p>
          <p class="text-xs text-indigo-500 mt-1">&gt;30s Play Log Telemetry</p>
        </div>

        <div class="p-5 bg-white rounded-xl shadow border-l-4 border-emerald-500">
          <p class="text-xs text-gray-500 font-semibold">ZAMCO Royalties Earned</p>
          <p class="text-2xl font-black text-emerald-600 mt-1">K{{ totalRoyalties.toFixed(2) }}</p>
          <p class="text-xs text-emerald-500 mt-1">K0.10 Per Stream Pool</p>
        </div>

        <div class="p-5 bg-white rounded-xl shadow border-l-4 border-purple-500">
          <p class="text-xs text-gray-500 font-semibold">VIP Fan Subscriptions</p>
          <p class="text-2xl font-black text-purple-600 mt-1">{{ activeFanSubscriptions }}</p>
          <p class="text-xs text-purple-500 mt-1">K50/K150/K500 Fan Clubs</p>
        </div>
      </div>

      <!-- Catalog Table -->
      <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="font-bold text-base text-gray-900">Music Catalog Management</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 uppercase text-gray-500 font-bold border-b border-gray-200">
              <tr>
                <th class="py-3 px-4">Track</th>
                <th class="py-3 px-4">Artist</th>
                <th class="py-3 px-4">Genre</th>
                <th class="py-3 px-4">Streams</th>
                <th class="py-3 px-4">Access Tier</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="track in recentTracks" :key="track.id" class="hover:bg-gray-50">
                <td class="py-3 px-4 font-bold text-gray-900">{{ track.title }}</td>
                <td class="py-3 px-4 text-gray-700">{{ track.artist_name }} <span class="text-gray-400">({{ track.artist_email }})</span></td>
                <td class="py-3 px-4 text-gray-600">{{ track.genre }}</td>
                <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ track.stream_count.toLocaleString() }}</td>
                <td class="py-3 px-4">
                  <span :class="[
                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase',
                    track.is_vip_only ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'
                  ]">
                    {{ track.is_vip_only ? 'VIP Fan Pass' : 'Free Stream' }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <button @click="toggleVip(track.id)" class="text-amber-600 font-bold hover:underline">
                    Toggle VIP
                  </button>
                </td>
              </tr>
              <tr v-if="recentTracks.length === 0">
                <td colspan="6" class="py-8 text-center text-gray-400">No music tracks in catalog yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
  totalTracks: number;
  totalStreams: number;
  totalRoyalties: number;
  activeFanSubscriptions: number;
  recentTracks: any[];
  topArtists: any[];
}>();

const toggleVip = (id: number) => {
  router.post(route('admin.growmusic.toggle-vip', id));
};
</script>
