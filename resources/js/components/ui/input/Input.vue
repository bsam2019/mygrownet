<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';
import { ref, computed } from 'vue';
import { EyeIcon, EyeOffIcon } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    defaultValue?: string | number;
    modelValue?: string | number;
    class?: HTMLAttributes['class'];
    type?: string;
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});

const showPassword = ref(false);
const inputType = computed(() => {
    if (props.type === 'password') {
        return showPassword.value ? 'text' : 'password';
    }
    return props.type || 'text';
});
</script>

<template>
    <div class="relative">
        <input
            v-model="modelValue"
            :type="inputType"
            :class="cn(
                'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                props.type === 'password' ? 'pr-10' : '',
                props.class,
            )"
        />
        <button
            v-if="props.type === 'password'"
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
        >
            <EyeIcon v-if="!showPassword" class="h-5 w-5" />
            <EyeOffIcon v-else class="h-5 w-5" />
        </button>
    </div>
</template>
