<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-4">
            <li>
                <div>
                    <Link :href="route('admin.dashboard')" class="text-gray-400 hover:text-gray-500">
                        <HomeIcon class="h-5 w-5 flex-shrink-0" aria-hidden="true" />
                        <span class="sr-only">Home</span>
                    </Link>
                </div>
            </li>
            <li v-for="(item, index) in items" :key="index">
                <div class="flex items-center">
                    <ChevronRightIcon class="h-5 w-5 flex-shrink-0 text-gray-400" aria-hidden="true" />
                    <Link
                        v-if="item.href && !item.current"
                        :href="item.href"
                        class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                    >
                        {{ item.title }}
                    </Link>
                    <span
                        v-else
                        class="ml-4 text-sm font-medium text-gray-500"
                        :class="{ 'text-gray-700': item.current }"
                        aria-current="page"
                    >
                        {{ item.title }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { HomeIcon, ChevronRightIcon } from 'lucide-vue-next';
import type { BreadcrumbItemType } from '@/types';

defineProps<{
    items: BreadcrumbItemType[];
}>();
</script>
