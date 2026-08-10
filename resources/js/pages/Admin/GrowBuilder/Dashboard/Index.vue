<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 text-white font-sans selection:bg-indigo-500/30 pb-12">
        <Head title="GrowBuilder Domain Admin" />

        <!-- Header -->
        <header class="sticky top-0 z-10 backdrop-blur-md bg-slate-950/50 border-b border-white/10 px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-cyan-400">
                        GrowBuilder Domain Admin
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Overview of sites, AI usage, and infrastructure.
                    </p>
                </div>
                <div>
                    <Link href="/workspace" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-colors text-sm font-medium">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Workspace
                    </Link>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-8 space-y-8">
            <!-- Stats Grid -->
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Sites -->
                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-5 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Sites (Total / Active)</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-white">{{ stats.total_sites }}</span>
                                <span class="text-sm text-emerald-400">/ {{ stats.active_sites }}</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 border border-indigo-500/20">
                            <GlobeAltIcon class="w-6 h-6" />
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-slate-400 flex items-center gap-1">
                        <ArrowTrendingUpIcon class="w-4 h-4 text-emerald-400" />
                        <span class="text-emerald-400 font-medium">+{{ stats.new_sites_this_month }}</span> new this month
                    </div>
                </div>

                <!-- Infrastructure -->
                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-5 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Infrastructure</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-white">{{ stats.custom_domains }}</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-cyan-500/20 flex items-center justify-center text-cyan-400 border border-cyan-500/20">
                            <ServerStackIcon class="w-6 h-6" />
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-slate-400 flex items-center gap-1">
                        <span>{{ stats.custom_domains }} custom domains &bull; {{ stats.ssg_enabled_sites }} SSG enabled</span>
                    </div>
                </div>

                <!-- Profiles -->
                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-5 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Business Profiles</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-white">{{ stats.business_profiles }}</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400 border border-amber-500/20">
                            <BriefcaseIcon class="w-6 h-6" />
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-slate-400 flex items-center gap-1">
                        <span>{{ stats.profiles_with_tpin }} profiles with TPIN</span>
                    </div>
                </div>

                <!-- Commerce -->
                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-5 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Commerce</p>
                            <div class="mt-2 flex flex-col gap-1">
                                <span class="text-2xl font-bold text-white">{{ formatCurrency(stats.total_revenue_zmw) }}</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                            <ShoppingCartIcon class="w-6 h-6" />
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-slate-400 flex items-center gap-1">
                        <span>{{ stats.total_orders.toLocaleString() }} total orders</span>
                    </div>
                </div>
            </section>

            <!-- AI & Infrastructure Row -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400 border border-purple-500/30">
                        <SparklesIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">AI Usage (This Month)</p>
                        <p class="text-xl font-semibold text-white">{{ stats.ai_usage_this_month.toLocaleString() }} <span class="text-xs font-normal text-slate-500">tokens</span></p>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400 border border-blue-500/30">
                        <RocketLaunchIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">SSG Deployments (This Month)</p>
                        <p class="text-xl font-semibold text-white">{{ stats.ssg_deployments_month.toLocaleString() }} <span class="text-xs font-normal text-slate-500">builds</span></p>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-pink-500/20 flex items-center justify-center text-pink-400 border border-pink-500/30">
                        <QrCodeIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">QR Code Bridge</p>
                        <p class="text-xl font-semibold text-white">{{ stats.qr_codes_total.toLocaleString() }} <span class="text-xs font-normal text-slate-500">codes</span> <span class="text-slate-600">/</span> {{ stats.qr_scans_total.toLocaleString() }} <span class="text-xs font-normal text-slate-500">scans</span></p>
                    </div>
                </div>
            </section>

            <!-- Two Column Layout -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Top Sites -->
                <div class="lg:col-span-2 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-white/10 flex items-center justify-between">
                        <h2 class="text-lg font-medium text-white flex items-center gap-2">
                            <StarIcon class="w-5 h-5 text-indigo-400" />
                            Top Sites
                        </h2>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-white/5 text-slate-400 text-xs uppercase tracking-wider">
                                    <th class="px-5 py-3 font-medium">Site & Business</th>
                                    <th class="px-5 py-3 font-medium">Location & Industry</th>
                                    <th class="px-5 py-3 font-medium">Status / Stack</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <tr v-for="site in topSites" :key="site.id" class="hover:bg-white/5 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-white">{{ site.name }}</div>
                                        <div class="text-slate-400 text-xs mt-0.5">{{ site.business_name }}</div>
                                        <div class="text-indigo-300 text-xs mt-0.5">
                                            {{ site.custom_domain || site.subdomain + '.mygrownet.com' }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-slate-300">{{ site.city || '—' }}</div>
                                        <div class="text-slate-400 text-xs mt-0.5">{{ site.industry || '—' }}</div>
                                    </td>
                                    <td class="px-5 py-4 flex flex-col items-start gap-2">
                                        <span :class="[
                                            'px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border',
                                            site.status === 'published' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-500/20 text-slate-400 border-slate-500/30'
                                        ]">
                                            {{ site.status }}
                                        </span>
                                        <span v-if="site.ssg_enabled" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 flex items-center gap-1">
                                            <BoltIcon class="w-3 h-3" /> SSG
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="topSites.length === 0">
                                    <td colspan="3" class="px-5 py-8 text-center text-slate-500">
                                        No sites found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Recent SSG Deployments -->
                <div class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-white/10">
                        <h2 class="text-lg font-medium text-white flex items-center gap-2">
                            <CloudArrowUpIcon class="w-5 h-5 text-cyan-400" />
                            Recent Deployments
                        </h2>
                    </div>
                    <div class="p-2 flex-1 overflow-y-auto max-h-[400px]">
                        <div v-for="dep in recentDeployments" :key="dep.id" class="p-4 mb-2 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="font-medium text-sm text-white truncate pr-2">{{ dep.site_name }}</div>
                                <span :class="[
                                    'px-2 py-1 rounded-md text-[10px] font-semibold capitalize whitespace-nowrap',
                                    dep.status === 'deployed' ? 'bg-emerald-500/20 text-emerald-400' :
                                    dep.status === 'failed' ? 'bg-red-500/20 text-red-400' :
                                    'bg-amber-500/20 text-amber-400 flex items-center gap-1'
                                ]">
                                    <ArrowPathIcon v-if="dep.status === 'building' || dep.status === 'pending'" class="w-3 h-3 animate-spin" />
                                    {{ dep.status }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-400 flex items-center justify-between mb-2">
                                <span>{{ dep.subdomain }}.mygrownet.com</span>
                                <span>{{ (dep.build_duration_ms / 1000).toFixed(1) }}s</span>
                            </div>
                            <div class="text-[11px] text-slate-500 flex items-center justify-between border-t border-white/5 pt-2">
                                <span>by {{ dep.triggered_by }}</span>
                                <span>{{ formatRelativeTime(dep.deployed_at) }}</span>
                            </div>
                        </div>
                        <div v-if="recentDeployments.length === 0" class="p-5 text-center text-sm text-slate-500">
                            No recent deployments.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recent Activity Timeline -->
            <section class="rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm p-6">
                <h2 class="text-lg font-medium text-white mb-6 flex items-center gap-2">
                    <ClockIcon class="w-5 h-5 text-slate-400" />
                    Recent Activity
                </h2>
                <div class="relative pl-5 border-l border-white/10 space-y-8 ml-2">
                    <div v-for="(item, index) in recentActivity" :key="index" class="relative">
                        <div class="absolute -left-[25px] top-1 w-2.5 h-2.5 rounded-full bg-indigo-500 ring-4 ring-slate-900"></div>
                        <div class="text-sm">
                            <span class="font-semibold text-white mr-2">{{ item.type }}</span>
                            <span class="text-slate-300">{{ item.description }}</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1.5">{{ formatRelativeTime(item.time) }}</div>
                    </div>
                    <div v-if="recentActivity.length === 0" class="text-sm text-slate-500">
                        No recent activity.
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeftIcon,
    GlobeAltIcon,
    ArrowTrendingUpIcon,
    ServerStackIcon,
    BriefcaseIcon,
    ShoppingCartIcon,
    SparklesIcon,
    RocketLaunchIcon,
    QrCodeIcon,
    StarIcon,
    BoltIcon,
    CloudArrowUpIcon,
    ArrowPathIcon,
    ClockIcon
} from '@heroicons/vue/24/outline'

interface Stats {
    total_sites: number;
    active_sites: number;
    custom_domains: number;
    ssg_enabled_sites: number;
    total_pages: number;
    total_orders: number;
    total_revenue_zmw: number;
    ai_usage_this_month: number;
    business_profiles: number;
    profiles_with_tpin: number;
    ssg_deployments_month: number;
    qr_codes_total: number;
    qr_scans_total: number;
    page_revisions_saved: number;
    new_sites_this_month: number;
}

interface TopSite {
    id: number;
    name: string;
    subdomain: string;
    custom_domain: string | null;
    status: string;
    ssg_enabled: boolean;
    template_version: number;
    created_at: string;
    user_email: string;
    business_name: string;
    industry: string;
    city: string;
}

interface SsgDeployment {
    id: number;
    status: 'pending' | 'building' | 'deployed' | 'failed';
    build_duration_ms: number;
    triggered_by: string;
    deployed_at: string;
    site_name: string;
    subdomain: string;
}

interface ActivityItem {
    type: string;
    description: string;
    time: string;
}

const props = defineProps<{
    stats: Stats;
    topSites: TopSite[];
    recentDeployments: SsgDeployment[];
    recentActivity: ActivityItem[];
}>()

const formatCurrency = (amount: number) => {
    return `K ${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const formatRelativeTime = (dateString: string) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)
    
    if (diffInSeconds < 60) return `${diffInSeconds} seconds ago`
    const diffInMinutes = Math.floor(diffInSeconds / 60)
    if (diffInMinutes < 60) return `${diffInMinutes} minutes ago`
    const diffInHours = Math.floor(diffInMinutes / 60)
    if (diffInHours < 24) return `${diffInHours} hours ago`
    const diffInDays = Math.floor(diffInHours / 24)
    if (diffInDays === 1) return `1 day ago`
    return `${diffInDays} days ago`
}
</script>
