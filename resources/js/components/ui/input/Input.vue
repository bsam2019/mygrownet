<script setup>
import { onMounted, ref, computed } from 'vue';
import { EyeIcon, EyeOffIcon } from 'lucide-vue-next';

const props = defineProps({
    type: {
        type: String,
        default: 'text'
    },
    modelValue: {
        type: [String, Number],
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const model = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const input = ref(null);
const showPassword = ref(false);

const inputType = computed(() => {
    if (props.type === 'password') {
        return showPassword.value ? 'text' : 'password';
    }
    return props.type;
});

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="relative">
        <input
            :type="inputType"
            class="flex h-11 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors placeholder:text-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50"
            v-model="model"
            ref="input"
        />
        <button
            v-if="props.type === 'password'"
            type="button"
            @click="showPassword = !showPassword"
            tabindex="-1"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
        >
            <EyeIcon v-if="!showPassword" class="h-5 w-5" />
            <EyeOffIcon v-else class="h-5 w-5" />
        </button>
    </div>
</template>
