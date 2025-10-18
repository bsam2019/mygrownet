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
