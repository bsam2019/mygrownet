<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface SearchResult {
    id: number;
    name: string;
    type: string;
    route: string;
}

interface ResultGroup {
    label: string;
    results: SearchResult[];
}

const page = usePage();
const isSubdomain = ((page.props as any).routeName ?? '').startsWith('stockflow.sub.');
const apiPrefix = isSubdomain ? '/search' : '/stockflow/search';

const query = ref('');
const results = ref<ResultGroup[]>([]);
const showDropdown = ref(false);
const loading = ref(false);
let debounceTimer: number | null = null;

const search = async () => {
    const q = query.value.trim();
    if (q.length < 2) {
        results.value = [];
        showDropdown.value = false;
        return;
    }
    loading.value = true;
    try {
        const res = await fetch(`${apiPrefix}?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        results.value = data ?? [];
        showDropdown.value = true;
    } catch {
        results.value = [];
    }
    loading.value = false;
};

const onInput = () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(search, 300);
};

const navigate = (item: SearchResult) => {
    showDropdown.value = false;
    query.value = '';
    results.value = [];
    window.location.href = item.route;
};

const close = () => {
    showDropdown.value = false;
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') close();
};

const handleClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('[data-global-search]')) close();
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.removeEventListener('click', handleClickOutside);
    if (debounceTimer) clearTimeout(debounceTimer);
});
</script>

<template>
    <div data-global-search class="relative flex-1 max-w-lg">
        <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
            <input
                v-model="query"
                @input="onInput"
                @focus="query.trim().length >= 2 ? (showDropdown = true) : null"
                type="text"
                placeholder="Search items, customers, suppliers..."
                class="w-full rounded-lg border-0 bg-gray-100 pl-10 pr-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all"
            />
            <div v-if="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            </div>
        </div>

        <transition name="dropdown">
            <div v-if="showDropdown && results.length > 0" class="absolute left-0 right-0 mt-2 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black/5 focus:outline-none z-50">
                <div v-for="group in results" :key="group.label" class="py-1">
                    <div class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ group.label }}</div>
                    <button
                        v-for="item in group.results"
                        :key="item.id"
                        @click="navigate(item)"
                        class="flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left"
                    >
                        <span class="flex-1 truncate">{{ item.name }}</span>
                        <span class="flex-shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700">{{ item.type }}</span>
                    </button>
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
