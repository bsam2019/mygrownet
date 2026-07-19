<script setup lang="ts">
import { ref } from 'vue';
import { Squares2X2Icon } from '@heroicons/vue/24/solid';

const open = ref(false);

const shortcuts = [
    { name: 'GrowNet', domain: 'https://grownet.mygrownet.com', color: 'bg-green-500' },
    { name: 'BizBoost', domain: 'https://bizboost.mygrownet.com', color: 'bg-blue-500' },
    { name: 'GrowMart', domain: 'https://growmart.mygrownet.com', color: 'bg-orange-500' },
    { name: 'BMS', domain: 'https://bms.mygrownet.com', color: 'bg-purple-500' },
    { name: 'StockFlow', domain: 'https://stockflow.mygrownet.com', color: 'bg-teal-500' },
    { name: 'GrowFinance', domain: 'https://finance.mygrownet.com', color: 'bg-indigo-500' },
];
</script>

<template>
    <div class="relative">
        <button
            @click="open = !open"
            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            title="Switch Application"
        >
            <Squares2X2Icon class="w-5 h-5" />
        </button>

        <div
            v-if="open"
            class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-200 p-3 z-50"
            @click.away="open = false"
        >
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2 px-1">Quick Launch</p>
            <div class="grid grid-cols-3 gap-2">
                <a
                    v-for="app in shortcuts"
                    :key="app.domain"
                    :href="app.domain"
                    class="flex flex-col items-center gap-1 p-2 rounded-lg hover:bg-gray-50 transition-colors text-center"
                >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="app.color">
                        <span class="text-white text-xs font-bold">{{ app.name.charAt(0) }}</span>
                    </div>
                    <span class="text-xs text-gray-600 truncate w-full">{{ app.name }}</span>
                </a>
            </div>
        </div>
    </div>
</template>
