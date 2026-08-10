<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import {
    QrCodeIcon,
    PlusIcon,
    TrashIcon,
    ArrowTopRightOnSquareIcon,
    ClipboardDocumentIcon,
    LinkIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';

interface QrCode {
    id: number;
    code: string;
    label: string;
    target_url: string;
    short_url: string;
    google_chart: string;
    utm_source: string;
    utm_medium: string;
    utm_campaign: string;
    scan_count: number;
    created_at: string;
    image_path: string | null;
}

interface Props {
    site: Record<string, any>;
}

const props = defineProps<Props>();

const qrCodes = ref<QrCode[]>([]);
const loading = ref(true);
const creating = ref(false);
const showCreateForm = ref(false);
const copiedCode = ref<string | null>(null);

const form = ref({
    label: '',
    custom_url: '',
    utm_source: 'qr_code',
    utm_medium: 'offline',
    utm_campaign: '',
});

onMounted(() => loadQrCodes());

async function loadQrCodes() {
    loading.value = true;
    try {
        const { data } = await axios.get(`/dashboard/sites/${props.site.id}/qr-codes`);
        qrCodes.value = data.qr_codes ?? [];
    } finally {
        loading.value = false;
    }
}

async function createQrCode() {
    if (!form.value.label) return;
    creating.value = true;
    try {
        const { data } = await axios.post(`/dashboard/sites/${props.site.id}/qr-codes`, form.value);
        qrCodes.value.unshift(data.qr_code);
        showCreateForm.value = false;
        form.value = { label: '', custom_url: '', utm_source: 'qr_code', utm_medium: 'offline', utm_campaign: '' };
    } finally {
        creating.value = false;
    }
}

async function deleteQrCode(code: string) {
    if (!confirm('Delete this QR code? Existing printed materials will stop working.')) return;
    await axios.delete(`/dashboard/sites/${props.site.id}/qr-codes/${code}`);
    qrCodes.value = qrCodes.value.filter(q => q.code !== code);
}

function copyShortUrl(qr: QrCode) {
    navigator.clipboard.writeText(qr.short_url);
    copiedCode.value = qr.code;
    setTimeout(() => copiedCode.value = null, 2000);
}

function downloadQr(qr: QrCode) {
    const link = document.createElement('a');
    link.href = qr.google_chart;
    link.target = '_blank';
    link.click();
}

const totalScans = computed(() => qrCodes.value.reduce((sum, q) => sum + q.scan_count, 0));
</script>

<template>
    <Head :title="`QR Codes — ${site.name}`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-teal-950 to-slate-900 text-white">

        <!-- Header -->
        <div class="border-b border-white/10 bg-white/5 backdrop-blur-sm">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center gap-4">
                <div class="p-2.5 bg-teal-500/20 rounded-xl ring-1 ring-teal-400/30">
                    <QrCodeIcon class="h-6 w-6 text-teal-400" />
                </div>
                <div>
                    <h1 class="text-lg font-bold">QR Codes</h1>
                    <p class="text-xs text-slate-400">{{ site.name }} — physical-to-digital bridge</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Total Scans</p>
                        <p class="text-lg font-bold text-teal-400">{{ totalScans.toLocaleString() }}</p>
                    </div>
                    <button @click="showCreateForm = !showCreateForm"
                            class="flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-500 rounded-xl text-sm font-semibold transition-all">
                        <PlusIcon class="h-4 w-4" />
                        New QR Code
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 py-8 space-y-6">

            <!-- Strategy banner -->
            <div class="rounded-xl bg-teal-500/10 border border-teal-400/20 p-4 flex items-start gap-3">
                <LinkIcon class="h-5 w-5 text-teal-400 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-teal-300">Link your offline presence to your digital site</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Place QR codes on business cards, window stickers, flyers, and receipts.
                        Each scan is tracked with UTM attribution so you can measure offline-to-online conversion in BizBoost.
                    </p>
                </div>
            </div>

            <!-- Create Form -->
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                <div v-if="showCreateForm" class="rounded-xl bg-white/5 border border-white/10 p-6 space-y-4">
                    <h2 class="text-sm font-semibold text-white">Create New QR Code</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-slate-400 mb-1">Label *</label>
                            <input v-model="form.label" type="text" placeholder="e.g. Main Site, WhatsApp, Product Catalog"
                                   class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-slate-400 mb-1">Custom URL (leave blank to use your site URL)</label>
                            <input v-model="form.custom_url" type="url" placeholder="https://wa.me/260977123456"
                                   class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">UTM Source</label>
                            <input v-model="form.utm_source" type="text"
                                   class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-teal-500" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">UTM Medium</label>
                            <input v-model="form.utm_medium" type="text"
                                   class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-teal-500" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-slate-400 mb-1">UTM Campaign</label>
                            <input v-model="form.utm_campaign" type="text" placeholder="e.g. lusaka-flyers-aug2026"
                                   class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500" />
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showCreateForm = false"
                                class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancel</button>
                        <button @click="createQrCode" :disabled="creating || !form.label"
                                class="px-5 py-2 bg-teal-600 hover:bg-teal-500 disabled:opacity-60 rounded-xl text-sm font-semibold transition-all">
                            {{ creating ? 'Creating...' : 'Generate QR Code' }}
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- QR Code Grid -->
            <div v-if="loading" class="flex justify-center py-16">
                <ArrowTopRightOnSquareIcon class="h-8 w-8 text-slate-600 animate-spin" />
            </div>

            <div v-else-if="!qrCodes.length" class="rounded-xl bg-white/5 border border-white/10 p-12 text-center">
                <QrCodeIcon class="h-12 w-12 text-slate-600 mx-auto mb-3" />
                <p class="text-slate-400 text-sm">No QR codes yet</p>
                <p class="text-slate-500 text-xs mt-1">Create your first QR code to start tracking offline conversions</p>
                <button @click="showCreateForm = true"
                        class="mt-4 px-5 py-2.5 bg-teal-600 hover:bg-teal-500 rounded-xl text-sm font-semibold transition-all">
                    Create First QR Code
                </button>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="qr in qrCodes" :key="qr.id"
                     class="rounded-xl bg-white/5 border border-white/10 p-4 flex flex-col gap-3">
                    <!-- QR Image -->
                    <div class="bg-white rounded-xl p-3 flex items-center justify-center">
                        <img :src="qr.image_path ?? qr.google_chart" :alt="`QR code for ${qr.label}`"
                             class="h-32 w-32 object-contain" />
                    </div>
                    <!-- Label & Scans -->
                    <div>
                        <p class="text-sm font-semibold text-white">{{ qr.label }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <EyeIcon class="h-3.5 w-3.5 text-slate-500" />
                            <span class="text-xs text-slate-400">{{ qr.scan_count.toLocaleString() }} scans</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 truncate" :title="qr.target_url">{{ qr.target_url }}</p>
                    </div>
                    <!-- UTM badges -->
                    <div class="flex flex-wrap gap-1">
                        <span v-if="qr.utm_source" class="text-xs px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">{{ qr.utm_source }}</span>
                        <span v-if="qr.utm_medium" class="text-xs px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">{{ qr.utm_medium }}</span>
                        <span v-if="qr.utm_campaign" class="text-xs px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">{{ qr.utm_campaign }}</span>
                    </div>
                    <!-- Actions -->
                    <div class="flex gap-2 mt-auto">
                        <button @click="copyShortUrl(qr)"
                                class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg text-xs font-medium border transition-all"
                                :class="copiedCode === qr.code
                                    ? 'bg-emerald-500/20 border-emerald-500/30 text-emerald-400'
                                    : 'bg-white/5 border-white/10 text-slate-400 hover:text-white hover:border-white/20'">
                            <ClipboardDocumentIcon class="h-3.5 w-3.5" />
                            {{ copiedCode === qr.code ? 'Copied!' : 'Copy Link' }}
                        </button>
                        <button @click="downloadQr(qr)"
                                class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg text-xs font-medium border bg-white/5 border-white/10 text-slate-400 hover:text-white hover:border-white/20 transition-all">
                            <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
                            Download
                        </button>
                        <button @click="deleteQrCode(qr.code)"
                                class="p-1.5 rounded-lg text-xs border bg-white/5 border-red-500/20 text-red-400 hover:bg-red-500/20 transition-all">
                            <TrashIcon class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
