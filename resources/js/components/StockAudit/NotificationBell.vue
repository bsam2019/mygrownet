<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { BellIcon, CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon, InformationCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useNotifications } from '@/composables/useNotifications';

interface ApiNotification {
    id: number;
    type: string;
    title: string;
    message: string | null;
    action_url: string | null;
    action_text: string | null;
    priority: string;
    read_at: string | null;
    created_at: string;
}

interface LocalNote {
    id: number;
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
    read: boolean;
}

const page = usePage();
const { notifications: toastNotifications } = useNotifications();

const showBell = ref(false);
const localNotes = ref<LocalNote[]>([]);
const apiNotes = ref<ApiNotification[]>([]);
const unreadApiCount = ref(0);
let localId = 0;
let pollTimer: number | null = null;

const unreadCount = computed(() => unreadApiCount.value + localNotes.value.filter(n => !n.read).length);

const isSubdomain = ((page.props as any).routeName ?? '').startsWith('stockflow.sub.');
const apiPrefix = isSubdomain ? '/notifications' : '/stock-audit/notifications';

const addLocalNote = (type: LocalNote['type'], message: string) => {
    localNotes.value.unshift({ id: ++localId, type, message, read: false });
    if (localNotes.value.length > 20) localNotes.value.pop();
};

const fetchCount = async () => {
    try {
        const res = await fetch(apiPrefix + '/count');
        const data = await res.json();
        unreadApiCount.value = data.count ?? 0;
    } catch {}
};

const fetchList = async () => {
    try {
        const res = await fetch(apiPrefix + '/list');
        const data = await res.json();
        apiNotes.value = data.notifications ?? [];
    } catch {}
};

const markRead = async (id: number) => {
    const n = apiNotes.value.find(n => n.id === id);
    if (n && !n.read_at) {
        n.read_at = new Date().toISOString();
        unreadApiCount.value = Math.max(0, unreadApiCount.value - 1);
        try { await fetch(apiPrefix + '/' + id + '/read', { method: 'POST' }); } catch {}
    }
};

const markAllRead = async () => {
    apiNotes.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString(); });
    unreadApiCount.value = 0;
    localNotes.value = [];
    try { await fetch(apiPrefix + '/read-all', { method: 'POST' }); } catch {}
};

const dismissLocal = (id: number) => {
    localNotes.value = localNotes.value.filter(n => n.id !== id);
};

const typeFromPriority = (p: string): LocalNote['type'] => {
    if (p === 'urgent' || p === 'high') return 'error';
    if (p === 'low') return 'info';
    return 'success';
};

const typeIcon: Record<string, any> = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
};

const typeStyle: Record<string, string> = {
    success: 'text-emerald-600 bg-emerald-50',
    error: 'text-red-600 bg-red-50',
    warning: 'text-amber-600 bg-amber-50',
    info: 'text-blue-600 bg-blue-50',
};

const formatTime = (dateStr: string) => {
    const d = new Date(dateStr);
    const now = new Date();
    const diff = now.getTime() - d.getTime();
    if (diff < 60000) return 'just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    return d.toLocaleDateString();
};

// Capture flash on mount
onMounted(() => {
    const flash = (page.props as any).flash ?? {};
    if (flash.success) addLocalNote('success', flash.success);
    if (flash.error) addLocalNote('error', flash.error);
    if (flash.message) addLocalNote('info', flash.message);
    fetchCount();
    pollTimer = window.setInterval(fetchCount, 30000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});

// Watch toasts
watch(toastNotifications, (notes) => {
    for (const n of notes) {
        const exists = localNotes.value.some(b => b.message === n.message && b.type === n.type);
        if (!exists) addLocalNote(n.type, n.message);
    }
}, { deep: true });

// Fetch list when bell opens
watch(showBell, (val) => { if (val) fetchList(); });

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') showBell.value = false;
};

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <div data-notification-bell class="relative">
        <button
            @click.stop="showBell = !showBell"
            class="relative rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
        >
            <BellIcon class="h-6 w-6" />
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <transition name="dropdown">
            <div
                v-if="showBell"
                class="absolute right-0 mt-2 w-80 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
            >
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                    <button v-if="unreadCount > 0" @click="markAllRead" class="text-xs text-gray-500 hover:text-gray-700">Clear all</button>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <div v-if="apiNotes.length === 0 && localNotes.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                        <BellIcon class="mx-auto h-8 w-8 text-gray-300 mb-2" />
                        No notifications yet
                    </div>

                    <template v-for="note in apiNotes" :key="'api-' + note.id">
                        <div
                            @click="markRead(note.id)"
                            class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0"
                            :class="{ 'opacity-60': note.read_at }"
                        >
                            <div :class="['flex-shrink-0 rounded-lg p-1.5', typeStyle[typeFromPriority(note.priority)]]">
                                <component :is="typeIcon[typeFromPriority(note.priority)]" class="h-4 w-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ note.title }}</p>
                                <p v-if="note.message" class="text-xs text-gray-500 mt-0.5">{{ note.message }}</p>
                                <p class="text-[10px] text-gray-400 mt-1">{{ formatTime(note.created_at) }}</p>
                            </div>
                        </div>
                    </template>

                    <div v-for="note in localNotes" :key="'local-' + note.id" @click="note.read = true" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0" :class="{ 'opacity-60': note.read }">
                        <div :class="['flex-shrink-0 rounded-lg p-1.5', typeStyle[note.type]]">
                            <component :is="typeIcon[note.type]" class="h-4 w-4" />
                        </div>
                        <p class="flex-1 text-sm text-gray-700 min-w-0">{{ note.message }}</p>
                        <button @click.stop="dismissLocal(note.id)" class="flex-shrink-0 text-gray-300 hover:text-gray-500">
                            <XMarkIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.dropdown-enter-active { transition: all 0.15s ease-out; }
.dropdown-leave-active { transition: all 0.1s ease-in; }
.dropdown-enter-from { opacity: 0; transform: translateY(-4px); }
.dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
