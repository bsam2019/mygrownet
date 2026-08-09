<template>
  <AdminLayout title="GrowStream Platform & Hub Admin Dashboard">
    <div class="py-6 space-y-8">
      <!-- Dashboard Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[var(--gs-border)] pb-6">
        <div>
          <h1 class="text-3xl font-bold text-[var(--gs-text)]">Platform &amp; Creator Hub Dashboard</h1>
          <p class="mt-1 text-sm text-[var(--gs-muted)]">Unified control center for general streaming, creator uploads, subscribers, and B2B Creator Hubs.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <Link
            :href="route('growstream.admin.hubs')"
            class="px-4 py-2 rounded-xl bg-amber-500 text-black font-bold text-xs hover:bg-amber-400 transition-colors flex items-center gap-1.5 shadow-lg shadow-amber-500/20"
          >
            <span class="material-symbols-outlined text-sm">domain</span> Manage Creator Hubs
          </Link>
          <Link
            :href="route('growstream.admin.videos')"
            class="px-4 py-2 rounded-xl bg-[#e2571f] text-white text-xs font-bold hover:bg-[#c94918] transition-colors flex items-center gap-1.5 shadow-lg shadow-[#e2571f]/20"
          >
            <span class="material-symbols-outlined text-sm">movie</span> Creator Uploads
          </Link>
        </div>
      </div>

      <!-- Unified Telemetry Metrics Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Metric 1: Creator Uploads & Catalog -->
        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Streaming Video Catalog</p>
              <p class="text-3xl font-black text-[var(--gs-text)] mt-2">
                {{ stats.published_videos.toLocaleString() }}
                <span class="text-xs font-normal text-[var(--gs-muted)]">/ {{ stats.total_videos.toLocaleString() }}</span>
              </p>
              <p v-if="stats.pending_moderation_count > 0" class="text-xs font-bold text-amber-400 mt-1">
                ⏳ {{ stats.pending_moderation_count }} pending moderation
              </p>
            </div>
            <div class="p-3 bg-[#e2571f]/10 rounded-2xl text-[#e2571f]">
              <span class="material-symbols-outlined text-2xl">movie</span>
            </div>
          </div>
        </div>

        <!-- Metric 2: B2B Creator Hubs -->
        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Creator Hub Platforms</p>
              <p class="text-3xl font-black text-amber-400 mt-2">
                {{ stats.active_hubs_count.toLocaleString() }}
                <span class="text-xs font-normal text-[var(--gs-muted)]">/ {{ stats.total_hubs_count.toLocaleString() }} Active</span>
              </p>
              <p class="text-xs text-neutral-400 mt-1">Standalone B2B Academies</p>
            </div>
            <div class="p-3 bg-amber-500/10 rounded-2xl text-amber-400">
              <span class="material-symbols-outlined text-2xl">domain</span>
            </div>
          </div>
        </div>

        <!-- Metric 3: Total Viewers & Subscribers -->
        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Platform Viewers &amp; Users</p>
              <p class="text-3xl font-black text-indigo-400 mt-2">{{ stats.total_subscribers.toLocaleString() }}</p>
              <p class="text-xs text-indigo-300 mt-1">{{ stats.unique_viewers.toLocaleString() }} unique stream watchers</p>
            </div>
            <div class="p-3 bg-indigo-500/10 rounded-2xl text-indigo-400">
              <span class="material-symbols-outlined text-2xl">group</span>
            </div>
          </div>
        </div>

        <!-- Metric 4: Streaming Views Telemetry -->
        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Total Streaming Views</p>
              <p class="text-3xl font-black text-emerald-400 mt-2">{{ stats.total_views.toLocaleString() }}</p>
              <p class="text-xs text-emerald-400 mt-1">{{ stats.completion_rate }}% completion rate</p>
            </div>
            <div class="p-3 bg-emerald-500/10 rounded-2xl text-emerald-400">
              <span class="material-symbols-outlined text-2xl">visibility</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Operations Center -->
      <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
        <h2 class="text-base font-bold text-[var(--gs-text)] mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">bolt</span> Unified Operations Toolbar
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
          <Link
            :href="route('growstream.admin.videos')"
            class="p-3.5 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-xl text-[#e2571f] mb-1 group-hover:scale-110 transition-transform">movie</span>
            <span class="font-bold text-xs text-neutral-200">Video Catalog</span>
          </Link>

          <Link
            :href="route('growstream.admin.moderation')"
            class="p-3.5 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-xl text-amber-400 mb-1 group-hover:scale-110 transition-transform">gavel</span>
            <span class="font-bold text-xs text-neutral-200">Moderation Queue</span>
          </Link>

          <Link
            :href="route('growstream.admin.creators')"
            class="p-3.5 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-xl text-indigo-400 mb-1 group-hover:scale-110 transition-transform">badge</span>
            <span class="font-bold text-xs text-neutral-200">Creators</span>
          </Link>

          <Link
            :href="route('growstream.admin.categories')"
            class="p-3.5 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-xl text-sky-400 mb-1 group-hover:scale-110 transition-transform">category</span>
            <span class="font-bold text-xs text-neutral-200">Categories</span>
          </Link>

          <Link
            :href="route('growstream.admin.hubs')"
            class="p-3.5 rounded-xl border border-neutral-800 bg-amber-500/10 border-amber-500/30 hover:bg-amber-500/20 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-xl text-amber-400 mb-1 group-hover:scale-110 transition-transform">domain</span>
            <span class="font-bold text-xs text-amber-200">Creator Hubs</span>
          </Link>

          <Link
            :href="route('growstream.admin.hub_pricing.show')"
            class="p-3.5 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-xl text-purple-400 mb-1 group-hover:scale-110 transition-transform">payments</span>
            <span class="font-bold text-xs text-neutral-200">Hub Pricing</span>
          </Link>
        </div>
      </div>

      <!-- Creator Hub Platforms & Video Uploads Data Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- B2B Creator Hub Platforms Table -->
        <div class="gs-card border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-[var(--gs-border)] flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-amber-400 text-lg">domain</span>
              <h3 class="text-sm font-bold text-[var(--gs-text)]">B2B Creator Hub Platforms</h3>
            </div>
            <Link :href="route('growstream.admin.hubs')" class="text-xs text-amber-400 hover:underline">Manage All Hubs</Link>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-neutral-900/80 text-neutral-400 uppercase font-semibold">
                <tr>
                  <th class="px-6 py-3">Hub Platform</th>
                  <th class="px-6 py-3">Subdomain</th>
                  <th class="px-6 py-3">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-800 text-neutral-200">
                <tr v-for="hub in hubs" :key="hub.id" class="hover:bg-neutral-900/40">
                  <td class="px-6 py-3.5">
                    <p class="font-bold text-neutral-100 truncate max-w-xs">{{ hub.brand_name }}</p>
                    <span class="text-[10px] text-amber-400 uppercase font-bold">{{ hub.subscription_plan }} Plan</span>
                  </td>
                  <td class="px-6 py-3.5 font-mono text-neutral-300">{{ hub.subdomain }}.mygrownet.com</td>
                  <td class="px-6 py-3.5">
                    <span :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                      hub.is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'
                    ]">
                      {{ hub.is_active ? 'Active' : 'Suspended' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="hubs.length === 0">
                  <td colspan="3" class="px-6 py-6 text-center text-neutral-500">No Creator Hub platforms created yet.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Creator Video Uploads Table -->
        <div class="gs-card border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-[var(--gs-border)] flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-[#e2571f] text-lg">movie</span>
              <h3 class="text-sm font-bold text-[var(--gs-text)]">Recent Creator Uploads</h3>
            </div>
            <Link :href="route('growstream.admin.videos')" class="text-xs text-primary hover:underline">View All Videos</Link>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-neutral-900/80 text-neutral-400 uppercase font-semibold">
                <tr>
                  <th class="px-6 py-3">Video Title</th>
                  <th class="px-6 py-3">Views</th>
                  <th class="px-6 py-3">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-800 text-neutral-200">
                <tr v-for="video in recentVideos" :key="video.id" class="hover:bg-neutral-900/40">
                  <td class="px-6 py-3.5">
                    <p class="font-bold text-neutral-100 truncate max-w-xs">{{ video.title }}</p>
                    <p class="text-[11px] text-neutral-400">{{ video.creator }}</p>
                  </td>
                  <td class="px-6 py-3.5 font-mono">{{ video.view_count.toLocaleString() }}</td>
                  <td class="px-6 py-3.5">
                    <span :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                      video.is_published ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'
                    ]">
                      {{ video.is_published ? 'Published' : 'Draft' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="recentVideos.length === 0">
                  <td colspan="3" class="px-6 py-6 text-center text-neutral-500">No recent videos found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface DashboardStats {
  total_videos: number;
  published_videos: number;
  pending_moderation_count: number;
  total_series: number;
  total_views: number;
  unique_viewers: number;
  total_subscribers: number;
  completion_rate: number;
  avg_watch_time: number;
  points_awarded: number;
  active_hubs_count: number;
  total_hubs_count: number;
}

interface RecentVideo {
  id: number;
  title: string;
  creator: string;
  status: string;
  is_published: boolean;
  view_count: number;
  created_at: string;
}

interface HubPlatform {
  id: number;
  brand_name: string;
  subdomain: string;
  subscription_plan: string;
  subscription_status: string;
  is_active: boolean;
  created_at: string;
}

defineProps<{
  stats: DashboardStats;
  recentVideos: RecentVideo[];
  topVideos: any[];
  hubs: HubPlatform[];
  viewTrends: any[];
  pointsDistribution: any[];
}>();
</script>
