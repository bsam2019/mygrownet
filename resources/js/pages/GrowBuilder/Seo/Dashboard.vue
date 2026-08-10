<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import {
    MagnifyingGlassIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    GlobeAltIcon,
    DocumentTextIcon,
    MapIcon,
    ClockIcon,
    BuildingOffice2Icon,
    CodeBracketIcon,
    ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    site: Record<string, any>;
    profile: Record<string, any> | null;
}

const props = defineProps<Props>();

const sitemapUrl = computed(() => props.site.custom_domain
    ? `https://${props.site.custom_domain}/sitemap.xml`
    : `/sites/${props.site.subdomain}/sitemap.xml`);

const robotsUrl = computed(() => props.site.custom_domain
    ? `https://${props.site.custom_domain}/robots.txt`
    : `/sites/${props.site.subdomain}/robots.txt`);

const siteUrl = computed(() => props.site.custom_domain
    ? `https://${props.site.custom_domain}`
    : `/sites/${props.site.subdomain}`);

interface SeoCheck {
    id: string;
    label: string;
    status: 'pass' | 'warn' | 'fail';
    detail: string;
    action?: string;
}

const checks = computed((): SeoCheck[] => {
    const profile = props.profile ?? {};
    const site = props.site;
    const results: SeoCheck[] = [];

    // Business Profile completeness
    results.push({
        id: 'business_name',
        label: 'Business name set',
        status: (profile.trade_name || profile.legal_name) ? 'pass' : 'fail',
        detail: (profile.trade_name || profile.legal_name) ? profile.trade_name ?? profile.legal_name : 'No business name — required for JSON-LD schema.',
        action: 'Edit Business Profile',
    });

    results.push({
        id: 'description',
        label: 'Business description',
        status: profile.description ? 'pass' : 'warn',
        detail: profile.description ? `${String(profile.description).length} characters` : 'No description — add one for Google Knowledge Panel.',
        action: 'Edit Business Profile',
    });

    results.push({
        id: 'phone',
        label: 'Phone number',
        status: profile.phone ? 'pass' : 'warn',
        detail: profile.phone ?? 'Missing phone — reduces local search conversion.',
        action: 'Edit Business Profile',
    });

    results.push({
        id: 'address',
        label: 'Physical address',
        status: profile.physical_address ? 'pass' : 'fail',
        detail: profile.physical_address ?? 'No address — required for LocalBusiness schema.',
        action: 'Edit Business Profile',
    });

    results.push({
        id: 'hours',
        label: 'Opening hours',
        status: profile.opening_hours ? 'pass' : 'warn',
        detail: profile.opening_hours ? 'Hours configured — appear in Google Search.' : 'No opening hours — customers cannot tell when you are open.',
        action: 'Edit Business Profile',
    });

    results.push({
        id: 'industry',
        label: 'Industry / Schema type',
        status: profile.industry ? 'pass' : 'warn',
        detail: profile.industry ? `schema.org type will be mapped from "${profile.industry}"` : 'No industry selected — defaults to LocalBusiness schema.',
        action: 'Edit Business Profile',
    });

    // Trust signals
    results.push({
        id: 'tpin',
        label: 'TPIN trust badge',
        status: profile.tpin ? 'pass' : 'warn',
        detail: profile.tpin ? `TPIN: ${profile.tpin}` : 'No TPIN — add for Zambian customer trust.',
        action: 'Edit Business Profile',
    });

    // Site-level checks
    results.push({
        id: 'ssg',
        label: 'Static Site Generation (SSG)',
        status: site.ssg_enabled ? 'pass' : 'warn',
        detail: site.ssg_enabled
            ? `Last deployed: ${site.last_ssg_deployed_at ? new Date(site.last_ssg_deployed_at).toLocaleDateString() : 'Unknown'}`
            : 'SSG disabled — enable for CDN-first serving and faster FCP.',
        action: 'Deploy SSG',
    });

    results.push({
        id: 'custom_domain',
        label: 'Custom domain',
        status: site.custom_domain ? 'pass' : 'warn',
        detail: site.custom_domain ? site.custom_domain : 'Using platform subdomain — a custom domain improves brand credibility.',
    });

    results.push({
        id: 'status',
        label: 'Site published',
        status: site.status === 'published' ? 'pass' : 'fail',
        detail: site.status === 'published' ? 'Site is live and indexed.' : 'Site is not published — Google cannot crawl it.',
    });

    return results;
});

const passCount = computed(() => checks.value.filter(c => c.status === 'pass').length);
const warnCount = computed(() => checks.value.filter(c => c.status === 'warn').length);
const failCount = computed(() => checks.value.filter(c => c.status === 'fail').length);

const seoScore = computed(() => {
    const total = checks.value.length;
    const score = (passCount.value / total) * 100;
    return Math.round(score);
});

const scoreColor = computed(() => {
    if (seoScore.value >= 80) return 'text-emerald-400';
    if (seoScore.value >= 60) return 'text-amber-400';
    return 'text-red-400';
});

const statusIcon = {
    pass: CheckCircleIcon,
    warn: ExclamationTriangleIcon,
    fail: XCircleIcon,
};
const statusClass = {
    pass: 'text-emerald-400',
    warn: 'text-amber-400',
    fail: 'text-red-400',
};
const statusRowClass = {
    pass: 'border-emerald-500/20 bg-emerald-500/5',
    warn: 'border-amber-500/20 bg-amber-500/5',
    fail: 'border-red-500/20 bg-red-500/5',
};
</script>

<template>
    <Head :title="`SEO Health — ${site.name}`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900 text-white">

        <!-- Header -->
        <div class="border-b border-white/10 bg-white/5 backdrop-blur-sm">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center gap-4">
                <div class="p-2.5 bg-emerald-500/20 rounded-xl ring-1 ring-emerald-400/30">
                    <MagnifyingGlassIcon class="h-6 w-6 text-emerald-400" />
                </div>
                <div>
                    <h1 class="text-lg font-bold">SEO Health</h1>
                    <p class="text-xs text-slate-400">{{ site.name }} — structured data &amp; discoverability</p>
                </div>
                <!-- Score -->
                <div class="ml-auto flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs text-slate-400">SEO Score</p>
                        <p class="text-2xl font-bold" :class="scoreColor">{{ seoScore }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 py-8 space-y-6">

            <!-- Score overview -->
            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-1 rounded-xl bg-white/5 border border-white/10 p-4 flex flex-col items-center justify-center">
                    <p class="text-4xl font-bold mb-1" :class="scoreColor">{{ seoScore }}</p>
                    <p class="text-xs text-slate-400 text-center">SEO Score</p>
                </div>
                <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4 flex flex-col items-center justify-center">
                    <p class="text-3xl font-bold text-emerald-400">{{ passCount }}</p>
                    <p class="text-xs text-emerald-300 mt-1">Passing</p>
                </div>
                <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-4 flex flex-col items-center justify-center">
                    <p class="text-3xl font-bold text-amber-400">{{ warnCount }}</p>
                    <p class="text-xs text-amber-300 mt-1">Warnings</p>
                </div>
                <div class="rounded-xl bg-red-500/10 border border-red-500/20 p-4 flex flex-col items-center justify-center">
                    <p class="text-3xl font-bold text-red-400">{{ failCount }}</p>
                    <p class="text-xs text-red-300 mt-1">Failing</p>
                </div>
            </div>

            <!-- SEO Checklist -->
            <div class="rounded-xl bg-white/5 border border-white/10 p-6 space-y-2">
                <h2 class="text-sm font-semibold text-white mb-4">SEO Audit Checks</h2>
                <div v-for="check in checks" :key="check.id"
                     class="rounded-lg border p-3 flex items-start gap-3"
                     :class="statusRowClass[check.status]">
                    <component :is="statusIcon[check.status]" class="h-4 w-4 shrink-0 mt-0.5" :class="statusClass[check.status]" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white">{{ check.label }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ check.detail }}</p>
                    </div>
                    <span v-if="check.action" class="text-xs text-slate-500 shrink-0">→ {{ check.action }}</span>
                </div>
            </div>

            <!-- SEO Assets -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a :href="sitemapUrl" target="_blank"
                   class="rounded-xl bg-white/5 border border-white/10 p-4 flex items-center gap-3 hover:bg-white/10 transition-colors group">
                    <MapIcon class="h-5 w-5 text-slate-400 group-hover:text-emerald-400 transition-colors" />
                    <div>
                        <p class="text-sm font-medium text-white">sitemap.xml</p>
                        <p class="text-xs text-slate-500">Submit to Google Search Console</p>
                    </div>
                    <ArrowTopRightOnSquareIcon class="h-4 w-4 text-slate-600 ml-auto" />
                </a>

                <a :href="robotsUrl" target="_blank"
                   class="rounded-xl bg-white/5 border border-white/10 p-4 flex items-center gap-3 hover:bg-white/10 transition-colors group">
                    <DocumentTextIcon class="h-5 w-5 text-slate-400 group-hover:text-emerald-400 transition-colors" />
                    <div>
                        <p class="text-sm font-medium text-white">robots.txt</p>
                        <p class="text-xs text-slate-500">Crawler instructions</p>
                    </div>
                    <ArrowTopRightOnSquareIcon class="h-4 w-4 text-slate-600 ml-auto" />
                </a>

                <a :href="`https://search.google.com/test/rich-results?url=${encodeURIComponent(siteUrl)}`" target="_blank"
                   class="rounded-xl bg-white/5 border border-white/10 p-4 flex items-center gap-3 hover:bg-white/10 transition-colors group">
                    <CodeBracketIcon class="h-5 w-5 text-slate-400 group-hover:text-emerald-400 transition-colors" />
                    <div>
                        <p class="text-sm font-medium text-white">Rich Results Test</p>
                        <p class="text-xs text-slate-500">Test JSON-LD on Google</p>
                    </div>
                    <ArrowTopRightOnSquareIcon class="h-4 w-4 text-slate-600 ml-auto" />
                </a>
            </div>

            <!-- What the JSON-LD schema will emit -->
            <div class="rounded-xl bg-white/5 border border-white/10 p-6">
                <h2 class="text-sm font-semibold text-white mb-3">JSON-LD Schema Preview</h2>
                <p class="text-xs text-slate-400 mb-3">
                    This schema is automatically injected into every published page's &lt;head&gt; and powers Google's local business card, Knowledge Panel, and rich snippets.
                </p>
                <div class="rounded-lg bg-slate-900 border border-white/10 p-4">
                    <pre class="text-xs text-emerald-300 font-mono whitespace-pre-wrap">{{
    JSON.stringify({
        "@context": "https://schema.org",
        "@type": profile?.industry === 'pharmacy' ? 'Pharmacy' : profile?.industry === 'restaurant' ? 'Restaurant' : 'LocalBusiness',
        "name": profile?.trade_name ?? profile?.legal_name ?? site.name,
        "description": profile?.description ?? '...',
        "telephone": profile?.phone ?? '...',
        "address": {
            "@type": "PostalAddress",
            "streetAddress": profile?.physical_address ?? '...',
            "addressLocality": profile?.city ?? '...',
            "addressCountry": profile?.country ?? 'ZM',
        },
        "openingHours": "Mo-Fr 08:00-17:00",
        "priceRange": profile?.price_range ?? '$$',
    }, null, 2)
}}</pre>
                </div>
            </div>

        </div>
    </div>
</template>
