<template>
    <nav class="space-y-1">
        <NavigationLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
            <template #icon>
                <HomeIcon class="h-5 w-5" />
            </template>
            Dashboard
        </NavigationLink>
        <NavigationLink :href="route('admin.investments.index')" :active="route().current('admin.investments.*')">
            <template #icon>
                <ChartPieIcon class="h-5 w-5" />
            </template>
            Investments
        </NavigationLink>
        <NavigationLink :href="route('admin.investments.metrics')" :active="route().current('admin.investments.metrics')">
            <template #icon>
                <ChartBarIcon class="h-5 w-5" />
            </template>
            Investment Metrics
        </NavigationLink>
        <NavigationLink :href="route('admin.growmart.dashboard')" :active="route().current('admin.growmart.*')">
            <template #icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
            </template>
            GrowMart
        </NavigationLink>
        <NavigationLink :href="route('admin.settings')" :active="route().current('admin.settings')">
            <template #icon>
                <CogIcon class="h-5 w-5" />
            </template>
            Settings
        </NavigationLink>
    </nav>
</template>

<script setup>
import { HomeIcon, ChartPieIcon, ChartBarIcon, CogIcon } from '@heroicons/vue/24/outline';
</script>
