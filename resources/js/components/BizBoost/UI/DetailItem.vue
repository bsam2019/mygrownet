<script setup lang="ts">
interface Props {
    label: string;
    value?: string | number | null;
    icon?: object;
    href?: string;
    type?: 'default' | 'email' | 'phone' | 'link';
}

const props = withDefaults(defineProps<Props>(), {
    type: 'default',
});

const displayValue = props.value ?? '-';

const getHref = () => {
    if (props.href) return props.href;
    if (!props.value) return null;
    switch (props.type) {
        case 'email': return `mailto:${props.value}`;
        case 'phone': return `tel:${props.value}`;
        case 'link': return String(props.value);
        default: return null;
    }
};
</script>

<template>
    <div class="flex items-start gap-3">
        <div v-if="icon" class="flex-shrink-0 p-2 bg-gray-100 rounded-lg dark:bg-gray-700">
            <component :is="icon" class="h-4 w-4 text-gray-600 dark:text-gray-400" aria-hidden="true" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ label }}</p>
            <a
                v-if="getHref() && value"
                :href="getHref()!"
                class="text-sm text-gray-900 hover:text-violet-600 dark:text-white dark:hover:text-violet-400 transition-colors"
            >
                {{ displayValue }}
            </a>
            <p v-else class="text-sm text-gray-900 dark:text-white">
                <slot>{{ displayValue }}</slot>
            </p>
        </div>
    </div>
</template>
