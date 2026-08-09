<template>
  <AdminLayout title="GrowStream Admin Overview">
    <div class="py-6 space-y-8">
      <!-- Header with Exit & Action Links -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[var(--gs-border)] pb-6">
        <div>
          <h1 class="text-3xl font-bold text-[var(--gs-text)]">GrowStream Administration</h1>
          <p class="mt-1 text-sm text-[var(--gs-muted)]">Real-time telemetry, content moderation, tenant hubs, and platform performance.</p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            :href="route('growstream.admin.hubs')"
            class="px-4 py-2 rounded-xl bg-[#e2571f] text-white text-xs font-bold hover:bg-[#c94918] transition-colors flex items-center gap-1.5 shadow-lg shadow-[#e2571f]/20"
          >
            <span class="material-symbols-outlined text-sm">domain</span> Manage Creator Hubs
          </Link>
          <Link
            :href="route('growstream.admin.hub_pricing.show')"
            class="px-4 py-2 rounded-xl border border-neutral-700 bg-neutral-800 text-neutral-200 text-xs font-semibold hover:bg-neutral-700 transition-colors flex items-center gap-1.5"
          >
            <span class="material-symbols-outlined text-sm">payments</span> Pricing Tiers
          </Link>
        </div>
      </div>

      <!-- Key Performance Metrics Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Total Published Videos</p>
              <p class="text-3xl font-black text-[var(--gs-text)] mt-2">{{ stats.published_videos.toLocaleString() }} <span class="text-xs font-normal text-[var(--gs-muted)]">/ {{ stats.total_videos.toLocaleString() }}</span></p>
            </div>
            <div class="p-3 bg-[#e2571f]/10 rounded-2xl text-[#e2571f]">
              <span class="material-symbols-outlined text-2xl">movie</span>
            </div>
          </div>
        </div>

        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Total Views</p>
              <p class="text-3xl font-black text-emerald-400 mt-2">{{ stats.total_views.toLocaleString() }}</p>
            </div>
            <div class="p-3 bg-emerald-500/10 rounded-2xl text-emerald-400">
              <span class="material-symbols-outlined text-2xl">visibility</span>
            </div>
          </div>
        </div>

        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Unique Viewers</p>
              <p class="text-3xl font-black text-indigo-400 mt-2">{{ stats.unique_viewers.toLocaleString() }}</p>
            </div>
            <div class="p-3 bg-indigo-500/10 rounded-2xl text-indigo-400">
              <span class="material-symbols-outlined text-2xl">group</span>
            </div>
          </div>
        </div>

        <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-[var(--gs-muted)]">Completion Rate</p>
              <p class="text-3xl font-black text-amber-400 mt-2">{{ stats.completion_rate }}%</p>
            </div>
            <div class="p-3 bg-amber-500/10 rounded-2xl text-amber-400">
              <span class="material-symbols-outlined text-2xl">task_alt</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="gs-card p-6 border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg">
        <h2 class="text-base font-bold text-[var(--gs-text)] mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">bolt</span> Quick Operations Hub
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <Link
            :href="route('growstream.admin.videos')"
            class="p-4 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-2xl text-[#e2571f] mb-1 group-hover:scale-110 transition-transform">movie</span>
            <span class="font-bold text-xs text-neutral-200">Manage Videos</span>
          </Link>

          <Link
            :href="route('growstream.admin.moderation')"
            class="p-4 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-2xl text-amber-400 mb-1 group-hover:scale-110 transition-transform">gavel</span>
            <span class="font-bold text-xs text-neutral-200">Moderation Queue</span>
          </Link>

          <Link
            :href="route('growstream.admin.hubs')"
            class="p-4 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-2xl text-emerald-400 mb-1 group-hover:scale-110 transition-transform">domain</span>
            <span class="font-bold text-xs text-neutral-200">Tenant Hubs</span>
          </Link>

          <Link
            :href="route('growstream.admin.hub_pricing.show')"
            class="p-4 rounded-xl border border-neutral-800 bg-neutral-900/60 hover:bg-neutral-800 transition-all flex flex-col items-center justify-center text-center group"
          >
            <span class="material-symbols-outlined text-2xl text-purple-400 mb-1 group-hover:scale-110 transition-transform">payments</span>
            <span class="font-bold text-xs text-neutral-200">Hub Pricing</span>
          </Link>
        </div>
      </div>

      <!-- Recent Videos & Top Performing Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Videos -->
        <div class="gs-card border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-[var(--gs-border)] flex items-center justify-between">
            <h3 class="text-sm font-bold text-[var(--gs-text)]">Recent Content Uploads</h3>
            <Link :href="route('growstream.admin.videos')" class="text-xs text-primary hover:underline">View All</Link>
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

        <!-- Top Performing Videos -->
        <div class="gs-card border border-[var(--gs-border)] rounded-2xl bg-[var(--gs-surface-low,#16120f)] shadow-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-[var(--gs-border)] flex items-center justify-between">
            <h3 class="text-sm font-bold text-[var(--gs-text)]">Top Performing Videos</h3>
            <Link :href="route('growstream.admin.analytics')" class="text-xs text-primary hover:underline">Analytics</Link>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-neutral-900/80 text-neutral-400 uppercase font-semibold">
                <tr>
                  <th class="px-6 py-3">Video Title</th>
                  <th class="px-6 py-3">Views</th>
                  <th class="px-6 py-3">Completion</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-800 text-neutral-200">
                <tr v-for="video in topVideos" :key="video.id" class="hover:bg-neutral-900/40">
                  <td class="px-6 py-3.5">
                    <p class="font-bold text-neutral-100 truncate max-w-xs">{{ video.title }}</p>
                  </td>
                  <td class="px-6 py-3.5 font-mono text-emerald-400">{{ video.view_count.toLocaleString() }}</td>
                  <td class="px-6 py-3.5 font-mono text-amber-400">{{ video.completion_rate }}%</td>
                </tr>
                <tr v-if="topVideos.length === 0">
                  <td colspan="3" class="px-6 py-6 text-center text-neutral-500">No top videos recorded.</td>
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
  total_series: number;
  total_views: number;
  unique_viewers: number;
  completion_rate: number;
  avg_watch_time: number;
  points_awarded: number;
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

interface TopVideo {
  id: number;
  title: string;
  view_count: number;
  completion_rate: number;
  points_awarded: number;
}

defineProps<{
  stats: DashboardStats;
  recentVideos: RecentVideo[];
  topVideos: TopVideo[];
  viewTrends: any[];
  pointsDistribution: any[];
}>();
</script>
