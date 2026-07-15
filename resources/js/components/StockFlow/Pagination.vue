<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    meta?: {
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
}

defineProps<Props>();
</script>

<template>
    <div v-if="meta && meta.last_page > 1" class="flex flex-col items-center gap-3 mt-6 sm:flex-row sm:justify-between">
        <p class="text-sm text-gray-600">
            Showing <span class="font-medium">{{ meta.from }}</span>
            to <span class="font-medium">{{ meta.to }}</span>
            of <span class="font-medium">{{ meta.total }}</span> results
        </p>
        <nav class="flex items-center gap-1">
            <template v-for="(link, i) in links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    :class="[
                        'inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                        link.active
                            ? 'bg-emerald-600 text-white'
                            : 'text-gray-700 hover:bg-gray-100',
                    ]"
                    :preserve-scroll="true"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex items-center justify-center px-3 py-1.5 text-sm text-gray-400"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>
