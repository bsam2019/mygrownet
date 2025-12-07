<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { DocumentPlusIcon } from '@heroicons/vue/24/outline';

interface Props {
    icon?: any;
    title: string;
    description?: string;
    actionLabel?: string;
    actionHref?: string;
    compact?: boolean;
}

withDefaults(defineProps<Props>(), {
    icon: DocumentPlusIcon,
    compact: false,
});

defineEmits<{
    action: [];
}>();
</script>

<template>
    <div :class="['text-center', compact ? 'py-6' : 'py-12']">
        <div :class="[
            'mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4',
            compact ? 'h-10 w-10' : 'h-14 w-14'
        ]">
            <component
                :is="icon"
                :class="['text-slate-400', compact ? 'h-5 w-5' : 'h-7 w-7']"
                aria-hidden="true"
            />
        </div>
        <h3 :class="['font-medium text-slate-900', compact ? 'text-sm' : 'text-base']">
            {{ title }}
        </h3>
        <p v-if="description" :class="['text-slate-500 mt-1', compact ? 'text-xs' : 'text-sm']">
            {{ description }}
        </p>
        <div v-if="actionLabel" class="mt-4">
            <Link
                v-if="actionHref"
                :href="actionHref"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition-colors"
            >
                {{ actionLabel }}
            </Link>
            <button
                v-else
                @click="$emit('action')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition-colors"
            >
                {{ actionLabel }}
            </button>
        </div>
        <slot />
    </div>
</template>
