<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    modelValue: string | number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const isOpen = ref(false);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const close = () => {
    isOpen.value = false;
};

const handleSelect = (value: string | number) => {
    emit('update:modelValue', value);
    close();
};
</script>

<template>
    <div class="relative" @blur="close" tabindex="-1">
        <slot 
            :value="modelValue"
            :isOpen="isOpen"
            :onToggle="toggle"
            :onSelect="handleSelect"
        />
    </div>
</template>