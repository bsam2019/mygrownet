<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import {
    RocketLaunchIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ArrowPathIcon,
    GlobeAltIcon,
    ChartBarIcon,
    Cog6ToothIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    site: Record<string, any>;
    deployments: Array<{
        id: number;
        status: 'pending' | 'building' | 'deployed' | 'failed';
        build_duration_ms: number;
        triggered_by: string;
        deployed_at: string | null;
        cdn_url: string | null;
        build_log: string | null;
        created_at: string;
    }>;
}

const props = defineProps<Props>();

const isDeploying = ref(false);
const deployResult = ref<null | { success: boolean; message: string; cdn_url?: string; pages_compiled?: number; build_duration_ms?: number }>(null);
const ssgEnabled = ref(!!props.site.ssg_enabled);
const selectedLog = ref<string | null>(null);
const deployments = ref(props.deployments ?? []);

async function triggerDeploy() {
    isDeploying.value = true;
    deployResult.value = null;
    try {
        const { data } = await axios.post(`/dashboard/sites/${props.site.id}/ssg/deploy`, { trigger: 'manual' });
        deployResult.value = data;
        // Prepend to deployment list
        deployments.value.unshift({
            id: Date.now(),
            status: data.success ? 'deployed' : 'failed',
            build_duration_ms: data.build_duration_ms,
            triggered_by: 'manual',
            deployed_at: new Date().toISOString(),
            cdn_url: data.cdn_url,
            build_log: data.errors?.join('\n') ?? null,
            created_at: new Date().toISOString(),
        });
    } catch (e: any) {
        deployResult.value = { success: false, message: e.response?.data?.message ?? 'Deploy failed.' };
    } finally {
        isDeploying.value = false;
    }
}

async function toggleSsg() {
    const newVal = !ssgEnabled.value;
    await axios.post(`/dashboard/sites/${props.site.id}/ssg/toggle`, { enabled: newVal });
    ssgEnabled.value = newVal;
}

const avgBuildMs = computed(() => {
    const deployed = deployments.value.filter(d => d.status === 'deployed' && d.build_duration_ms);
    if (!deployed.length) return null;
    const avg = deployed.reduce((sum, d) => sum + d.build_duration_ms, 0) / deployed.length;
    return Math.round(avg);
});

function relativeTime(dateStr: string): string {
    const diff = Date.now() - new Date(dateStr).getTime();
    const m = Math.floor(diff / 60000);
    if (m < 1) return 'Just now';
    if (m < 60) return `${m}m ago`;
    const h = Math.floor(m / 60);
    if (h < 24) return `${h}h ago`;
    return `${Math.floor(h / 24)}d ago`;
}

const statusConfig: Record<string, { label: string; class: string; icon: any }> = {
    deployed: { label: 'Deployed', class: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30', icon: CheckCircleIcon },
    failed:   { label: 'Failed',   class: 'bg-red-500/20 text-red-400 border-red-500/30',           icon: XCircleIcon },
    building: { label: 'Building', class: 'bg-amber-500/20 text-amber-400 border-amber-500/30',     icon: ArrowPathIcon },
    pending:  { label: 'Pending',  class: 'bg-slate-500/20 text-slate-400 border-slate-500/30',     icon: ClockIcon },
};
</script>

<template>
    <Head :title="`SSG Deploy — ${site.name}`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-violet-950 to-slate-900 text-white">

        <!-- Header -->
        <div class="border-b border-white/10 bg-white/5 backdrop-blur-sm">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center gap-4">
                <div class="p-2.5 bg-violet-500/20 rounded-xl ring-1 ring-violet-400/30">
                    <RocketLaunchIcon class="h-6 w-6 text-violet-400" />
                </div>
                <div>
                    <h1 class="text-lg font-bold">Static Site Generation</h1>
                    <p class="text-xs text-slate-400">{{ site.name }} — CDN-optimised builds</p>
                </div>
                <!-- SSG Toggle -->
                <div class="ml-auto flex items-center gap-3">
                    <span class="text-xs" :class="ssgEnabled ? 'text-emerald-400' : 'text-slate-500'">
                        {{ ssgEnabled ? 'SSG Enabled' : 'SSG Disabled' }}
                    </span>
                    <button @click="toggleSsg"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                            :class="ssgEnabled ? 'bg-emerald-600' : 'bg-slate-700'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                              :class="ssgEnabled ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 py-8 space-y-6">

            <!-- Strategy banner -->
            <div class="rounded-xl bg-violet-500/10 border border-violet-400/20 p-4 flex items-start gap-3">
                <GlobeAltIcon class="h-5 w-5 text-violet-400 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-violet-300">Why SSG? First Contentful Paint &lt;2s on 3G</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        SSG compiles your site pages into self-contained HTML/CSS files served from CDN — no server round-trip required.
                        Critical for Zambian mobile users on slow connections.
                    </p>
                </div>
            </div>

            <!-- Metrics row -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                    <p class="text-xs text-slate-400 mb-1">Total Deployments</p>
                    <p class="text-2xl font-bold text-white">{{ deployments.length }}</p>
                </div>
                <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                    <p class="text-xs text-slate-400 mb-1">Avg Build Time</p>
                    <p class="text-2xl font-bold" :class="avgBuildMs && avgBuildMs < 2000 ? 'text-emerald-400' : 'text-amber-400'">
                        {{ avgBuildMs ? `${avgBuildMs}ms` : '—' }}
                    </p>
                </div>
                <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                    <p class="text-xs text-slate-400 mb-1">Last Deploy</p>
                    <p class="text-sm font-semibold text-white">
                        {{ deployments[0] ? relativeTime(deployments[0].created_at) : 'Never' }}
                    </p>
                </div>
            </div>

            <!-- Deploy trigger -->
            <div class="rounded-xl bg-white/5 border border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-white">Trigger Build</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Compiles all pages to static HTML with JSON-LD SEO and sitemap.xml</p>
                    </div>
                    <button @click="triggerDeploy" :disabled="isDeploying"
                            class="flex items-center gap-2 px-5 py-2.5 bg-violet-600 hover:bg-violet-500 disabled:opacity-60 rounded-xl text-sm font-semibold transition-all duration-200 shadow-lg shadow-violet-500/25">
                        <ArrowPathIcon class="h-4 w-4" :class="isDeploying ? 'animate-spin' : ''" />
                        {{ isDeploying ? 'Building...' : 'Deploy Static Build' }}
                    </button>
                </div>

                <!-- Deploy result -->
                <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0">
                    <div v-if="deployResult" class="rounded-lg p-4 border"
                         :class="deployResult.success ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-red-500/10 border-red-500/20'">
                        <div class="flex items-start gap-2">
                            <CheckCircleIcon v-if="deployResult.success" class="h-4 w-4 text-emerald-400 shrink-0 mt-0.5" />
                            <XCircleIcon v-else class="h-4 w-4 text-red-400 shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium" :class="deployResult.success ? 'text-emerald-400' : 'text-red-400'">
                                    {{ deployResult.message }}
                                </p>
                                <div v-if="deployResult.success" class="flex items-center gap-4 mt-1">
                                    <span class="text-xs text-slate-400">📄 {{ deployResult.pages_compiled }} pages</span>
                                    <span class="text-xs text-slate-400">⚡ {{ deployResult.build_duration_ms }}ms</span>
                                    <a v-if="deployResult.cdn_url" :href="deployResult.cdn_url" target="_blank"
                                       class="text-xs text-violet-400 hover:underline">View deployed site →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Deployment History -->
            <div class="rounded-xl bg-white/5 border border-white/10 p-6">
                <h2 class="text-sm font-semibold text-white mb-4">Deployment History</h2>
                <div v-if="!deployments.length" class="text-center py-8 text-slate-500 text-sm">
                    No deployments yet. Click "Deploy Static Build" to get started.
                </div>
                <div v-else class="space-y-2">
                    <div v-for="d in deployments" :key="d.id"
                         class="rounded-lg bg-white/5 border border-white/10 p-3 flex items-center gap-4">
                        <!-- Status icon -->
                        <component :is="statusConfig[d.status]?.icon ?? ClockIcon"
                                   class="h-4 w-4 shrink-0"
                                   :class="d.status === 'building' ? 'animate-spin' : ''"
                                   :style="{ color: d.status === 'deployed' ? '#34d399' : d.status === 'failed' ? '#f87171' : d.status === 'building' ? '#fbbf24' : '#94a3b8' }" />
                        <!-- Status badge -->
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full border"
                              :class="statusConfig[d.status]?.class">
                            {{ statusConfig[d.status]?.label }}
                        </span>
                        <span class="text-xs text-slate-400">{{ d.triggered_by }}</span>
                        <span v-if="d.build_duration_ms" class="text-xs text-slate-500">{{ d.build_duration_ms }}ms</span>
                        <div class="ml-auto flex items-center gap-3">
                            <a v-if="d.cdn_url && d.status === 'deployed'" :href="d.cdn_url" target="_blank"
                               class="text-xs text-violet-400 hover:underline">View →</a>
                            <button v-if="d.build_log" @click="selectedLog = selectedLog === d.id.toString() ? null : d.id.toString()"
                                    class="text-xs text-slate-500 hover:text-slate-300">Logs</button>
                            <span class="text-xs text-slate-600">{{ relativeTime(d.created_at) }}</span>
                        </div>
                    </div>

                    <!-- Build log viewer -->
                    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100">
                        <div v-if="selectedLog !== null">
                            <div v-for="d in deployments.filter(x => x.id.toString() === selectedLog)" :key="`log-${d.id}`"
                                 class="mt-2 rounded-lg bg-slate-900 border border-white/10 p-4">
                                <pre class="text-xs text-slate-300 whitespace-pre-wrap font-mono">{{ d.build_log }}</pre>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>
