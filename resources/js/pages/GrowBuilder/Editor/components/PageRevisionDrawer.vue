<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import {
    ClockIcon,
    ArrowPathIcon,
    XMarkIcon,
    ArrowUturnLeftIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

interface Revision {
    id: number;
    revision_number: number;
    created_by_user_id: number;
    commit_message: string | null;
    trigger: string;
    created_at: string;
}

interface Props {
    siteId: number;
    pageId: number;
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'rollback', layout: any[]): void;
}>();

const revisions = ref<Revision[]>([]);
const loading = ref(false);
const rollingBack = ref<number | null>(null);
const rollbackSuccess = ref<number | null>(null);

async function load() {
    if (!props.pageId) return;
    loading.value = true;
    try {
        const { data } = await axios.get(
            `/dashboard/editor/${props.siteId}/pages/${props.pageId}/revisions`
        );
        revisions.value = data.revisions ?? [];
    } finally {
        loading.value = false;
    }
}

async function rollback(revisionNumber: number) {
    if (!confirm(`Restore page to revision #${revisionNumber}? Your current content will be saved as a new snapshot first.`)) return;
    rollingBack.value = revisionNumber;
    try {
        const { data } = await axios.post(
            `/dashboard/editor/${props.siteId}/pages/${props.pageId}/revisions/${revisionNumber}/rollback`
        );
        if (data.success) {
            rollbackSuccess.value = revisionNumber;
            emit('rollback', data.layout);
            await load(); // Refresh list — new pre-rollback revision added
            setTimeout(() => rollbackSuccess.value = null, 3000);
        }
    } finally {
        rollingBack.value = null;
    }
}

function relativeTime(dateStr: string): string {
    const diff = Date.now() - new Date(dateStr).getTime();
    const m = Math.floor(diff / 60000);
    if (m < 1) return 'Just now';
    if (m < 60) return `${m}m ago`;
    const h = Math.floor(m / 60);
    if (h < 24) return `${h}h ago`;
    return `${Math.floor(h / 24)}d ago`;
}

const triggerBadge: Record<string, { label: string; class: string }> = {
    auto_save:   { label: 'Auto-save',   class: 'bg-blue-500/20 text-blue-400' },
    manual:      { label: 'Manual',      class: 'bg-slate-500/20 text-slate-400' },
    pre_upgrade: { label: 'Pre-upgrade', class: 'bg-amber-500/20 text-amber-400' },
    pre_rollback:{ label: 'Pre-rollback',class: 'bg-violet-500/20 text-violet-400' },
};

// Load when drawer opens
import { watch } from 'vue';
watch(() => props.open, (val) => {
    if (val) load();
});
watch(() => props.pageId, () => {
    if (props.open) load();
});
</script>

<template>
    <!-- Overlay -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="open" class="fixed inset-0 bg-black/50 z-40" @click="emit('close')" />
    </Transition>

    <!-- Drawer -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0"
                leave-active-class="transition duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
        <div v-if="open"
             class="fixed right-0 top-0 h-full w-80 bg-slate-900 border-l border-white/10 z-50 flex flex-col shadow-2xl">

            <!-- Drawer header -->
            <div class="flex items-center gap-3 px-4 py-3 border-b border-white/10">
                <ClockIcon class="h-4 w-4 text-slate-400" />
                <span class="text-sm font-semibold text-white">Revision History</span>
                <button @click="emit('close')" class="ml-auto text-slate-500 hover:text-white transition-colors">
                    <XMarkIcon class="h-4 w-4" />
                </button>
            </div>

            <!-- Rollback success banner -->
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100">
                <div v-if="rollbackSuccess !== null"
                     class="mx-4 mt-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-3 flex items-center gap-2">
                    <CheckCircleIcon class="h-4 w-4 text-emerald-400 shrink-0" />
                    <p class="text-xs text-emerald-400">Page restored to revision #{{ rollbackSuccess }}.</p>
                </div>
            </Transition>

            <!-- Loading -->
            <div v-if="loading" class="flex-1 flex items-center justify-center">
                <ArrowPathIcon class="h-6 w-6 text-slate-600 animate-spin" />
            </div>

            <!-- Empty state -->
            <div v-else-if="!revisions.length" class="flex-1 flex flex-col items-center justify-center gap-2 px-6">
                <ClockIcon class="h-10 w-10 text-slate-700" />
                <p class="text-sm text-slate-500 text-center">No revisions yet</p>
                <p class="text-xs text-slate-600 text-center">Revisions are saved automatically each time you save the page.</p>
            </div>

            <!-- Revision list -->
            <div v-else class="flex-1 overflow-y-auto py-2">
                <div v-for="rev in revisions" :key="rev.id"
                     class="px-4 py-3 border-b border-white/5 hover:bg-white/5 transition-colors group">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-semibold text-slate-300">#{{ rev.revision_number }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium"
                                      :class="(triggerBadge[rev.trigger] ?? triggerBadge.manual).class">
                                    {{ (triggerBadge[rev.trigger] ?? triggerBadge.manual).label }}
                                </span>
                            </div>
                            <p v-if="rev.commit_message" class="text-xs text-slate-400 mt-0.5 truncate">{{ rev.commit_message }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ relativeTime(rev.created_at) }}</p>
                        </div>
                        <!-- Rollback button (appears on hover) -->
                        <button @click="rollback(rev.revision_number)"
                                :disabled="rollingBack === rev.revision_number"
                                class="shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium border border-white/10 bg-white/5 text-slate-400 hover:text-white hover:bg-blue-600 hover:border-blue-500 transition-all opacity-0 group-hover:opacity-100"
                                :class="rollingBack === rev.revision_number ? 'opacity-100 animate-pulse' : ''">
                            <ArrowUturnLeftIcon class="h-3 w-3" />
                            {{ rollingBack === rev.revision_number ? '...' : 'Restore' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer info -->
            <div class="border-t border-white/10 px-4 py-3">
                <p class="text-xs text-slate-600">Revisions are kept for 90 days. Pre-upgrade snapshots are kept indefinitely.</p>
            </div>
        </div>
    </Transition>
</template>
