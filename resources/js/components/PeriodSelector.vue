<template>
    <div class="flex items-center gap-2">
        <Select v-model="selected" class="w-40">
            <option value="week">Last Week</option>
            <option value="month">Last Month</option>
            <option value="quarter">Last Quarter</option>
            <option value="year">Last Year</option>
        </Select>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Select } from '@/Components/ui/select';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const selected = ref(props.modelValue);

watch(selected, (value) => {
    emit('update:modelValue', value);
});
</script>