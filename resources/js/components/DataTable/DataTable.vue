<script setup lang="ts">
import { computed } from 'vue';
import LoadingSpinner from '../LoadingSpinner.vue';

const props = defineProps<{
    columns: Array<{
        key: string;
        label: string;
        sortable?: boolean;
    }>;
    data: Array<any>;
    loading?: boolean;
    sortBy?: string;
    sortDirection?: 'asc' | 'desc';
}>();

const emit = defineEmits(['sort']);

const sortedData = computed(() => {
    if (!props.sortBy) return props.data;

    return [...props.data].sort((a, b) => {
        const valA = a[props.sortBy!];
        const valB = b[props.sortBy!];
        return props.sortDirection === 'asc'
            ? valA > valB ? 1 : -1
            : valA < valB ? 1 : -1;
    });
});
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            :class="{ 'cursor-pointer hover:bg-gray-100': column.sortable }"
                            @click="column.sortable && emit('sort', column.key)">
                            <div class="flex items-center space-x-1">
                                <span>{{ column.label }}</span>
                                <span v-if="column.sortable && sortBy === column.key"
                                      class="text-gray-400">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="loading">
                        <td :colspan="columns.length" class="px-6 py-4">
                            <LoadingSpinner />
                        </td>
                    </tr>
                    <template v-else>
                        <slot name="rows" :data="sortedData">
                            <tr v-for="row in sortedData" :key="row.id">
                                <td v-for="column in columns"
                                    :key="column.key"
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ row[column.key] }}
                                </td>
                            </tr>
                        </slot>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
