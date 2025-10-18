<template>
    <div class="relative">
        <div @click="open = ! open">
            <slot name="trigger" />
        </div>

        <div v-show="open"
             class="absolute z-50 mt-2 rounded-md shadow-lg"
             :class="[widthClass, alignmentClasses]"
             @click="open = false">
            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-white',
    },
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => ({
    '48': 'w-48',
}[props.width.toString()]));

const alignmentClasses = computed(() => ({
    left: 'origin-top-left left-0',
    right: 'origin-top-right right-0',
}[props.align]));
</script>
