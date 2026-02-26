<script setup lang="ts">
interface Props {
    count?: number;
}

withDefaults(defineProps<Props>(), {
    count: 5,
});
</script>

<template>
    <div class="divide-y divide-gray-200">
        <div
            v-for="i in count"
            :key="i"
            class="px-6 py-4 animate-pulse"
        >
            <div class="flex items-center gap-4">
                <!-- Icon skeleton -->
                <div class="w-10 h-10 bg-gray-200 rounded"></div>
                
                <!-- Content skeleton -->
                <div class="flex-1 min-w-0">
                    <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                </div>
                
                <!-- Actions skeleton -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-200 rounded"></div>
                    <div class="w-8 h-8 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>
    </div>
</template>
